<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_Index_View extends Vtiger_Basic_View
{
	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('DonateUs');
		$this->exposeMethod('Index');
		$this->exposeMethod('Github');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new NoPermittedForAdminException('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request, $display=true)
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
		$viewer->assign('SELECTED_MENU', $selectedMenuId);
		$viewer->assign('SETTINGS_MENUS', $menuModels); // used only in old layout 
		$viewer->assign('MENUS', $menu);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}
	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('SettingsIndexHeader.tpl', $qualifiedModuleName);
	}
	public function postProcessSettings(Vtiger_Request $request)
	{

		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
	}

	public function Index(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$usersCount = Users_Record_Model::getCount(true);
		$activeWorkFlows = Settings_Workflows_Record_Model::getActiveCount();
		$activeModules = Settings_ModuleManager_Module_Model::getModulesCount(true);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();

		$viewer->assign('USERS_COUNT', $usersCount);
		$viewer->assign('ACTIVE_WORKFLOWS', $activeWorkFlows);
		$viewer->assign('ACTIVE_MODULES', $activeModules);
		$viewer->assign('SETTINGS_SHORTCUTS', $pinnedSettingsShortcuts);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
	public function Github(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = 'Settings:Github';
		$clientModel = Settings_Github_Client_Model::getInstance();
		$isAuthor = $request->get('author');
		$isAuthor = $isAuthor == 'true' ? true : false;
		$pageNumber = $request->get('page');
		if(empty($pageNumber)){
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
		$viewer->assign('PAGE_NUMBER',$pageNumber);
		$viewer->assign('ISSUES_STATE',$state);
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
	protected function getMenu() {
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
			"libraries.jquery.ckeditor.ckeditor",
			"libraries.jquery.ckeditor.adapters.jquery",
			'modules.Vtiger.resources.CkEditor',
			'modules.Settings.Vtiger.resources.Vtiger',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.$moduleName",
			'modules.Settings.Vtiger.resources.Index',
			"modules.Settings.$moduleName.resources.Index",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
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
