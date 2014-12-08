<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Workflows_Save_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$summary = $request->get('summary');
		$moduleName = $request->get('module_name');
		$conditions = $request->get('conditions');
		$filterSavedInNew = $request->get('filtersavedinnew');
		$executionCondition = $request->get('execution_condition');

		if($recordId) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
		} else {
			$workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
		}

		$response = new Vtiger_Response();
		$workflowModel->set('summary', $summary);
		$workflowModel->set('module_name', $moduleName);
		$workflowModel->set('conditions', $conditions);
		$workflowModel->set('execution_condition', $executionCondition);
		
		if($executionCondition == '6') {
			$schtime = $request->get("schtime");
			if(!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime,0,2)>23) {  // invalid time format
				$schtime='00:00';
			}
			$schtime .=':00';

			$workflowModel->set('schtime', $schtime);

			$workflowScheduleType = $request->get('schtypeid');
			$workflowModel->set('schtypeid', $workflowScheduleType);

			$dayOfMonth = null; $dayOfWeek = null; $month = null; $annualDates =null;

			if($workflowScheduleType == Workflow::$SCHEDULED_WEEKLY) {
				$dayOfWeek = Zend_Json::encode($request->get('schdayofweek'));
			} else if($workflowScheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
				$dayOfMonth = Zend_Json::encode($request->get('schdayofmonth'));
			} else if($workflowScheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
				$date = $request->get('schdate');
				$dateDBFormat = DateTimeField::convertToDBFormat($date);
				$nextTriggerTime = $dateDBFormat.' '.$schtime;
				$currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
				if($nextTriggerTime > $currentTime) {
					$workflowModel->set('nexttrigger_time', $nextTriggerTime);
				} else {
					$workflowModel->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
				}
				$annualDates = Zend_Json::encode(array($dateDBFormat));
			} else if($workflowScheduleType == Workflow::$SCHEDULED_ANNUALLY) {
				$annualDates = Zend_Json::encode($request->get('schannualdates'));
			}
			$workflowModel->set('schdayofmonth', $dayOfMonth);
			$workflowModel->set('schdayofweek', $dayOfWeek);
			$workflowModel->set('schannualdates', $annualDates);
		}
		
		// Added to save the condition only when its changed from vtiger6
		if($filterSavedInNew == '6') {
			//Added to change advanced filter condition to workflow
			$workflowModel->transformAdvanceFilterToWorkFlowFilter();
		}
		$workflowModel->set('filtersavedinnew', $filterSavedInNew);
		$workflowModel->save();

		//Update only for scheduled workflows other than specific date
		if($workflowScheduleType != Workflow::$SCHEDULED_ON_SPECIFIC_DATE && $executionCondition == '6') {
			$workflowModel->updateNextTriggerTime();
		}
		
		$response->setResult(array('id' => $workflowModel->get('workflow_id')));
		$response->emit();
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        }
} 