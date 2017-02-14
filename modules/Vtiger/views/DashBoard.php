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

class Vtiger_DashBoard_View extends Vtiger_Index_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcessAjax(\Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		if (empty($sourceModule)) {
			$sourceModule = $moduleName;
		}
		$currentDashboard = $request->get('dashboardId');
		if (empty($currentDashboard)) {
			$currentDashboard = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards('Header');
		} else {
			$widgets = [];
		}
		$modulesWithWidget = Vtiger_DashBoard_Model::getModulesWithWidgets($sourceModule, $currentDashboard);
		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('MODULES_WITH_WIDGET', $modulesWithWidget);
		$viewer->assign('USER_PRIVILEGES_MODEL', $userPrivilegesModel);
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE_NAME', $sourceModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->view('dashboards/DashBoardPreProcessAjax.tpl', $moduleName);
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = $request->get('dashboardId');
		if (empty($currentDashboard)) {
			$currentDashboard = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards('Header');
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

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'dashboards/DashBoardPreProcess.tpl';
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = $request->get('dashboardId');
		if (empty($currentDashboard)) {
			$currentDashboard = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
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

	public function postProcess(Vtiger_Request $request)
	{
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
			'~libraries/jquery/gridster/jquery.gridster.min.js',
			'~libraries/jquery/flot/jquery.flot.min.js',
			'~libraries/jquery/flot/jquery.flot.pie.min.js',
			'~libraries/jquery/flot/jquery.flot.stack.min.js',
			'~libraries/jquery/jqplot/jquery.jqplot.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.pieRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.barRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.pointLabels.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.funnelRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.barRenderer.min.js',
			'~libraries/jquery/jqplot/plugins/jqplot.logAxisRenderer.min.js',
			'modules.Vtiger.resources.DashBoard',
			'modules.' . $moduleName . '.resources.DashBoard',
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/fullcalendar/moment.min.js',
			'~libraries/fullcalendar/fullcalendar.js'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_CssScript_Model instances
	 */
	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentHeaderCssScriptInstances = parent::getHeaderCss($request);

		$headerCss = array(
			'~libraries/jquery/gridster/jquery.gridster.min.css',
			'~libraries/jquery/jqplot/jquery.jqplot.min.css',
			'~libraries/fullcalendar/fullcalendar.min.css',
			'~libraries/fullcalendar/fullcalendarCRM.css'
		);
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);
		return $headerCssScriptInstances;
	}
}
