<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Webforms_List_View extends Settings_Vtiger_List_View {
	
	function preProcess(Vtiger_Request $request, $display=true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('DESCRIPTION', 'LBL_ALLOWS_YOU_TO_MANAGE_WEBFORMS');
		parent::preProcess($request, false);
	}

	public function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);

		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
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
			'modules.Vtiger.resources.List',
			'modules.Settings.Vtiger.resources.List',
			"modules.Settings.$moduleName.resources.List",
			"modules.Settings.$moduleName.resources.Edit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}