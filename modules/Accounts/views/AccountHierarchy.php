<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Accounts_AccountHierarchy_View extends \App\Controller\View\Page
{
	use App\Controller\ClearProcess;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$hierarchy = $recordModel->getAccountHierarchy();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACCOUNT_HIERARCHY', $hierarchy);
		$viewer->view('AccountHierarchy.tpl', $moduleName);
	}
}
