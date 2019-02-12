<?php

/**
 * Multifilter dashboard .
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Vtiger_Multifilter_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, \App\User::getCurrentUserId());
		if ($request->has('content')) {
			if ($request->has('modulename')) {
				$modulesName = $request->getByType('modulename', 2);
			} else {
				return;
			}
			if ($request->has('filterid')) {
				$filterId = $request->getInteger('filterid');
			} else {
				return;
			}
			if (!$request->has('owner')) {
				$owner = \App\User::getCurrentUserId();
			} else {
				$owner = $request->getByType('owner', 2);
			}
			$multifilterModel = new Vtiger_Multifilter_Model();
			$multifilterModel->setModulesName($modulesName);
			$multifilterModel->setFilterId($filterId);
			$multifilterModel->setWidgetModel($widget);
			$customView = CustomView_Record_Model::getInstanceById($filterId);
			$viewer->assign('LIST_VIEW_URL', $multifilterModel->getListViewURL());
			$viewer->assign('CUSTOM_VIEW_NAME', $customView->get('viewname'));
			$viewer->assign('CUSTOM_VIEW_ID', $filterId);
			$viewer->assign('OWNER', $owner);
			$viewer->assign('MULTIFILTER_WIDGET_MODEL', $multifilterModel);
			$viewer->assign('BASE_MODULE', $multifilterModel->getTargetModule());
			$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
			$viewer->view('dashboards/MultifilterContents.tpl', $moduleName);
		} else {
			if ($widget->get('data')) {
				$widgetActiveFilters = \App\Json::decode(App\Purifier::decodeHtml($widget->get('data')))['customMultiFilter'] ?? [];
			}
			$viewer->assign('WIDGET', $widget);
			$viewer->assign('WIDGET_ACTIVE_FILTERS', $widgetActiveFilters ?? []);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->view('dashboards/Multifilter.tpl', $moduleName);
		}
	}
}
