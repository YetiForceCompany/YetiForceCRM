<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Notebook_Dashboard extends Vtiger_IndexAjax_View {
	
	public function process(Vtiger_Request $request, $widget=NULL) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}
		
		$widget = Vtiger_Notebook_Model::getUserInstance($widgetId);

		$mode = $request->get('mode');
		if ($mode == 'save') {
			$widget->save($request);
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/NotebookContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Notebook.tpl', $moduleName);
		}
		
	}
	
	// NOTE: Move this function to appropriate model.
	protected function getKeyMetricsWithCount() {
		global $current_user, $adb;
		$current_user = Users_Record_Model::getCurrentUserModel();
		
		require_once 'modules/CustomView/ListViewTop.php';
		$metriclists = getMetricList();
		
		foreach ($metriclists as $key => $metriclist) {
			
			$metricresult = NULL;
			if($metriclist['module'] == "Calendar") {
				$listquery = getListQuery($metriclist['module']);
				$oCustomView = new CustomView($metriclist['module']);
				$metricsql = $oCustomView->getModifiedCvListQuery($metriclist['id'],$listquery,$metriclist['module']);
				$metricresult = $adb->query(Vtiger_Functions::mkCountQuery($metricsql));
			} else {
				$queryGenerator = new QueryGenerator($metriclist['module'], $current_user);
				$queryGenerator->initForCustomViewById($metriclist['id']);
				$metricsql = $queryGenerator->getQuery();
				$metricresult = $adb->query(Vtiger_Functions::mkCountQuery($metricsql));
			}
			if($metricresult) {
					$rowcount = $adb->fetch_array($metricresult);
					$metriclists[$key]['count'] = $rowcount['count'];
				}
		}
		return $metriclists;
	}
	
}