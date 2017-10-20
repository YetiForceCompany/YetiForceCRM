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

class Vtiger_MassDelete_Action extends Vtiger_Mass_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
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
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if ($request->get('selected_ids') === 'all' && $request->getMode() === 'FindDuplicates') {
			$recordIds = Vtiger_FindDuplicate_Model::getMassDeleteRecords($request);
		} else {
			$recordIds = self::getRecordsListFromRequest($request);
		}
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			if ($recordModel->isDeletable()) {
				$recordModel->delete();
			} else {
				throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $request->getByType('viewname', 2), 'module' => $moduleName]);
		$response->emit();
	}
}
