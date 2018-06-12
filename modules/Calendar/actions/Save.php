<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Calendar_Save_Action extends Vtiger_Save_Action
{
	public function process(\App\Request $request)
	{
		$recordModel = $this->saveRecord($request);
		$loadUrl = $recordModel->getDetailViewUrl();

		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentRecordId = $request->getInteger('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} elseif ($request->getBoolean('returnToList')) {
			$moduleModel = $recordModel->getModule();
			$loadUrl = $moduleModel->getListViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request - values of the record
	 *
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->getInteger('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if ($relatedModule->getName() === 'Events') {
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}

		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);
		$user = \App\User::getCurrentUserModel();
		if ($request->getBoolean('allday')) {
			$request->set('time_start', $user->getDetail('start_hour'));
			$request->set('time_end', $user->getDetail('end_hour'));
		}
		//Start Date and Time values
		$startDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('date_start'));
		$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
		if ($startTime) {
			$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($startTime);
			$startDateTime = App\Fields\DateTime::formatToDb($startDate . ' ' . $startTime);
			list($startDate, $startTime) = explode(' ', $startDateTime);
		}
		$recordModel->set('date_start', $startDate);
		$recordModel->set('time_start', $startTime);
		//End Date and Time values
		$endDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('due_date'));
		$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_end'));
		if ($endTime) {
			$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
			$endDateTime = App\Fields\DateTime::formatToDb($endDate . ' ' . $endTime);
			list($endDate, $endTime) = explode(' ', $endDateTime);
		}
		$recordModel->set('due_date', $endDate);
		$recordModel->set('time_end', $endTime);
		$activityType = $request->getByType('activitytype');
		if (empty($activityType)) {
			$recordModel->set('activitytype', 'Task');
			$recordModel->set('visibility', 'Private');
		}
		if ($request->has('markAsCompleted')) {
			$recordModel->set('activitystatus', $request->get('markAsCompleted'));
		}
		//Due to dependencies on the older code
		if ($request->get('set_reminder') && $request->get('set_reminder') !== 'No') {
			unset($_SESSION['next_reminder_time']);
			$remDays = (int) $request->get('remdays');
			$remHrs = (int) $request->get('remhrs');
			$remMin = (int) $request->get('remmin');
			$reminderTime = $remDays * 24 * 60 + $remHrs * 60 + $remMin;
			$recordModel->set('set_reminder', $reminderTime);
		} else {
			$recordModel->set('set_reminder', false);
		}
		$time = (strtotime($request->get('time_end'))) - (strtotime($request->get('time_start')));
		$diffinSec = (strtotime($request->get('due_date'))) - (strtotime($request->get('date_start')));
		$diff_days = floor($diffinSec / (60 * 60 * 24));
		$hours = ((float) $time / 3600) + ($diff_days * 24);
		$minutes = ((float) $hours - (int) $hours) * 60;
		$recordModel->set('duration_hours', (int) $hours);
		$recordModel->set('duration_minutes', round($minutes, 0));

		return $recordModel;
	}
}
