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

class Vtiger_MiniList_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}

		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, \App\User::getCurrentUserId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', \App\Purifier::TEXT);
		}
		$miniListWidgetModel = new Vtiger_MiniList_Model();
		$miniListWidgetModel->setWidgetModel($widget);
		$searchParams = App\Condition::validSearchParams($miniListWidgetModel->getTargetModule(), $request->getArray('search_params'));
		if (!empty($searchParams) && \is_array($searchParams)) {
			$miniListWidgetModel->setSearchParams($searchParams);
			foreach ($request->getArray('search_params') as $fieldListGroup) {
				foreach ($fieldListGroup as $fieldSearchInfo) {
					$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
					$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
					$fieldSearchInfo['specialOption'] = \in_array($fieldSearchInfo[1], ['ch', 'kh']) ? true : '';
					$searchParams[$fieldName] = $fieldSearchInfo;
				}
			}
		}

		$fieldHref = $filterField = false;
		if ($widget->get('data')) {
			$widgetParams = \App\Json::decode($widget->get('data'));
			$fieldHref = $widgetParams['fieldHref'] ?? false;
			if (!empty($widgetParams['filterFields'])) {
				$filterField = Vtiger_Field_Model::getInstanceFromFieldId($widgetParams['filterFields']);
			}
		}

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('MINILIST_WIDGET_MODEL', $miniListWidgetModel);
		$viewer->assign('BASE_MODULE', $miniListWidgetModel->getTargetModule());
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('DATA', $data);
		$viewer->assign('FILTER_FIELD', $filterField);
		$viewer->assign('FIELD_HREF', $fieldHref);
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		if ($request->has('content')) {
			$viewer->view('dashboards/MiniListContents.tpl', $moduleName);
			$viewer->view('dashboards/MiniListFooter.tpl', $moduleName);
		} else {
			$widget->set('title', $miniListWidgetModel->getTitle());
			$viewer->view('dashboards/MiniList.tpl', $moduleName);
		}
	}
}
