<?php

/**
 * Multifilter dashboard .
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Vtiger_Multifilter_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}
		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, \App\User::getCurrentUserId());
		if ($widget->get('data')) {
			$showFullName = \App\Json::decode(App\Purifier::decodeHtml($widget->get('data')))['showFullName'] ?? 0;
		}
		$viewer->assign('WIDGET_SHOW_FULL_NAME', $showFullName ?? 0);
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

	/**
	 * Set widget specific data.
	 *
	 * @param Vtiger_Widget_Model $widget
	 * @param array               $data
	 *
	 * @return void
	 */
	public function setWidgetData(Vtiger_Widget_Model $widget, array $data)
	{
		$filters = array_keys(CustomView_Record_Model::getAll());
		$widgetData = ['customMultiFilter' => []];
		if ($widget->get('data') && 'null' !== $widget->get('data')) {
			$widgetData = array_merge($widgetData, \App\Json::decode(App\Purifier::decodeHtml($widget->get('data'))));
		}
		foreach ($widgetData as $key => &$value) {
			if (!empty($data[$key]) && !array_diff($data[$key], $filters)) {
				$value = $data[$key];
			}
		}
		$widget->set('data', \App\Json::encode($widgetData));
	}
}
