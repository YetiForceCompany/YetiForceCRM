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

class Settings_Profiles_Edit_View extends Settings_Vtiger_Index_View
{
	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($request->getInteger('record'));
			$title = $recordModel->getName();
		} else {
			$title = \App\Language::translate('LBL_VIEW_EDIT', $moduleName);
		}
		return $title;
	}

	public function process(App\Request $request)
	{
		$this->initialize($request);
		$qualifiedModuleName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	public function initialize(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->getInteger('record');
		$fromRecord = $request->getInteger('from_record');

		if (!empty($record)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} elseif (!empty($fromRecord)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($fromRecord);
			$recordModel->getModulePermissions();
			$recordModel->getGlobalPermissions();
			$recordModel->set('profileid', '');
			$viewer->assign('MODE', '');
			$viewer->assign('IS_DUPLICATE_RECORD', $fromRecord);
		} else {
			$recordModel = new Settings_Profiles_Record_Model();
			$viewer->assign('MODE', '');
		}
		$viewer->assign('ALL_PROFILES', $recordModel->getAll());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ALL_BASIC_ACTIONS', Vtiger_Action_Model::getAllBasic(true));
		$viewer->assign('ALL_UTILITY_ACTIONS', Vtiger_Action_Model::getAllUtility(true));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.Edit',
			'modules.Settings.' . $request->getModule() . '.resources.Edit',
		]));
	}
}
