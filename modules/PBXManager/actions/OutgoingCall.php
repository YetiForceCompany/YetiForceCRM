<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class PBXManager_OutgoingCall_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$serverModel = PBXManager_Server_Model::getInstance();
		$gateway = $serverModel->get("gateway");
		$response = new Vtiger_Response();
		$user = Users_Record_Model::getCurrentUserModel();
		$userNumber = $user->phone_crm_extension;

		if ($gateway && $userNumber) {
			try {
				$number = $request->get('number');
				$recordId = $request->get('record');
				$connector = $serverModel->getConnector();
				$result = $connector->call($number, $recordId);
				$response->setResult($result);
			} catch (Exception $e) {
				throw new Exception($e);
			}
		} else {
			$response->setResult(false);
		}
		$response->emit();
	}
}
