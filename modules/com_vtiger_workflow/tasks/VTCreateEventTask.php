<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('include/Webservices/Utils.php');
require_once("include/Webservices/VtigerCRMObject.php");
require_once("include/Webservices/VtigerCRMObjectMeta.php");
require_once("include/Webservices/DataTransform.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/Webservices/ModuleTypes.php';
require_once('include/Webservices/Create.php');
require_once 'include/Webservices/DescribeObject.php';
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';

require_once("modules/Users/Users.php");

class VTCreateEventTask extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array('eventType', 'eventName', 'description', 'sendNotification',
			'startTime', 'startDays', 'startDirection', 'startDatefield',
			'endTime', 'endDays', 'endDirection', 'endDatefield',
			'status', 'priority', 'recurringcheck', 'repeat_frequency',
			'recurringtype', 'calendar_repeat_limit_date',
			'mon_flag', 'tue_flag', 'wed_flag', 'thu_flag', 'fri_flag', 'sat_flag', 'sun_flag',
			'repeatMonth', 'repeatMonth_date', 'repeatMonth_daytype', 'repeatMonth_day', 'assigned_user_id');
	}

	function getAdmin()
	{
		$user = Users::getActiveAdminUser();
		$currentUser = vglobal('current_user');
		$this->originalUser = $currentUser;
		$currentUser = $user;
		return $user;
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (!\App\Module::isModuleActive('Calendar')) {
			return;
		}
		$currentUser = vglobal('current_user');
		$userId = $recordModel->get('assigned_user_id');
		$adminUser = $this->getAdmin();
		if ($userId === null) {
			$userId = $adminUser;
		}
		$moduleName = $recordModel->getModuleName();


		$startDate = $this->calculateDate($recordModel, $this->startDays, $this->startDirection, $this->startDatefield);
		$endDate = $this->calculateDate($recordModel, $this->endDays, $this->endDirection, $this->endDatefield);

		if ($this->assigned_user_id == 'currentUser') {
			$userId = \App\User::getCurrentUserId();
		} else if ($this->assigned_user_id == 'triggerUser') {
			$userId = \App\User::getCurrentUserRealId();
		}
		if ($this->assigned_user_id == 'copyParentOwner') {
			$userId = $recordModel->get('assigned_user_id');
		} else if (!empty($this->assigned_user_id)) { // Added to check if the user/group is active
			$userExists = (new App\Db\Query())->from('vtiger_users')
				->where(['id' => $this->assigned_user_id, 'status' => 'Active'])
				->exists();
			if ($userExists) {
				$userId = $this->assigned_user_id;
			} else {
				$groupExist = (new App\Db\Query())->from('vtiger_groups')
					->where(['groupid' => $this->assigned_user_id])
					->exists();
				if ($groupExist) {
					$userId = $this->assigned_user_id;
				}
			}
		}

		$fields = array(
			'activitytype' => $this->eventType,
			'description' => $this->description,
			'subject' => $this->eventName,
			'taskpriority' => $this->priority,
			'activitystatus' => $this->status,
			'assigned_user_id' => $userId,
			'time_start' => self::convertToDBFormat($this->startTime),
			'date_start' => $startDate,
			'time_end' => self::convertToDBFormat($this->endTime),
			'due_date' => $endDate,
			'duration_hours' => 0
		);

		//Setting visibility value
		$sharedType = Calendar_Module_Model::getSharedType($userId);
		if ($sharedType == 'selectedusers' || empty($sharedType)) {
			$sharedType = 'public';
		}
		$fields['visibility'] = ucfirst($sharedType);

		$id = $recordModel->getId();
		$field = Vtiger_ModulesHierarchy_Model::getMappingRelatedField($moduleName);
		if ($field) {
			$fields[$field] = $id;
		}
		$newRecordModel = Vtiger_Record_Model::getCleanInstance('Events');
		$newRecordModel->setData($fields);
		$newRecordModel->setHandlerExceptions(['disableWorkflow' => true]);
		$newRecordModel->save();
		relateEntities($recordModel->getEntity(), $moduleName, $recordModel->getId(), 'Calendar', $newRecordModel->getId());
		/*
		  $handler = vtws_getModuleHandlerFromName('Events', $adminUser);
		  $meta = $handler->getMeta();
		  $recordValues = DataTransform::sanitizeForInsert($newRecordModel->getData(), $meta);
		  list($typeId, $id) = vtws_getIdComponents($event['id']);
		  $event = CRMEntity::getInstance('Events');
		  $event->id = $id;
		  $event->column_fields = $recordValues;

		  if ($this->recurringcheck && !empty($startDate) &&
		  ($this->calendar_repeat_limit_date)) {

		  $resultRow = array();

		  $resultRow['date_start'] = $startDate;
		  $resultRow['time_start'] = self::conv12to24hour($this->startTime);
		  $resultRow['due_date'] = $this->calendar_repeat_limit_date;
		  $resultRow['time_end'] = self::conv12to24hour($this->endTime);
		  $resultRow['recurringtype'] = $this->recurringtype;
		  $resultRow['recurringfreq'] = $this->repeat_frequency;

		  if ($this->sun_flag) {
		  $daysOfWeekToRepeat[] = 0;
		  }
		  if ($this->mon_flag) {
		  $daysOfWeekToRepeat[] = 1;
		  }
		  if ($this->tue_flag) {
		  $daysOfWeekToRepeat[] = 2;
		  }
		  if ($this->wed_flag) {
		  $daysOfWeekToRepeat[] = 3;
		  }
		  if ($this->thu_flag) {
		  $daysOfWeekToRepeat[] = 4;
		  }
		  if ($this->fri_flag) {
		  $daysOfWeekToRepeat[] = 5;
		  }
		  if ($this->sat_flag) {
		  $daysOfWeekToRepeat[] = 6;
		  }

		  if ($this->recurringtype == 'Daily' || $this->recurringtype == 'Yearly') {
		  $recurringInfo = $this->recurringtype;
		  } elseif ($this->recurringtype == 'Weekly') {
		  if ($daysOfWeekToRepeat != null) {
		  $recurringInfo = $this->recurringtype . '::' . implode('::', $daysOfWeekToRepeat);
		  } else {
		  $recurringInfo = $recurringType;
		  }
		  } elseif ($this->recurringtype == 'Monthly') {
		  $recurringInfo = $this->recurringtype . '::' . $this->repeatMonth;
		  if ($this->repeatMonth == 'date') {
		  $recurringInfo = $recurringInfo . '::' . $this->repeatMonth_date;
		  } else {
		  $recurringInfo = $recurringInfo . '::' . $this->repeatMonth_daytype . '::' . $this->repeatMonth_day;
		  }
		  }
		  $resultRow['recurringinfo'] = $recurringInfo;

		  // Added this to relate these events to parent module.
		  AppRequest::set('createmode', 'link');
		  AppRequest::set('return_module', $moduleName);
		  AppRequest::set('return_id', $entityIdDetails[1]);

		  $recurObj = RecurringType::fromDBRequest($resultRow);

		  include_once 'modules/Calendar/RepeatEvents.php';
		  Calendar_RepeatEvents::repeat($event, $recurObj);

		  AppRequest::set('createmode', '');
		  }
		 */
		$currentUser = vglobal('current_user');
		$currentUser = $this->originalUser;
	}

	private function calculateDate($recordModel, $days, $direction, $datefield)
	{
		$baseDate = $recordModel->get($datefield);
		if ($baseDate == '') {
			$baseDate = date('Y-m-d');
		}
		if ($days == '') {
			$days = 0;
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		$date = strftime('%Y-%m-%d', $baseDate + $days * 24 * 60 * 60 *
			(strtolower($direction) == 'before' ? -1 : 1));
		return $date;
	}

	/**
	 * To convert time_start & time_end values to db format
	 * @param type $timeStr
	 * @return time
	 */
	static function convertToDBFormat($timeStr)
	{
		$date = new DateTime();
		$time = Vtiger_Time_UIType::getTimeValueWithSeconds($timeStr);
		$dbInsertDateTime = DateTimeField::convertToDBTimeZone($date->format('Y-m-d') . ' ' . $time);
		return $dbInsertDateTime->format('H:i:s');
	}

	static function conv12to24hour($timeStr)
	{
		$arr = array();
		preg_match('/(\d{1,2}):(\d{1,2})(am|pm)/', $timeStr, $arr);
		if ($arr[3] == 'am') {
			$hours = ((int) $arr[1]) % 12;
		} else {
			$hours = ((int) $arr[1]) % 12 + 12;
		}
		return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($arr[2], 2, '0', STR_PAD_LEFT);
	}

	public function getTimeFieldList()
	{
		return array('startTime', 'endTime');
	}
}
