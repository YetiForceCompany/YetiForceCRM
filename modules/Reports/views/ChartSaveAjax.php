<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Reports_ChartSaveAjax_View extends Vtiger_IndexAjax_View
{
	public function checkPermission(\App\Request $request)
	{
		$record = $request->getInteger('record');
		if (!$record) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule()) && !$reportModel->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->getInteger('record');
		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportModel->setModule('Reports');
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));

		$secondaryModules = $reportModel->getSecondaryModules();
		if (empty($secondaryModules)) {
			$viewer->assign('CLICK_THROUGH', true);
		}

		$dataFields = $request->get('datafields', 'count(*)');
		if (is_string($dataFields)) {
			$dataFields = [$dataFields];
		}

		$reportModel->set('reporttypedata', \App\Json::encode([
				'type' => $request->get('charttype', 'pieChart'),
				'groupbyfield' => $request->get('groupbyfield'),
				'datafields' => $dataFields, ]
		));
		$reportModel->set('reporttype', 'chart');
		$reportModel->save();

		$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);

		$data = $reportChartModel->getData();
		$viewer->assign('CHART_TYPE', $reportChartModel->getChartType());
		$viewer->assign('DATA', json_encode($data, JSON_HEX_APOS));
		$viewer->assign('MODULE', $moduleName);

		$viewer->view('ChartReportContents.tpl', $moduleName);
	}
}
