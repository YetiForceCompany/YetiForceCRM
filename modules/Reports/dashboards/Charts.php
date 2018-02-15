<?php

/**
 * Wdiget to show chart from reports.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Reports_Charts_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$widgetId = $request->getInteger('widgetid');
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());

		$data = [];
		$typeChart = '';
		$reportId = json_decode($widget->get('data'), true);
		$reportId = $reportId['reportId'];
		if (!empty($reportId)) {
			$reportModel = Reports_Record_Model::getInstanceById($reportId);
			$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
			$typeChart = $reportChartModel->getChartType();
			$data = $reportChartModel->getData();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('CHART_TYPE', $typeChart);
		$viewer->assign('SCRIPTS', $this->getScripts($typeChart));
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/ChartsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Charts.tpl', $moduleName);
		}
	}

	public function getScripts($chartType)
	{
		$jsFileNames = [
			'modules.Reports.resources.TypeCharts',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		return $jsScriptInstances;
	}
}
