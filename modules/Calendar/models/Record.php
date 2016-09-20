<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~include/utils/RecurringType.php');

class Calendar_Record_Model extends Vtiger_Record_Model
{

	public static $referenceFields = ['link', 'process', 'subprocess'];

	public static function getNameByReference($refModuleName)
	{
		$fieldName = Vtiger_Cache::get('NameRelatedField', $refModuleName . '-Calendar');
		if (!empty($fieldName)) {
			return $fieldName;
		}
		$parentModuleModel = Vtiger_Module_Model::getInstance($refModuleName);
		$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
		if ($relationModel && $relationModel->getRelationField()) {
			$fieldName = $relationModel->getRelationField()->getFieldName();
			Vtiger_Cache::set('NameRelatedField', $refModuleName . '-Calendar', $fieldName);
		}
		return $fieldName;
	}

	public static function setCrmActivity($referenceIds, $refModuleName = false)
	{
		$db = PearDatabase::getInstance();
		foreach ($referenceIds as $ID => $fieldName) {
			if (empty($fieldName)) {
				$fieldName = self::getNameByReference($refModuleName);
			}
			$result = $db->pquery("SELECT vtiger_activity.status,date_start FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid WHERE vtiger_crmentity.deleted = ? && vtiger_activity.$fieldName = ? && vtiger_activity.status IN ('" . implode("','", Calendar_Module_Model::getComponentActivityStateLabel('current')) . "') ORDER BY date_start ASC LIMIT 1;", [0, $ID]);
			if ($row = $db->getRow($result)) {
				$date = new DateTime(date('Y-m-d'));
				$diff = $date->diff(new DateTime($row['date_start']));
				$db->update('vtiger_entity_stats', ['crmactivity' => (int) $diff->format("%r%a")], '`crmid` = ?', [$ID]);
			} else {
				$db->update('vtiger_entity_stats', ['crmactivity' => null], '`crmid` = ?', [$ID]);
			}
		}
	}

	/**
	 * Function returns the Entity Name of Record Model
	 * @return <String>
	 */
	public function getName()
	{
		$name = $this->get('subject');
		if (empty($name)) {
			$name = parent::getName();
		}
		return $name;
	}

	/**
	 * Function to insert details about reminder in to Database
	 * @param <Date> $reminderSent
	 * @param <integer> $recurId
	 * @param <String> $reminderMode like edit/delete
	 */
	public function setActivityReminder($reminderSent = 0, $recurId = '', $reminderMode = '')
	{
		$moduleInstance = CRMEntity::getInstance($this->getModuleName());
		$moduleInstance->activity_reminder($this->getId(), $this->get('reminder_time'), $reminderSent, $recurId, $reminderMode);
	}

	/**
	 * Function returns the Module Name based on the activity type
	 * @return <String>
	 */
	public function getType()
	{
		$activityType = $this->get('activitytype');
		if ($activityType == 'Task') {
			return 'Calendar';
		}
		return 'Events';
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Calendar&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function returns recurring information for EditView
	 * @return <Array> - which contains recurring Information
	 */
	public function getRecurrenceInformation()
	{
		$recurringObject = $this->getRecurringObject();

		if ($recurringObject) {
			$recurringData['recurringcheck'] = 'Yes';
			$recurringData['repeat_frequency'] = $recurringObject->getRecurringFrequency();
			$recurringData['eventrecurringtype'] = $recurringObject->getRecurringType();
			$recurringEndDate = $recurringObject->getRecurringEndDate();
			if (!empty($recurringEndDate)) {
				$recurringData['recurringenddate'] = $recurringEndDate->get_formatted_date();
			}
			$recurringInfo = $recurringObject->getUserRecurringInfo();

			if ($recurringObject->getRecurringType() == 'Weekly') {
				$noOfDays = count($recurringInfo['dayofweek_to_repeat']);
				for ($i = 0; $i < $noOfDays; ++$i) {
					$recurringData['week' . $recurringInfo['dayofweek_to_repeat'][$i]] = 'checked';
				}
			} elseif ($recurringObject->getRecurringType() == 'Monthly') {
				$recurringData['repeatMonth'] = $recurringInfo['repeatmonth_type'];
				if ($recurringInfo['repeatmonth_type'] == 'date') {
					$recurringData['repeatMonth_date'] = $recurringInfo['repeatmonth_date'];
				} else {
					$recurringData['repeatMonth_daytype'] = $recurringInfo['repeatmonth_daytype'];
					$recurringData['repeatMonth_day'] = $recurringInfo['dayofweek_to_repeat'][0];
				}
			}
		} else {
			$recurringData['recurringcheck'] = 'No';
		}
		return $recurringData;
	}

	public function save()
	{
		//Time should changed to 24hrs format
		AppRequest::set('time_start', Vtiger_Time_UIType::getTimeValueWithSeconds(AppRequest::get('time_start')));
		AppRequest::set('time_end', Vtiger_Time_UIType::getTimeValueWithSeconds(AppRequest::get('time_end')));
		parent::save();
	}

	/**
	 * Function to get recurring information for the current record in detail view
	 * @return <Array> - which contains Recurring Information
	 */
	public function getRecurringDetails()
	{
		$recurringObject = $this->getRecurringObject();
		if ($recurringObject) {
			$recurringInfoDisplayData = $recurringObject->getDisplayRecurringInfo();
			$recurringEndDate = $recurringObject->getRecurringEndDate();
		} else {
			$recurringInfoDisplayData['recurringcheck'] = vtranslate('LBL_NO', $currentModule);
			$recurringInfoDisplayData['repeat_str'] = '';
		}
		if (!empty($recurringEndDate)) {
			$recurringInfoDisplayData['recurringenddate'] = $recurringEndDate->get_formatted_date();
		}

		return $recurringInfoDisplayData;
	}

	/**
	 * Function to get the recurring object
	 * @return Object - recurring object
	 */
	public function getRecurringObject()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT vtiger_recurringevents.*, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.due_date, vtiger_activity.time_end FROM vtiger_recurringevents
					INNER JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_recurringevents.activityid
					WHERE vtiger_recurringevents.activityid = ?';
		$result = $db->pquery($query, array($this->getId()));
		if ($db->num_rows($result)) {
			return RecurringType::fromDBRequest($db->query_result_rowdata($result, 0));
		}
		return false;
	}

	/**
	 * Function updates the Calendar Reminder popup's status
	 */
	public function updateReminderStatus($status = 1)
	{
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_activity_reminder_popup set status = ? WHERE recordid = ?", array($status, $this->getId()));
	}

	public function updateReminderPostpone($time)
	{
		$db = PearDatabase::getInstance();
		switch ($time) {
			case '15m':
				$datatime = date('Y-m-d H:i:s', strtotime('+15 min'));
				break;
			case '30m':
				$datatime = date('Y-m-d H:i:s', strtotime('+30 min'));
				break;
			case '1h':
				$datatime = date('Y-m-d H:i:s', strtotime('+60 min'));
				break;
			case '2h':
				$datatime = date('Y-m-d H:i:s', strtotime('+120 min'));
				break;
			case '6h':
				$datatime = date('Y-m-d H:i:s', strtotime('+6 hour'));
				break;
			case '1d':
				$datatime = date('Y-m-d H:i:s', strtotime('+1 day'));
				break;
		}
		$datatimeSTR = strtotime($datatime);
		$time_start = date('H:i:s', $datatimeSTR);
		$date_start = date('Y-m-d', $datatimeSTR);
		$db->pquery('UPDATE vtiger_activity_reminder_popup set status = ?, date_start = ?, time_start = ? WHERE recordid = ?', array(0, $date_start, $time_start, $this->getId()));

		$result = $db->pquery('SELECT value FROM vtiger_calendar_config WHERE type = ? && name = ? && value = ?', array('reminder', 'update_event', 1));
		if ($db->num_rows($result) > 0) {
			$query = 'SELECT date_start, time_start, due_date, time_end FROM vtiger_activity WHERE activityid = ?';
			$result = $db->pquery($query, array($this->getId()));
			$date_start_record = $db->query_result($result, 0, 'date_start');
			$time_start_record = $db->query_result($result, 0, 'time_start');
			$due_date_record = $db->query_result($result, 0, 'due_date');
			$time_end_record = $db->query_result($result, 0, 'time_end');
			$duration = strtotime($due_date_record . ' ' . $time_end_record) - strtotime($date_start_record . ' ' . $time_start_record);

			$time_end_record = date('H:i:s', $datatimeSTR + $duration);
			$due_date_record = date('Y-m-d', $datatimeSTR + $duration);
			$params = array($date_start, $time_start, $due_date_record, $time_end_record, $this->getId());
			$db->pquery('UPDATE vtiger_activity set date_start = ?, time_start = ?, due_date = ?, time_end = ? WHERE activityid = ?', $params);
		}
	}

	public function getActivityTypeIcon()
	{
		$icon = $this->get('activitytype');
		if ($icon == 'Task')
			$icon = 'Tasks';
		return $icon . '.png';
	}

	/**
	 * Function to get modal view url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getActivityStateModalUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Calendar&view=ActivityStateModal&record=' . $this->getId();
	}
}
