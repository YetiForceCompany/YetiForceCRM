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

class Vtiger_ExportData_Action extends Vtiger_Mass_Action
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
		$moduleName = $request->getByType('source_module', 2);
		if (empty($moduleName)) {
			$moduleName = $request->getModule();
		}
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function is called by the controller.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$exportModel = Vtiger_Export_Model::getInstanceFromRequest($request);
		if ($request->getMode() === 'ExportSelectedRecords') {
			$exportModel->setRecordList($this->getRecordsListFromRequest($request));
		}
		$exportModel->exportData($request);
	}
}
