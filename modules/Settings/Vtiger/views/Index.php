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

class Settings_Vtiger_Index_View extends Vtiger_Basic_View
{

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::addBasic('view');
		parent::__construct();
		$this->exposeMethod('DonateUs');
		$this->exposeMethod('index');
		$this->exposeMethod('github');
		$this->exposeMethod('systemWarnings');
		$this->exposeMethod('getWarningsList');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	public function preProcessSettings(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$selectedMenuId = $request->get('block');
		$fieldId = $request->get('fieldid');
		$settingsModel = Settings_Vtiger_Module_Model::getInstance();
		$menuModels = $settingsModel->getMenus();
		$menu = $settingsModel->prepareMenuToDisplay($menuModels, $moduleName, $selectedMenuId, $fieldId);
		if ($settingsModel->has('selected')) {
			$viewer->assign('SELECTED_PAGE', $settingsModel->get('selected'));
		}
		$viewer->assign('SELECTED_MENU', $selectedMenuId);
		$viewer->assign('SETTINGS_MENUS', $menuModels); // used only in old layout 
		$viewer->assign('MENUS', $menu);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->getViewer($request)->view('SettingsIndexHeader.tpl', $request->getModule(false));
	}

	public function postProcessSettings(Vtiger_Request $request)
	{
		$this->getViewer($request)->view('SettingsMenuEnd.tpl', $request->getModule(false));
	}

	/**
	 * Index
	 * @param Vtiger_Request $request
	 */
	public function index(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$usersCount = Users_Record_Model::getCount(true);
		$allWorkflows = Settings_Workflows_Record_Model::getAllAmountWorkflowsAmount();
		$activeModules = Settings_ModuleManager_Module_Model::getModulesCount(true);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
		$warnings = \App\SystemWarnings::getWarnings('all');

		$viewer->assign('WARNINGS_COUNT', count($warnings));
		$viewer->assign('WARNINGS', !Vtiger_Session::has('SystemWarnings') ? $warnings : []);
		$viewer->assign('USERS_COUNT', $usersCount);
		$viewer->assign('ALL_WORKFLOWS', $allWorkflows);
		$viewer->assign('ACTIVE_MODULES', $activeModules);
		$viewer->assign('SETTINGS_SHORTCUTS', $pinnedSettingsShortcuts);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function github(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = 'Settings:Github';
		$clientModel = Settings_Github_Client_Model::getInstance();
		$isAuthor = $request->get('author');
		$isAuthor = $isAuthor == 'true' ? true : false;
		$pageNumber = $request->get('page');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$state = empty($request->get('state')) ? 'open' : $request->get('state');
		$issues = $clientModel->getAllIssues($pageNumber, $state, $isAuthor);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('totalCount', Settings_Github_Issues_Model::$totalCount);

		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('IS_AUTHOR', $isAuthor);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('ISSUES_STATE', $state);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', false);
		$viewer->assign('LISTVIEW_COUNT', Settings_Github_Issues_Model::$totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('GITHUB_ISSUES', $issues);
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->view('Github.tpl', $qualifiedModuleName);
	}

	public function DonateUs(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('DonateUs.tpl', $qualifiedModuleName);
	}

	/**
	 * Displays warnings system
	 * 
	 * @param Vtiger_Request $request
	 */
	public function systemWarnings(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$folders = array_values(\App\SystemWarnings::getFolders());
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('FOLDERS', \App\Json::encode($folders));
		$viewer->view('SystemWarnings.tpl', $qualifiedModuleName);
	}

	/**
	 * Displays a list of system warnings
	 * 
	 * @param Vtiger_Request $request
	 */
	public function getWarningsList(Vtiger_Request $request)
	{
		$folder = $request->get('folder');
		$active = $request->getBoolean('active');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$list = \App\SystemWarnings::getWarnings($folder, $active);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('WARNINGS_LIST', $list);
		$viewer->view('SystemWarningsList.tpl', $qualifiedModuleName);
	}

	protected function getMenu()
	{
		return [];
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
			'modules.Vtiger.resources.Vtiger',
			'libraries.jquery.ckeditor.ckeditor',
			'libraries.jquery.ckeditor.adapters.jquery',
			'libraries.jquery.jstree.jstree',
			'~libraries/jquery/datatables/media/js/jquery.dataTables.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
			'modules.Vtiger.resources.CkEditor',
			'modules.Settings.Vtiger.resources.Vtiger',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.$moduleName",
			'modules.Settings.Vtiger.resources.Index',
			"modules.Settings.$moduleName.resources.Index",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return array_merge($headerScriptInstances, $jsScriptInstances);
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			'libraries.jquery.jstree.themes.proton.style',
			'~libraries/jquery/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($cssInstances, $headerCssInstances);
	}

	public static function getSelectedFieldFromModule($menuModels, $moduleName)
	{
		if ($menuModels) {
			foreach ($menuModels as $menuModel) {
				$menuItems = $menuModel->getMenuItems();
				foreach ($menuItems as $item) {
					$linkTo = $item->getUrl();
					if (stripos($linkTo, '&module=' . $moduleName) !== false || stripos($linkTo, '?module=' . $moduleName) !== false) {
						return $item;
					}
				}
			}
		}
		return false;
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}
