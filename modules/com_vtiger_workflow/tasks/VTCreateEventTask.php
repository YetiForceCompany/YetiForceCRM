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
			'status', 'priority', 'assigned_user_id');
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

		if ($this->assigned_user_id === 'currentUser') {
			$userId = \App\User::getCurrentUserId();
		} else if ($this->assigned_user_id === 'triggerUser') {
			$userId = $recordModel->executeUser;
		} else if ($this->assigned_user_id === 'copyParentOwner') {
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
		$id = $recordModel->getId();
		$field = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		if ($field) {
			$fields[$field] = $id;
		}
		$newRecordModel = Vtiger_Record_Model::getCleanInstance('Events');
		$newRecordModel->setData($fields);
		$newRecordModel->setHandlerExceptions(['disableWorkflow' => true]);
		$newRecordModel->save();
		relateEntities($recordModel->getEntity(), $moduleName, $recordModel->getId(), 'Calendar', $newRecordModel->getId());
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
