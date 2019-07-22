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
	}

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function postProcess(App\Request $request, $display = true)
	{
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	/**
	 * Pre process settings.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessSettings(App\Request $request)
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

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Vtiger_MenuItem_Model::getPinnedItems();
		$warnings = \App\SystemWarnings::getWarnings('all');
		$viewer->assign('WARNINGS', !App\Session::has('SystemWarnings') ? $warnings : []);
		$systemMonitoring = [
			'WARNINGS_COUNT' => [
				'LABEL' => 'PLU_SYSTEM_WARNINGS',
				'VALUE' => \count($warnings),
				'HREF' => 'index.php?module=Logs&parent=Settings&view=SystemWarnings',
				'ICON' => 'fas fa-exclamation-triangle'
			],
			'SECURITY_COUNT' => [
				'LABEL' => 'PLU_SECURITY',
				'VALUE' => $this->getSecurityCount(),
				'HREF' => 'index.php?module=Log&parent=Settings&view=Index',
				'ICON' => 'fas fa-bug'
			],
			'USERS_COUNT' => [
				'LABEL' => 'PLU_USERS',
				'VALUE' => Users_Record_Model::getCount(true),
				'HREF' => 'index.php?module=Users&parent=Settings&view=List',
				'ICON' => 'adminIcon-user'
			],
			'ACTIVE_MODULES' => [
				'LABEL' => 'PLU_MODULES',
				'VALUE' => Settings_ModuleManager_Module_Model::getModulesCount(true),
				'HREF' => 'index.php?module=ModuleManager&parent=Settings&view=List',
				'ICON' => 'adminIcon-modules-installation'
			],
			'ALL_WORKFLOWS' => [
				'LABEL' => 'PLU_WORKFLOWS_ACTIVE',
				'VALUE' => Settings_Workflows_Record_Model::getAllAmountWorkflowsAmount(),
				'HREF' => 'index.php?module=Workflows&parent=Settings&view=List',
				'ICON' => 'adminIcon-triggers'
			],
		];
		$viewer->assign('SYSTEM_MONITORING', $systemMonitoring);
		$viewer->assign('SETTINGS_SHORTCUTS', $pinnedSettingsShortcuts);
		$viewer->assign('PRODUCTS_PREMIUM', \App\YetiForce\Shop::getProducts('featured'));
		$viewer->assign('PRODUCTS_PARTNER', \App\YetiForce\Shop::getProducts('featured', 'Partner'));
		$viewer->assign('PAYPAL_URL', \App\YetiForce\Shop::getPaypalUrl());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Post process settings.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function postProcessSettings(App\Request $request)
	{
		$this->getViewer($request)->view('SettingsMenuEnd.tpl', $request->getModule(false));
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
	public function getFooterScripts(App\Request $request)
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
	public function getHeaderCss(App\Request $request)
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
					if (false !== stripos($linkTo, '&module=' . $moduleName) || false !== stripos($linkTo, '?module=' . $moduleName)) {
						return $item;
					}
				}
			}
		}
		return false;
	}

	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
