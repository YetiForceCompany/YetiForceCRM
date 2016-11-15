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

class Accounts_AccountHierarchy_View extends Vtiger_View_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		
	}

	private function getLastModified($id)
	{
		return (new \App\Db\Query())->from('u_#__crmentity_last_changes')
				->where(['crmid' => $id, 'fieldname' => 'active'])
				->one();
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$hierarchy = $recordModel->getAccountHierarchy();
		$listColumns = AppConfig::module('Accounts', 'COLUMNS_IN_HIERARCHY');
		$lastModifiedField = [];
		if (!empty($listColumns) && in_array('active', $listColumns)) {
			foreach ($hierarchy['entries'] as $crmId => $entry) {
				$lastModified = $this->getLastModified($crmId);
				if ($lastModified) {
					$lastModifiedField[$crmId]['active']['userModel'] = Vtiger_Record_Model::getInstanceById($lastModified['user_id'], 'Users');
					$lastModifiedField[$crmId]['active']['changedon'] = (new DateTimeField($lastModified['date_updated']))->getFullcalenderDateTimevalue();
				}
			}
		}
		$viewer->assign('LAST_MODIFIED', $lastModifiedField);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACCOUNT_HIERARCHY', $hierarchy);
		$viewer->view('AccountHierarchy.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}
}
