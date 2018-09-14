<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

class Calendar_Save_Action extends Vtiger_Save_Action
{
	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request Values of the record
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return \Vtiger_Record_Model Record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
		$recordModel = parent::saveRecord($request);
		if ($request->getBoolean('reapeat')) {
			$recurringEvents = Calendar_RecuringEvents_Model::getInstanceFromRequest($request);
			if ($request->isEmpty('record')) {
				App\Db::getInstance()->createCommand()->update('vtiger_activity', ['followup' => $recordModel->getId()], ['activityid' => $recordModel->getId()])->execute();
				$data['followup'] = $recordModel->getId();
			} elseif (empty($data['followup'])) {
				$data['followup'] = $recordModel->getId();
			}
			$recurringEvents->setChanges($recordModel->getPreviousValue());
			$recurringEvents->setData($data);
			$recurringEvents->save();
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

		if (!$request->isEmpty('typeSaving') && $request->getInteger('typeSaving') === Calendar_RecuringEvents_Model::UPDATE_THIS_EVENT) {
			$recordModel->set('recurrence', $recordModel->getPreviousValue('recurrence'));
		}
		return $recordModel;
	}
}
