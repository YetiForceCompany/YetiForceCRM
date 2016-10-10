<?php

class Admin_Users_Login_View extends Vtiger_View_Controller {

	public function loginRequired() {
		return false;
	}
	
	public function checkPermission(Vtiger_Request $request) {
		return true;
	}
	
	public function postProcess(Vtiger_Request $request) {	}
	
	public function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_VERSION', vglobal('YetiForce_current_version'));
		$viewer->view('Login.tpl', 'Users');
	}
	
	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = [
			'skins.login',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
