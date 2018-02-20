<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_ChartSave_Action extends Reports_Save_Action
{
	public function process(\App\Request $request)
	{
		$reportModel = Reports_Record_Model::getCleanInstance();
		$reportModel->setModule('Reports');
		if (!$request->isEmpty('record') && !$request->getBoolean('isDuplicate')) {
			$reportModel->setId($request->getInteger('record'));
		}

		$reportModel->set('reportname', $request->get('reportname'));
		$reportModel->set('folderid', $request->getInteger('folderid'));
		$reportModel->set('description', $request->get('reports_description'));

		$reportModel->setPrimaryModule($request->getByType('primary_module'));

		$secondaryModules = $request->get('secondary_modules');
		$secondaryModules = implode(':', $secondaryModules);
		$reportModel->setSecondaryModule($secondaryModules);

		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->set('reporttype', 'chart');

		$dataFields = $request->get('datafields', 'count(*)');
		if (is_string($dataFields)) {
			$dataFields = [$dataFields];
		}

		$reportModel->set('reporttypedata', \App\Json::encode([
				'type' => $request->get('charttype', 'pieChart'),
				'groupbyfield' => $request->get('groupbyfield'),
				'datafields' => $dataFields, ]
		));
		$reportModel->save();

		$loadUrl = $reportModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
}
