<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_Record_Model extends Settings_Vtiger_Record_Model
{

	const tableName = 'vtiger_pbxmanager_gateway';

	public function getId()
	{
		return $this->get('id');
	}

	public function getName()
	{
		
	}

	public function getModule()
	{
		return new Settings_PBXManager_Module_Model;
	}

	static function getCleanInstance()
	{
		return new self;
	}

	public static function getInstance()
	{
		$serverModel = new self();
		$row = (new \App\Db\Query())->from(self::tableName)->one();
		if ($row !== false) {
			$serverModel->set('gateway', $row['gateway']);
			$serverModel->set('id', $row['id']);
			$parameters = \App\Json::decode(decode_html($row['parameters']));
			foreach ($parameters as $fieldName => $fieldValue) {
				$serverModel->set($fieldName, $fieldValue);
			}
			return $serverModel;
		}
		return $serverModel;
	}

	public static function getInstanceById($recordId, $qualifiedModuleName)
	{
		$row = (new \App\Db\Query())->from(self::tableName)->where(['id' => $recordId])->one();
		if ($row !== false) {
			$recordModel = new self();
			$recordModel->setData($row);
			$parameters = \App\Json::decode(decode_html($recordModel->get('parameters')));
			foreach ($parameters as $fieldName => $fieldValue) {
				$recordModel->set($fieldName, $fieldValue);
			}
			return $recordModel;
		}
		return false;
	}

	public function save()
	{
		$db = App\Db::getInstance();
		$parameters = '';
		$selectedGateway = $this->get('gateway');
		foreach (PBXManager_PBXManager_Connector::getSettingsParameters() as $field => $type) {
			$parameters[$field] = $this->get($field);
		}
		$this->set('parameters', \App\Json::encode($parameters));
		$params = [
			'gateway' => $selectedGateway,
			'parameters' => $this->get('parameters')
		];
		$id = $this->getId();
		if ($id) {
			$db->createCommand()->update(self::tableName, $params, ['id' => $id])->execute();
		} else {
			$db->createCommand()->insert(self::tableName, $params)->execute();
		}
	}
}
