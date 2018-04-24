<?php

/**
 * Widget as a chart with a filter.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_ChartFilter_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, \App\User::getCurrentUserId());
		$chartFilterWidgetModel = Vtiger_ChartFilter_Model::getInstance();
		$chartFilterWidgetModel->setWidgetModel($widget);
		$additionalFilterFields= $chartFilterWidgetModel->getAdditionalFiltersFields();
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CHART_MODEL', $chartFilterWidgetModel);
		$viewer->assign('ADDITIONAL_FILTERS_FIELDS', $additionalFilterFields);
		$viewer->assign('CHART_STACKED', $chartFilterWidgetModel->isStacked() ? 1 : 0);
		$viewer->assign('CHART_COLORS_FROM_DIVIDING_FIELD', $chartFilterWidgetModel->areColorsFromDividingField() ? 1 : 0);
		$viewer->assign('CHART_COLORS_FROM_FILTERS', $chartFilterWidgetModel->areColorsFromFilter() ? 1 : 0);
		$viewer->assign('ADDITIONAL_FILTER_FIELD_VALUE', []);
		if ($request->has('additional_filter_field')) {
			$additionalFilterFieldsValues = $request->getArray('additional_filter_field');
			$viewer->assign('ADDITIONAL_FILTER_FIELD_VALUE', $additionalFilterFieldsValues);
			$chartFilterWidgetModel->set('additionalFiltersFieldsSearch', $additionalFilterFieldsValues);
		}
		$viewer->assign('CHART_DATA', $chartFilterWidgetModel->getChartData());
		if ($owners = $chartFilterWidgetModel->getRowsOwners()) {
			$viewer->assign('CHART_OWNERS', $owners);
		}
		if ($request->has('content')) {
			$viewer->view('dashboards/ChartFilterContents.tpl', $moduleName);
		} else {
			$widget->set('title', $chartFilterWidgetModel->getTitle());
			$viewer->view('dashboards/ChartFilterHeader.tpl', $moduleName);
		}
	}
}
