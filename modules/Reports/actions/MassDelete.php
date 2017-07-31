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

class Reports_MassDelete_Action extends Vtiger_Mass_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request)
	{
		return true;
	}

	public function postProcess(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$parentModule = 'Reports';
		$recordIds = Reports_Record_Model::getRecordsListFromRequest($request);

		$reportsDeleteDenied = [];
		foreach ($recordIds as $recordId) {
			$recordModel = Reports_Record_Model::getInstanceById($recordId);
			if (!$recordModel->isDefault() && $recordModel->isEditable()) {
				$success = $recordModel->delete();
				if (!$success) {
					$reportsDeleteDenied[] = \App\Language::translate($recordModel->getName(), $parentModule);
				}
			} else {
				$reportsDeleteDenied[] = \App\Language::translate($recordModel->getName(), $parentModule);
			}
		}

		$response = new Vtiger_Response();
		if (empty($reportsDeleteDenied)) {
			$response->setResult(array(\App\Language::translate('LBL_REPORTS_DELETED_SUCCESSFULLY', $parentModule)));
		} else {
			$response->setError($reportsDeleteDenied, \App\Language::translate('LBL_DENIED_REPORTS', $parentModule));
		}

		$response->emit();
	}
}
