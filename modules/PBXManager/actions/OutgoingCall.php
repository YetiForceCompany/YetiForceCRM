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

class PBXManager_OutgoingCall_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$serverModel = PBXManager_Server_Model::getInstance();
		$gateway = $serverModel->get('gateway');
		$response = new Vtiger_Response();
		$user = Users_Record_Model::getCurrentUserModel();
		$userNumber = $user->phone_crm_extension;

		if ($gateway && $userNumber) {
			try {
				$number = $request->get('number');
				$connector = $serverModel->getConnector();
				$result = $connector->call($number);
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
