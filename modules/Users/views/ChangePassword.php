<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Users_ChangePassword_View extends Vtiger_Index_View
{

	function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER_MODEL', $currentUser);
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view('ChangePasswordUser.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.Users.resources.ChangePassword",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
}
