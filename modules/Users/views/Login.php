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

	public function loginRequired()
	{
		return false;
	}

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);

		$selectedModule = $request->getModule();
		$companyDetails = App\Company::getInstanceById();
		$companyLogo = $companyDetails->getLogo();
		$viewer->assign('MODULE', $selectedModule);
		$viewer->assign('MODULE_NAME', $selectedModule);
		$viewer->assign('QUALIFIED_MODULE', $selectedModule);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('COMPANY_LOGO', $companyLogo);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('CURRENT_VERSION', \App\Version::get());
		$viewer->assign('LANGUAGE_SELECTION', AppConfig::main('langInLoginView'));
		$viewer->assign('LAYOUT_SELECTION', AppConfig::main('layoutInLoginView'));
		$viewer->assign('ERROR', $request->get('error'));
		$viewer->assign('FPERROR', $request->get('fpError'));
		$viewer->assign('STATUS', $request->get('status'));
		$viewer->assign('STATUS_ERROR', $request->get('statusError'));
		$viewer->view('Login.tpl', 'Users');
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = [
			'skins.login',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
