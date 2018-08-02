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

class Accounts_AccountHierarchy_View extends \App\Controller\View
{
	use App\Controller\ClearProcess;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	private function getLastModified($id)
	{
		return (new \App\Db\Query())->from('u_#__crmentity_last_changes')->where(['crmid' => $id, 'fieldname' => 'active'])->one();
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

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
}
