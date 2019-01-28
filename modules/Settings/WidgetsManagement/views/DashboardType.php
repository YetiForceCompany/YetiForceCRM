<?php

/**
 * Form to add/edit dashboard.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_DashboardType_View extends Settings_Vtiger_BasicModal_View
{
	public function process(\App\Request $request)
	{
		$dashboardId = $request->getInteger('dashboardId');
		$dashboardInfo = Settings_WidgetsManagement_Module_Model::getDashboardInfo($dashboardId);
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('DASHBOARD_ID', $dashboardId);
		$viewer->assign('DASHBOARD_NAME', $dashboardInfo['name']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->view('DashboardType.tpl', $moduleName);
	}
}
