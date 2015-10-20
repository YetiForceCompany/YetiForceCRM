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

class Users_Login_View extends Vtiger_View_Controller
{

	function loginRequired()
	{
		return false;
	}

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}

	function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		include_once 'config/api.php';
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ENABLED_MOBILE_MODULE', in_array('mobileModule', $enabledServices));
		$viewer->assign('CURRENT_VERSION', vglobal('YetiForce_current_version'));
		$viewer->assign('LANGUAGE_SELECTION', vglobal('langInLoginView'));
		$viewer->view('Login.tpl', 'Users');
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~layouts/vlayout/skins/login.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
