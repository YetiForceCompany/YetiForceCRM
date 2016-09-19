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

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Export')) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		$exportModel = Vtiger_Export_Model::getInstanceFromRequest($request);
		if ($mode == 'ExportSelectedRecords') {
			$exportModel->setRecordList($this->getRecordsListFromRequest($request));
		}
		$exportModel->exportData($request);
	}
}
