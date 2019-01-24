<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Workflows_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$summary = $request->get('summary');
		$moduleName = $request->getByType('module_name', 2);
		$conditions = $request->getArray('conditions', 'Text');
		$filterSavedInNew = $request->get('filtersavedinnew');
		$executionCondition = $request->get('execution_condition');
		$workflowScheduleType = $request->get('schtypeid');
		if ($request->isEmpty('record')) {
			$workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
		} else {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($request->getInteger('record'));
		}
		$response = new Vtiger_Response();
		$workflowModel->set('summary', $summary);
		$workflowModel->set('module_name', $moduleName);
		$workflowModel->set('conditions', $conditions);
		$workflowModel->set('execution_condition', $executionCondition);
		if ($executionCondition == '6') {
			$schtime = null;
			if (!$request->isEmpty('schtime')) {
				$schtime = $request->getByType('schtime', 'TimeInUserFormat');
			}
			$workflowModel->set('schtime', $schtime);
			$workflowModel->set('schtypeid', $workflowScheduleType);
			$dayOfMonth = null;
			$dayOfWeek = null;
			$annualDates = null;
			if ($workflowScheduleType == Workflow::$SCHEDULED_WEEKLY) {
				$dayOfWeek = \App\Json::encode($request->get('schdayofweek'));
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
				$dayOfMonth = \App\Json::encode($request->get('schdayofmonth'));
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
				$date = $request->get('schdate');
				$dateDBFormat = DateTimeField::convertToDBFormat($date);
				$nextTriggerTime = $dateDBFormat . ' ' . $schtime;
				$currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
				if ($nextTriggerTime > $currentTime) {
					$workflowModel->set('nexttrigger_time', $nextTriggerTime);
				} else {
					$workflowModel->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
				}
				$annualDates = \App\Json::encode([$dateDBFormat]);
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_ANNUALLY) {
				$annualDates = \App\Json::encode($request->get('schannualdates'));
			}
			$workflowModel->set('schdayofmonth', $dayOfMonth);
			$workflowModel->set('schdayofweek', $dayOfWeek);
			$workflowModel->set('schannualdates', $annualDates);
		}
		// Added to save the condition only when its changed from vtiger6
		if ($filterSavedInNew == '6') {
			//Added to change advanced filter condition to workflow
			$workflowModel->transformAdvanceFilterToWorkFlowFilter();
		}
		$workflowModel->set('filtersavedinnew', $filterSavedInNew);
		$workflowModel->save();
		//Update only for scheduled workflows other than specific date
		if ($workflowScheduleType != Workflow::$SCHEDULED_ON_SPECIFIC_DATE && $executionCondition == '6') {
			$workflowModel->updateNextTriggerTime();
		}
		$response->setResult(['id' => $workflowModel->get('workflow_id')]);
		$response->emit();
	}
}
