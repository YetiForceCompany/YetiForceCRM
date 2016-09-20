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

class SMSNotifier_MassSaveAjax_Action extends Vtiger_Mass_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Save')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function that saves SMS records
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordIds = $this->getRecordsListFromRequest($request);
		$phoneFieldList = $request->get('fields');
		$message = $request->get('message');

		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$numberSelected = false;
			foreach ($phoneFieldList as $fieldname) {
				$fieldValue = $recordModel->get($fieldname);
				if (!empty($fieldValue)) {
					$toNumbers[] = $fieldValue;
					$numberSelected = true;
				}
			}
			if ($numberSelected) {
				$recordIds[] = $recordId;
			}
		}

		$response = new Vtiger_Response();

		if (!empty($toNumbers)) {
			SMSNotifier_Record_Model::SendSMS($message, $toNumbers, $currentUserModel->getId(), $recordIds, $moduleName);
			$response->setResult(true);
		} else {
			$response->setResult(false);
		}
		return $response;
	}
}
