<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Roles_Popup_View extends Vtiger_Footer_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$sourceRecord = $request->get('src_record');

		$sourceRole = Settings_Roles_Record_Model::getInstanceById($sourceRecord);
		$rootRole = Settings_Roles_Record_Model::getBaseRole();
		$allRoles = Settings_Roles_Record_Model::getAll();

		$viewer->assign('SOURCE_ROLE', $sourceRole);
		$viewer->assign('ROOT_ROLE', $rootRole);
		$viewer->assign('ROLES', $allRoles);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('TYPE', $request->get('type'));
		$viewer->assign('MODULE_NAME', $moduleName);

		$viewer->view('Popup.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Settings.Vtiger.resources.Popup',
			"modules.Settings.$moduleName.resources.Popup",
			"modules.Settings.$moduleName.resources.$moduleName",
			'libraries.jquery.jquery_windowmsg',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	protected function showBodyHeader()
	{
		return false;
	}
}
