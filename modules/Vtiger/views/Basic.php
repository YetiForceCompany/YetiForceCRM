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

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);

		if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
		}
		$selectedModule = $request->getModule();
		$viewer->assign('CURRENTDATE', App\Fields\Date::formatToDisplay(date('Y-n-j')));
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('MENUS', $this->getMenu());
		$viewer->assign('BROWSING_HISTORY', Vtiger_BrowsingHistory_Helper::getHistory());
		$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
		$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
		$viewer->assign('MENU_HEADER_LINKS', $this->getMenuHeaderLinks($request));
		if (AppConfig::performance('GLOBAL_SEARCH')) {
			$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());
		}
		if (AppConfig::search('GLOBAL_SEARCH_SELECT_MODULE')) {
			$viewer->assign('SEARCHED_MODULE', $selectedModule);
		}
		$viewer->assign('REMINDER_ACTIVE', $activeReminder);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function getMenu()
	{
		return Vtiger_Menu_Model::getAll(true);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/clockpicker/dist/bootstrap4-clockpicker.js',
			'~libraries/inputmask/dist/jquery.inputmask.bundle.js',
			'~libraries/mousetrap/mousetrap.js',
			'modules.Vtiger.resources.Menu',
			'modules.Vtiger.resources.Header',
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'~layouts/resources/Field.js',
			"modules.$moduleName.resources.Field",
			'~layouts/resources/validator/BaseValidator.js',
			'~layouts/resources/validator/FieldValidator.js',
			"modules.$moduleName.resources.validator.FieldValidator",
			'modules.Vtiger.resources.BasicSearch',
			"modules.$moduleName.resources.BasicSearch",
			'modules.Vtiger.resources.AdvanceFilter',
			'modules.Vtiger.resources.ConditionBuilder',
			"modules.$moduleName.resources.AdvanceFilter",
			'modules.Vtiger.resources.SearchAdvanceFilter',
			"modules.$moduleName.resources.SearchAdvanceFilter",
			'modules.Vtiger.resources.AdvanceSearch',
			"modules.$moduleName.resources.AdvanceSearch",
			'~libraries/html2canvas/dist/html2canvas.js',
		]));
	}
}
