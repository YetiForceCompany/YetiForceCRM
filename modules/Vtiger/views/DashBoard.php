<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_DashBoard_View extends Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $request->getModule();
		if ('Home' === $moduleName && !$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('Home' !== $moduleName && !$userPrivilegesModel->hasModuleActionPermission($moduleName, 'Dashboard')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if (empty($sourceModule)) {
			$sourceModule = $moduleName;
		}
		$currentDashboard = Vtiger_Widget_Model::getDashboardId($request);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards(0);
		} else {
			$widgets = [];
		}
		$viewer->assign('MODULES_WITH_WIDGET', 'Home' === $sourceModule ? Vtiger_DashBoard_Model::getModulesWithWidgets($sourceModule, $currentDashboard) : []);
		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('USER_PRIVILEGES_MODEL', $userPrivilegesModel);
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE_NAME', $sourceModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->view('dashboards/DashBoardPreProcessAjax.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = Vtiger_Widget_Model::getDashboardId($request);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards(0);
		} else {
			$widgets = [];
		}

		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('USER_PRIVILEGES_MODEL', $userPrivilegesModel);
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/** {@inheritdoc} */
	public function preProcessTplName(App\Request $request)
	{
		return 'dashboards/DashBoardPreProcess.tpl';
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = Vtiger_Widget_Model::getDashboardId($request);
		$_SESSION['DashBoard'][$moduleName]['LastDashBoardId'] = $currentDashboard;
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if ($permission) {
			$widgets = $dashBoardModel->getDashboards();
		} else {
			return;
		}
		$viewer->assign('WIDGETS', $widgets);
		$viewer->view('dashboards/DashBoardContents.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			'~libraries/lodash/lodash.js',
			'~libraries/gridstack/dist/gridstack-h5.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'~libraries/chart.js/dist/Chart.js',
			'~libraries/chartjs-plugin-funnel/dist/chart.funnel.js',
			'~libraries/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js',
			'~libraries/jquery-lazy/jquery.lazy.js',
			'~layouts/resources/Calendar.js',
			'modules.Vtiger.resources.DashBoard',
			'modules.' . $moduleName . '.resources.DashBoard',
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/fullcalendar/main.js',
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * Function to get the list of Css models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(App\Request $request)
	{
		$headerCss = [
			'~libraries/gridstack/dist/gridstack.css',
			'~libraries/fullcalendar/main.css',
		];

		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($headerCss));
	}
}
