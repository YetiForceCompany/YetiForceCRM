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

class Settings_Profiles_Detail_View extends Settings_Vtiger_Index_View
{
	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($request->getInteger('record'));
			$title = $recordModel->getName();
		} else {
			$title = \App\Language::translate('LBL_VIEW_DETAIL', $moduleName);
		}
		return $title;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ALL_BASIC_ACTIONS', Vtiger_Action_Model::getAllBasic(true));
		$viewer->assign('ALL_UTILITY_ACTIONS', Vtiger_Action_Model::getAllUtility(true));
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}
