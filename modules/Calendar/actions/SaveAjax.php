<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Calendar_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	public function process(\App\Request $request)
	{
		$user = \App\User::getCurrentUserModel();
		if ($request->getBoolean('allday')) {
			$request->set('time_start', $user->getDetail('start_hour'));
			$request->set('time_end', $user->getDetail('end_hour'));
		}
		$recordModel = $this->saveRecord($request);
		$fieldModelList = $recordModel->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => &$fieldModel) {
			$value = $recordModel->get($fieldName);
			if (!is_array($value)) {
				$fieldValue = \App\Purifier::encodeHtml($value);
			} else {
				foreach ($value as $key => $item) {
					$fieldValue[$key] = \App\Purifier::encodeHtml($item);
				}
			}
			$result[$fieldName] = [];
			if ($fieldName === 'date_start') {
				$timeStart = Vtiger_Time_UIType::getTimeValueWithSeconds($recordModel->get('time_start'));
				$dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeStart);

				$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
				$dateTimeComponents = explode(' ', $userDateTimeString);
				$dateComponent = $dateTimeComponents[0];
				//Conveting the date format in to Y-m-d . since full calendar expects in the same format
				$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
				$result[$fieldName]['value'] = $dataBaseDateFormatedString;
				$result[$fieldName]['display_value'] = $dateComponent;
			} elseif ($fieldName === 'due_date') {
				$timeEnd = Vtiger_Time_UIType::getTimeValueWithSeconds($recordModel->get('time_end'));
				$dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeEnd);

				$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
				$dateTimeComponents = explode(' ', $userDateTimeString);
				$dateComponent = $dateTimeComponents[0];
				//Conveting the date format in to Y-m-d . since full calendar expects in the same format
				$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
				$result[$fieldName]['value'] = $dataBaseDateFormatedString;
				$result[$fieldName]['display_value'] = $dateComponent;
			} elseif ($fieldName === 'time_end') {
				$dueDate = $recordModel->get('due_date');
				$dateTimeFieldInstance = new DateTimeField($dueDate . ' ' . $fieldValue);

				$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
				$dateTimeComponents = explode(' ', $userDateTimeString);

				if ($user->get('hour_format') === '12') {
					$dateTimeComponents[1] = Vtiger_Time_UIType::getTimeValueInAMorPM($dateTimeComponents[1]);
				}

				$result[$fieldName]['value'] = $fieldValue;
				$result[$fieldName]['display_value'] = $dateTimeComponents[1];
			} elseif ($fieldName === 'time_start') {
				$startDate = $recordModel->get('date_start');
				$dateTimeFieldInstance = new DateTimeField($startDate . ' ' . $fieldValue);

				$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
				$dateTimeComponents = explode(' ', $userDateTimeString);

				if ($user->get('hour_format') === '12') {
					$dateTimeComponents[1] = Vtiger_Time_UIType::getTimeValueInAMorPM($dateTimeComponents[1]);
				}

				$result[$fieldName]['value'] = $fieldValue;
				$result[$fieldName]['display_value'] = $dateTimeComponents[1];
			} elseif (is_array($recordModel->get($fieldName)) && $fieldModel->getFieldDataType() === 'sharedOwner') {
				$recordFieldValue = \App\Purifier::encodeHtml(implode(',', $recordModel->get($fieldName)));
				$result[$fieldName]['value'] = $result[$fieldName]['display_value'] = $fieldModel->getDisplayValue($recordFieldValue, $recordModel->getId(), $recordModel);
			} elseif ('time_start' !== $fieldName && 'time_end' !== $fieldName && 'duration_hours' !== $fieldName) {
				$result[$fieldName]['value'] = $fieldValue;
				$result[$fieldName]['display_value'] = App\Purifier::decodeHtml($fieldModel->getDisplayValue($fieldValue, $recordModel->getId(), $recordModel));
			} else {
				$result[$fieldName]['value'] = $result[$fieldName]['display_value'] = $fieldValue;
			}
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(\App\Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);

		$startDate = $request->get('date_start');
		if (!empty($startDate)) {
			//Start Date and Time values
			$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
			$startDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('date_start'));
			if ($startTime) {
				$startDateTime = App\Fields\DateTime::formatToDb($request->get('date_start') . ' ' . $startTime);
				list($startDate, $startTime) = explode(' ', $startDateTime);
			}
			$recordModel->set('date_start', $startDate);
			$recordModel->set('time_start', $startTime);
		}
		$endDate = $request->get('due_date');
		if (!empty($endDate)) {
			//End Date and Time values
			$endTime = $request->get('time_end');
			$endDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('due_date'));
			if ($endTime) {
				$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
				$endDateTime = App\Fields\DateTime::formatToDb($request->get('due_date') . ' ' . $endTime);
				list($endDate, $endTime) = explode(' ', $endDateTime);
			}
			$recordModel->set('time_end', $endTime);
			$recordModel->set('due_date', $endDate);
		}
		$record = $request->get('record');
		if (!$record) {
			$activityType = $request->get('activitytype');
			$visibility = $request->get('visibility');
			if (empty($activityType)) {
				$activityType = 'Task';
			}
			if (empty($visibility)) {
				$visibility = 'Private';
			}
			$recordModel->set('activitytype', $activityType);
			$recordModel->set('visibility', $visibility);
		}
		if ($request->has('markAsCompleted')) {
			$recordModel->set('activitystatus', $request->get('markAsCompleted'));
		}
		if ($endTime && $startTime) {
			$time = (strtotime($endTime)) - (strtotime($startTime));
			$diffinSec = (strtotime($endDate)) - (strtotime($startDate));
			$diff_days = floor($diffinSec / (60 * 60 * 24));

			$hours = ((float) $time / 3600) + ($diff_days * 24);
			$minutes = ((float) $hours - (int) $hours) * 60;

			$recordModel->set('duration_hours', (int) $hours);
			$recordModel->set('duration_minutes', round($minutes, 0));
		}

		return $recordModel;
	}
}
