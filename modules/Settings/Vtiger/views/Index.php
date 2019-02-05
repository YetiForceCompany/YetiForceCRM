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
	use \App\Controller\ExposeMethod;

	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_SYSTEM_SETTINGS';

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::addBasic('view');
		parent::__construct();
		$this->exposeMethod('index');
		$this->exposeMethod('github');
		$this->exposeMethod('systemWarnings');
		$this->exposeMethod('getWarningsList');
		$this->exposeMethod('security');
	}

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	/**
	 * Pre process settings.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessSettings(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$selectedMenuId = $request->getInteger('block', '');
		$fieldId = $request->getInteger('fieldid', '');
		$settingsModel = Settings_Vtiger_Module_Model::getInstance();
		$menuModels = $settingsModel->getMenus();
		$menu = $settingsModel->prepareMenuToDisplay($menuModels, $moduleName, $selectedMenuId, $fieldId);
		if ($settingsModel->has('selected')) {
			$viewer->assign('SELECTED_PAGE', $settingsModel->get('selected'));
		}
		$viewer->assign('MENUS', $menu);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);

			return;
		}
		$this->getViewer($request)->view('SettingsIndexHeader.tpl', $request->getModule(false));
	}

	public function postProcessSettings(\App\Request $request)
	{
		$this->getViewer($request)->view('SettingsMenuEnd.tpl', $request->getModule(false));
	}

	/**
	 * Index.
	 *
	 * @param \App\Request $request
	 */
	public function index(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$usersCount = Users_Record_Model::getCount(true);
		$allWorkflows = Settings_Workflows_Record_Model::getAllAmountWorkflowsAmount();
		$activeModules = Settings_ModuleManager_Module_Model::getModulesCount(true);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
		$warnings = \App\SystemWarnings::getWarnings('all');
		$viewer->assign('WARNINGS_COUNT', count($warnings));
		$viewer->assign('WARNINGS', !App\Session::has('SystemWarnings') ? $warnings : []);
		$viewer->assign('USERS_COUNT', $usersCount);
		$viewer->assign('SECURITY_COUNT', $this->getSecurityCount());
		$viewer->assign('ALL_WORKFLOWS', $allWorkflows);
		$viewer->assign('ACTIVE_MODULES', $activeModules);
		$viewer->assign('SETTINGS_SHORTCUTS', $pinnedSettingsShortcuts);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	public function github(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = 'Settings:Github';
		$clientModel = Settings_Github_Client_Model::getInstance();
		$isAuthor = $request->getBoolean('author');
		$pageNumber = $request->getInteger('page', 1);
		$state = $request->isEmpty('state', true) ? 'open' : $request->getByType('state', 'Text');
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

	/**
	 * Displays warnings system.
	 *
	 * @param \App\Request $request
	 */
	public function systemWarnings(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$folders = array_values(\App\SystemWarnings::getFolders());
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('FOLDERS', \App\Json::encode($folders));
		$viewer->view('SystemWarnings.tpl', $qualifiedModuleName);
	}

	/**
	 * Displays security information.
	 *
	 * @param \App\Request $request
	 */
	public function security(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$folders = array_values(\App\SystemWarnings::getFolders());
		$checker = new SensioLabs\Security\SecurityChecker();
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('FOLDERS', \App\Json::encode($folders));
		try {
			$viewer->assign('SENSIOLABS', $checker->check(ROOT_DIRECTORY));
		} catch (RuntimeException $exc) {
		}
		$viewer->view('Security.tpl', $qualifiedModuleName);
	}

	/**
	 * Displays a list of system warnings.
	 *
	 * @param \App\Request $request
	 */
	public function getWarningsList(\App\Request $request)
	{
		$folder = $request->getArray('folder', 'Text');
		$active = $request->getBoolean('active');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$list = \App\SystemWarnings::getWarnings($folder, $active);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('WARNINGS_LIST', $list);
		$viewer->view('SystemWarningsList.tpl', $qualifiedModuleName);
	}

	/**
	 * Get security alerts count.
	 *
	 * @return int
	 */
	protected function getSecurityCount()
	{
		$count = App\Log::getLogs('access_for_admin', 'oneDay', true);
		$count += App\Log::getLogs('access_to_record', 'oneDay', true);
		$count += App\Log::getLogs('access_for_api', 'oneDay', true);
		return $count + App\Log::getLogs('access_for_user', 'oneDay', true);
	}

	protected function getMenu()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$type = \App\Process::$processName;
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts([
				'modules.Vtiger.resources.Vtiger',
				'~vendor/ckeditor/ckeditor/ckeditor.js',
				'~vendor/ckeditor/ckeditor/adapters/jquery.js',
				'~libraries/jstree/dist/jstree.js',
				'~libraries/datatables.net/js/jquery.dataTables.js',
				'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
				'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
				'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js',
				'modules.Settings.Vtiger.resources.Vtiger',
				'modules.Settings.Vtiger.resources.Edit',
				'modules.Settings.Vtiger.resources.Index',
				'modules.Vtiger.resources.List',
				'modules.Settings.Vtiger.resources.List',
				"modules.Settings.$moduleName.resources.Index",
				"modules.Settings.$moduleName.resources.$type",
				"modules.Settings.$moduleName.resources.$moduleName",
			])
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]), parent::getHeaderCss($request));
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

	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}
