<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Index_View extends Vtiger_Basic_View {

	public function preProcess (Vtiger_Request $request) {
		parent::preProcess($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($currentUserModel->isAdminUser()) {
			$settingsIndexView = new Settings_Vtiger_Index_View();
			$settingsIndexView->preProcessSettings($request);
		}
	}

	public function postProcess(Vtiger_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($currentUserModel->isAdminUser()) {
			$settingsIndexView = new Settings_Vtiger_Index_View();
			$settingsIndexView->postProcessSettings($request);
		}
		parent::postProcess($request);
	}

	public function process(Vtiger_Request $request) {
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.Vtiger',
			"modules.$moduleName.resources.$moduleName",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}