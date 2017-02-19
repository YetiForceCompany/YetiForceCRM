<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Description:  Contains a variety of utility functions used to display UI
 * components such as top level menus,more menus,header links,crm logo,global search
 * and quick links of header part
 * footer is also loaded
 * function that connect to db connector to get data
 * ******************************************************************************
 * Contributor(s): YetiForce.com */

abstract class Vtiger_Basic_View extends Vtiger_Footer_View
{

	public function __construct()
	{
		parent::__construct();
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);

		if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
		}
		$selectedModule = $request->getModule();
		$companyDetails = App\Company::getInstanceById();
		$companyLogo = $companyDetails->getLogo();
		$currentDate = Vtiger_Date_UIType::getDisplayDateValue(date('Y-n-j'));
		$viewer->assign('CURRENTDATE', $currentDate);
		$viewer->assign('MODULE', $selectedModule);
		$viewer->assign('MODULE_NAME', $selectedModule);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('PARENT_MODULE', $request->get('parent'));
		$viewer->assign('MENUS', $this->getMenu());
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('COMPANY_LOGO', $companyLogo);

		$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
		$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
		$viewer->assign('MENU_HEADER_LINKS', $this->getMenuHeaderLinks($request));
		if (AppConfig::performance('GLOBAL_SEARCH')) {
			$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());
		}
		if (AppConfig::search('GLOBAL_SEARCH_SELECT_MODULE')) {
			$viewer->assign('SEARCHED_MODULE', $selectedModule);
		}
		$viewer->assign('CHAT_ACTIVE', \App\Module::isModuleActive('AJAXChat'));
		$viewer->assign('REMINDER_ACTIVE', $activeReminder);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function getMenu()
	{
		return Vtiger_Menu_Model::getAll(true);
	}

	protected function preProcessTplName(Vtiger_Request $request)
	{
		return 'BasicHeader.tpl';
	}

	//Note: To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	public function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		parent::postProcess($request);
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
			'libraries.bootstrap.js.eternicode-bootstrap-datepicker.js.bootstrap-datepicker',
			'~libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/locales/bootstrap-datepicker.' . Vtiger_Language_Handler::getShortLanguageName() . '.js',
			'~libraries/jquery/timepicker/jquery.timepicker.min.js',
			'~libraries/jquery/clockpicker/jquery-clockpicker.js',
			'~libraries/jquery/inputmask/jquery.inputmask.js',
			'~libraries/jquery/mousetrap/mousetrap.min.js',
			'modules.Vtiger.resources.Menu',
			'modules.Vtiger.resources.Header',
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'modules.Vtiger.resources.Popup',
			"modules.$moduleName.resources.Popup",
			'modules.Vtiger.resources.Field',
			"modules.$moduleName.resources.Field",
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.$moduleName.resources.validator.FieldValidator",
			'libraries.jquery.jquery_windowmsg',
			'modules.Vtiger.resources.BasicSearch',
			"modules.$moduleName.resources.BasicSearch",
			'modules.Vtiger.resources.AdvanceFilter',
			"modules.$moduleName.resources.AdvanceFilter",
			'modules.Vtiger.resources.SearchAdvanceFilter',
			"modules.$moduleName.resources.SearchAdvanceFilter",
			'modules.Vtiger.resources.AdvanceSearch',
			"modules.$moduleName.resources.AdvanceSearch",
			'modules.Settings.DataAccess.resources.SaveResult',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getGuiderModels(Vtiger_Request $request)
	{
		return [];
	}
}
