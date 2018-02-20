<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_Server_Model extends \App\Base
{
	const TABLE_NAME = 'vtiger_pbxmanager_gateway';

	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Static Function Server Record Model.
	 *
	 * @params string gateway name
	 *
	 * @return PBXManager_Server_Model
	 */
	public static function getInstance()
	{
		$serverModel = new self();
		$row = (new \App\Db\Query())->from(self::TABLE_NAME)->one();
		if ($row !== false) {
			$serverModel->set('gateway', $row['gateway']);
			$serverModel->set('id', $row['id']);
			$parameters = \App\Json::decode(App\Purifier::decodeHtml($row['parameters']));
			foreach ($parameters as $fieldName => $fieldValue) {
				$serverModel->set($fieldName, $fieldValue);
			}

			return $serverModel;
		}

		return $serverModel;
	}

	public static function checkPermissionForOutgoingCall()
	{
		$permission = Vtiger_Cache::get('outgoingCall', 'PBXManager');
		if ($permission !== false) {
			return $permission ? true : false;
		}
		Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = \App\Privilege::isPermitted('PBXManager', 'MakeOutgoingCalls');

		$serverModel = self::getInstance();
		$gateway = $serverModel->get('gateway');

		if ($permission && $gateway) {
			Vtiger_Cache::set('outgoingCall', 'PBXManager', 1);

			return true;
		} else {
			Vtiger_Cache::set('outgoingCall', 'PBXManager', 0);

			return false;
		}
	}

	public static function generateVtigerSecretKey()
	{
		return uniqid(rand());
	}

	public function getConnector()
	{
		return new PBXManager_PBXManager_Connector();
	}
}
