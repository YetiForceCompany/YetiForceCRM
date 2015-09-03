<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_View extends Vtiger_View_Controller {

	function loginRequired() {
		return false;
	}
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}
	
	function preProcess (Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		
		$selectedModule = $request->getModule();
		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();
		$viewer->assign('MODULE', $selectedModule);
		$viewer->assign('MODULE_NAME', $selectedModule);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('COMPANY_LOGO',$companyLogo);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if($display) {
			$this->preProcessDisplay($request);
		}
	}
	
	public function postProcess(Vtiger_Request $request) {	}
	
	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_VERSION', vglobal('YetiForce_current_version'));
		$viewer->view('Login.tpl', 'Users');
	}
	
	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~layouts/vlayout/skins/login.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
