<?php

/**
 * Settings OSSMailView index view class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_WidgetsManagement_Configuration_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$sourceModule = $request->getByType('sourceModule', 2);
		$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
		$dashboardModules = $widgetsManagementModel->getSelectableDashboard();

		if (empty($sourceModule)) {
			$sourceModule = 'Home';
		}
		$currentDashboard = $request->isEmpty('dashboardId', true) ? Settings_WidgetsManagement_Module_Model::getDefaultDashboard() : $request->getInteger('dashboardId');
		$viewer = $this->getViewer($request);
		// get widgets list
		$widgets = $dashboardModules[$sourceModule];
		$dashboardStored = $widgetsManagementModel->getDashboardForModule($sourceModule);
		$defaultValues = $widgetsManagementModel->getDefaultValues();
		$size = $widgetsManagementModel->getSize();
		$widgetsWithLimit = $widgetsManagementModel->getWidgetsWithLimit();
		$authorization = Settings_Roles_Record_Model::getAll();
		$blocks = $widgetsManagementModel->getBlocksId($currentDashboard);
		$specialWidgets = Settings_WidgetsManagement_Module_Model::getSpecialWidgets($sourceModule);
		$filterSelect = $widgetsManagementModel->getFilterSelect();
		$filterSelectDefault = $widgetsManagementModel->getFilterSelectDefault();

		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('FILTER_SELECT', $filterSelect);
		$viewer->assign('FILTER_SELECT_DEFAULT', $filterSelectDefault);
		$viewer->assign('DATE_SELECT_DEFAULT', Settings_WidgetsManagement_Module_Model::getDateSelectDefault());
		$viewer->assign('ALL_AUTHORIZATION', $authorization);
		$viewer->assign('ALL_SERVERS', Settings_WebserviceApps_Module_Model::getServers());
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', array_keys($dashboardModules));
		$viewer->assign('DASHBOARD_AUTHORIZATION_BLOCKS', $blocks[$sourceModule] ?? []);
		$viewer->assign('WIDGETS_AUTHORIZATION_INFO', $dashboardStored);
		$viewer->assign('SPECIAL_WIDGETS', $specialWidgets);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('SIZE', $size);
		$viewer->assign('DEFAULTVALUES', $defaultValues);
		$viewer->assign('TITLE_OF_LIMIT', $widgetsWithLimit);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));

		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
		\App\Log::trace(__METHOD__ . ' | End');
	}
}
