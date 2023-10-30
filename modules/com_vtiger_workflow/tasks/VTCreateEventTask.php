<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/WebServiceError.php';
require_once 'modules/Users/Users.php';

class VTCreateEventTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['eventType', 'eventName', 'description', 'sendNotification',
			'startTime', 'startDays', 'startDirection', 'startDatefield',
			'endTime', 'endDays', 'endDirection', 'endDatefield',
			'status', 'priority', 'assigned_user_id', ];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (!\App\Module::isModuleActive('Calendar')) {
			return;
		}
		$userId = $recordModel->get('assigned_user_id');
		if (null === $userId) {
			$userId = Users::getActiveAdminUser();
		}
		$moduleName = $recordModel->getModuleName();
		$startDate = $this->calculateDate($recordModel, $this->startDays, $this->startDirection, $this->startDatefield);
		$endDate = $this->calculateDate($recordModel, $this->endDays, $this->endDirection, $this->endDatefield);

		if ('currentUser' === $this->assigned_user_id) {
			$userId = \App\User::getCurrentUserId();
		} elseif ('triggerUser' === $this->assigned_user_id) {
			$userId = $recordModel->executeUser;
		} elseif ('copyParentOwner' === $this->assigned_user_id) {
			$userId = $recordModel->get('assigned_user_id');
		} elseif (!empty($this->assigned_user_id)) { // Added to check if the user/group is active
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
		$textParser = \App\TextParser::getInstanceByModel($recordModel);
		$fields = [
			'activitytype' => $this->eventType,
			'description' => $textParser->setContent($this->description)->parse()->getContent(),
			'subject' => $textParser->setContent($this->eventName)->parse()->getContent(),
			'taskpriority' => $this->priority,
			'activitystatus' => $this->status,
			'assigned_user_id' => $userId,
			'time_start' => self::convertToDBFormat($this->startTime),
			'date_start' => $startDate,
			'time_end' => self::convertToDBFormat($this->endTime),
			'due_date' => $endDate,
			'duration_hours' => 0,
		];
		$id = $recordModel->getId();
		$field = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		if ($field) {
			$fields[$field] = $id;
		}
		if ($parentRecord = \App\Record::getParentRecord($id)) {
			$parentModuleName = \App\Record::getType($parentRecord);
			$field = \App\ModuleHierarchy::getMappingRelatedField($parentModuleName);
			if ($field) {
				$fields[$field] = $parentRecord;
			}
			if ($parentRecord = \App\Record::getParentRecord($parentRecord)) {
				$parentModuleName = \App\Record::getType($parentRecord);
				$field = \App\ModuleHierarchy::getMappingRelatedField($parentModuleName);
				if ($field) {
					$fields[$field] = $parentRecord;
				}
			}
		}
		$newRecordModel = Vtiger_Record_Model::getCleanInstance('Calendar');
		$newRecordModel->setData($fields);
		$newRecordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
		$newRecordModel->save();
		$relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), $newRecordModel->getModule());
		if ($relationModel) {
			$relationModel->addRelation($recordModel->getId(), $newRecordModel->getId());
		}
	}

	private function calculateDate($recordModel, $days, $direction, $datefield)
	{
		$baseDate = $recordModel->get($datefield);
		if ('' == $baseDate) {
			$baseDate = date('Y-m-d');
		}
		if ('' == $days) {
			$days = 0;
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		return strftime('%Y-%m-%d', $baseDate + $days * 24 * 60 * 60 *
			('before' == strtolower($direction) ? -1 : 1));
	}

	/**
	 * To convert time_start & time_end values to db format.
	 *
	 * @param string $timeStr
	 *
	 * @return time
	 */
	public static function convertToDBFormat($timeStr)
	{
		$date = new DateTime();
		$time = \App\Fields\Time::sanitizeDbFormat($timeStr);
		$dbInsertDateTime = DateTimeField::convertToDBTimeZone($date->format('Y-m-d') . ' ' . $time);

		return $dbInsertDateTime->format('H:i:s');
	}

	public static function conv12to24hour($timeStr)
	{
		$arr = [];
		preg_match('/(\d{1,2}):(\d{1,2})(am|pm)/', $timeStr, $arr);
		if ('am' == $arr[3]) {
			$hours = ((int) $arr[1]) % 12;
		} else {
			$hours = ((int) $arr[1]) % 12 + 12;
		}
		return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($arr[2], 2, '0', STR_PAD_LEFT);
	}

	public function getTimeFieldList()
	{
		return ['startTime', 'endTime'];
	}
}
