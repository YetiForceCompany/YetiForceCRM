<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~include/utils/RecurringType.php');

class Calendar_Record_Model extends Vtiger_Record_Model {

/**
	 * Function returns the Entity Name of Record Model
	 * @return <String>
	 */
	function getName() {
		$name = $this->get('subject');
		if(empty($name)) {
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
	public function setActivityReminder($reminderSent = 0, $recurId = '', $reminderMode = '') {
		$moduleInstance = CRMEntity::getInstance($this->getModuleName());
		$moduleInstance->activity_reminder($this->getId(), $this->get('reminder_time'), $reminderSent, $recurId, $reminderMode);
	}

	/**
	 * Function returns the Module Name based on the activity type
	 * @return <String>
	 */
	function getType() {
		$activityType = $this->get('activitytype');
		if($activityType == 'Task') {
			return 'Calendar';
		}
		return 'Events';
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module=Calendar&view='.$module->getDetailViewName().'&record='.$this->getId();
	}

	/**
	 * Function returns recurring information for EditView
	 * @return <Array> - which contains recurring Information
	 */
	public function getRecurrenceInformation() {
		$recurringObject = $this->getRecurringObject();

		if ($recurringObject) {
			$recurringData['recurringcheck'] = 'Yes';
			$recurringData['repeat_frequency'] = $recurringObject->getRecurringFrequency();
			$recurringData['eventrecurringtype'] = $recurringObject->getRecurringType();
			$recurringEndDate = $recurringObject->getRecurringEndDate(); 
			if(!empty($recurringEndDate)){ 
				$recurringData['recurringenddate'] = $recurringEndDate->get_formatted_date(); 
			} 
			$recurringInfo = $recurringObject->getUserRecurringInfo();

			if ($recurringObject->getRecurringType() == 'Weekly') {
				$noOfDays = count($recurringInfo['dayofweek_to_repeat']);
				for ($i = 0; $i < $noOfDays; ++$i) {
					$recurringData['week'.$recurringInfo['dayofweek_to_repeat'][$i]] = 'checked';
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

	function save() {
		//Time should changed to 24hrs format
		$_REQUEST['time_start'] = Vtiger_Time_UIType::getTimeValueWithSeconds($_REQUEST['time_start']);
		$_REQUEST['time_end'] = Vtiger_Time_UIType::getTimeValueWithSeconds($_REQUEST['time_end']);
		parent::save();
	}

	/**
	 * Function to get recurring information for the current record in detail view
	 * @return <Array> - which contains Recurring Information
	 */
	public function getRecurringDetails() {
		$recurringObject = $this->getRecurringObject();
		if ($recurringObject) {
			$recurringInfoDisplayData = $recurringObject->getDisplayRecurringInfo();
			$recurringEndDate = $recurringObject->getRecurringEndDate(); 
		} else {
			$recurringInfoDisplayData['recurringcheck'] = vtranslate('LBL_NO', $currentModule);
			$recurringInfoDisplayData['repeat_str'] = '';
		}
		if(!empty($recurringEndDate)){ 
			$recurringInfoDisplayData['recurringenddate'] = $recurringEndDate->get_formatted_date(); 
		}

		return $recurringInfoDisplayData;
	}

	/**
	 * Function to get the recurring object
	 * @return Object - recurring object
	 */
	public function getRecurringObject() {
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
	public function updateReminderStatus($status=1) {
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_activity_reminder_popup set status = ? WHERE recordid = ?", array($status, $this->getId()));
	}
	public function updateReminderPostpone($time) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_activity_reminder_popup WHERE recordid = ?', array($this->getId()));
		$row = $db->query_result_rowdata($result, 0);
		$datatime = $row['date_start'] . ' '. $row['time_start']; 
		switch ($time) {
			case '15m':
				$datatime = date("Y-m-d H:i:s", strtotime("+15 min", strtotime($datatime)) );
			break;
			case '30m':
				$datatime = date("Y-m-d H:i:s", strtotime("+30 min", strtotime($datatime)) );
			break;
			case '1h':
				$datatime = date("Y-m-d H:i:s", strtotime("+60 min", strtotime($datatime)) );
			break;
			case '2h':
				$datatime = date("Y-m-d H:i:s", strtotime("+120 min", strtotime($datatime)) );
			break;
			case '1d':
				$datatime = date("Y-m-d", strtotime("+1 weekday", strtotime($datatime)) ).' '.$row['time_start'];
			break;
		}
		$time_start = date("H:i:s", strtotime($datatime));
		$date_start = date('Y-m-d', strtotime($datatime));
		$db->pquery("UPDATE vtiger_activity_reminder_popup set status = ?, date_start = ?, time_start = ? WHERE recordid = ?", array(0, $date_start, $time_start, $this->getId()));
	}
}
