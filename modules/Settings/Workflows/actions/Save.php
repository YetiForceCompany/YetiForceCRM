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

class Settings_Workflows_Save_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$summary = $request->get('summary');
		$moduleName = $request->getByType('module_name', 2);
		$conditions = $request->getArray('conditions', 'Text');
		$filterSavedInNew = $request->isEmpty('filtersavedinnew') ? null : $request->getInteger('filtersavedinnew');
		$executionCondition = $request->getInteger('execution_condition');
		$workflowScheduleType = $request->isEmpty('schtypeid') ? null : $request->getInteger('schtypeid');
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
		$workflowModel->set('params', null);
		if (\VTWorkflowManager::$ON_SCHEDULE === $executionCondition) {
			$schtime = null;
			if (!$request->isEmpty('schtime')) {
				$schtime = $request->getByType('schtime', 'TimeInUserFormat', true);
			}
			$workflowModel->set('schtime', $schtime);
			$workflowModel->set('schtypeid', $workflowScheduleType);
			$dayOfMonth = null;
			$dayOfWeek = null;
			$annualDates = null;
			if ($workflowScheduleType == Workflow::$SCHEDULED_WEEKLY) {
				$dayOfWeek = \App\Json::encode($request->getArray('schdayofweek', 'Integer'));
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
				$dayOfMonth = \App\Json::encode($request->getArray('schdayofmonth', 'Integer'));
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
				$date = $request->getByType('schdate', 'dateTimeInUserFormat', true);
				$date = \App\Fields\DateTime::formatToDb($date);
				$currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
				if ($date > $currentTime) {
					$workflowModel->set('nexttrigger_time', $date);
				} else {
					$workflowModel->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
				}
				$annualDates = \App\Json::encode([$date]);
			} elseif ($workflowScheduleType == Workflow::$SCHEDULED_ANNUALLY) {
				$dates = $request->getExploded('schannualdates', ',', 'DateInUserFormat');
				$dates = array_map('App\Fields\Date::formatToDB', $dates);
				sort($dates);
				$annualDates = \App\Json::encode($dates);
			}
			$params = array_intersect_key($request->getMultiDimensionArray('params', [
				'iterationOff' => \App\Purifier::BOOL,
				'showTasks' => \App\Purifier::BOOL,
				'enableTasks' => \App\Purifier::BOOL,
			]), array_flip(['iterationOff']));
			$workflowModel->set('params', empty($params) ? null : \App\Json::encode($params));
			$workflowModel->set('schdayofmonth', $dayOfMonth);
			$workflowModel->set('schdayofweek', $dayOfWeek);
			$workflowModel->set('schannualdates', $annualDates);
		} elseif (\VTWorkflowManager::$TRIGGER === $executionCondition) {
			$params = array_intersect_key($request->getMultiDimensionArray('params', [
				'iterationOff' => \App\Purifier::BOOL,
				'showTasks' => \App\Purifier::BOOL,
				'enableTasks' => \App\Purifier::BOOL,
			]), array_flip(['showTasks', 'enableTasks']));
			$workflowModel->set('params', empty($params) ? null : \App\Json::encode($params));
		}
		// Added to save the condition only when its changed from vtiger6
		if (6 === $filterSavedInNew) {
			//Added to change advanced filter condition to workflow
			$workflowModel->transformAdvanceFilterToWorkFlowFilter();
		}
		$workflowModel->set('filtersavedinnew', $filterSavedInNew);
		$workflowModel->save();
		//Update only for scheduled workflows other than specific date
		if ($workflowScheduleType != Workflow::$SCHEDULED_ON_SPECIFIC_DATE && '6' == $executionCondition) {
			$workflowModel->updateNextTriggerTime();
		}
		$response->setResult(['id' => $workflowModel->get('workflow_id')]);
		$response->emit();
	}
}
