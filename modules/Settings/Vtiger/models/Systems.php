<?php
 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_Systems_Model extends \App\Base
{
	const TABLE_NAME = 'vtiger_systems';

	public function getId()
	{
		return $this->get('id');
	}

	public function isSmtpAuthEnabled()
	{
		$smtp_auth_value = $this->get('smtp_auth');

		return ('on' == $smtp_auth_value || 1 == $smtp_auth_value || 'true' == $smtp_auth_value) ? true : false;
	}

	public function save()
	{
		$dbInstance = App\Db::getInstance();

		$id = $this->getId();
		$params = [
			'server' => $this->get('server'),
			'server_port' => $this->get('server_port'),
			'server_username' => $this->get('server_username'),
			'server_password' => $this->get('server_password'),
			'server_type' => $this->get('server_type'),
			'smtp_auth' => $this->isSmtpAuthEnabled(),
			'server_path' => $this->get('server_path'),
			'from_email_field' => $this->get('from_email_field')
		];

		if (empty($id)) {
			$id = $dbInstance->getUniqueID(self::TABLE_NAME);
			$params['id'] = $id;
			$query = $dbInstance->createCommand()->insert(self::TABLE_NAME, $params)->execute();
		} else {
			$query = $dbInstance->createCommand()->update(self::TABLE_NAME, $params, ['id' => $id])->execute();
		}

		return $id;
	}

	public static function getInstanceFromServerType($type, $componentName)
	{
		$result = (new App\Db\Query())->from(self::TABLE_NAME)->where(['server_type' => $type])->one();
		try {
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, 'Settings:Vtiger');
		} catch (Exception $e) {
			$modelClassName = self;
		}
		$instance = new $modelClassName();
		if ($result) {
			$instance->setData($result);
		}
		return $instance;
	}
}
