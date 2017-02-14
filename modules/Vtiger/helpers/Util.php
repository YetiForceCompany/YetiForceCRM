<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Util_Helper
{

	/**
	 * Function used to transform mulitiple uploaded file information into useful format.
	 * @param array $_files - ex: array( 'file' => array('name'=> array(0=>'name1',1=>'name2'),
	 * 												array('type'=>array(0=>'type1',2=>'type2'),
	 * 												...);
	 * @param type $top
	 * @return array   array( 'file' => array(0=> array('name'=> 'name1','type' => 'type1'),
	 * 									array(1=> array('name'=> 'name2','type' => 'type2'),
	 * 												...);
	 */
	public static function transformUploadedFiles(array $_files, $top = true)
	{
		$files = [];
		foreach ($_files as $name => $file) {
			if ($top)
				$subName = $file['name'];
			else
				$subName = $name;

			if (is_array($subName)) {
				foreach (array_keys($subName) as $key) {
					$files[$name][$key] = array(
						'name' => $file['name'][$key],
						'type' => $file['type'][$key],
						'tmp_name' => $file['tmp_name'][$key],
						'error' => $file['error'][$key],
						'size' => $file['size'][$key],
					);
					$files[$name] = self::transformUploadedFiles($files[$name], false);
				}
			} else {
				$files[$name] = $file;
			}
		}
		return $files;
	}

	/**
	 * Function parses date into readable format
	 * @param <Date Time> $dateTime
	 * @return string
	 */
	public static function formatDateDiffInStrings($dateTime)
	{
		// http://www.php.net/manual/en/datetime.diff.php#101029
		$currentDateTime = date('Y-m-d H:i:s');

		$seconds = strtotime($currentDateTime) - strtotime($dateTime);

		if ($seconds == 0)
			return vtranslate('LBL_JUSTNOW');
		if ($seconds > 0) {
			$prefix = '';
			$suffix = ' ' . vtranslate('LBL_AGO');
		} else if ($seconds < 0) {
			$prefix = vtranslate('LBL_DUE') . ' ';
			$suffix = '';
			$seconds = -($seconds);
		}

		$minutes = floor($seconds / 60);
		$hours = floor($minutes / 60);
		$days = floor($hours / 24);
		$months = floor($days / 30);

		if ($seconds < 60)
			return $prefix . self::pluralize($seconds, 'LBL_SECOND') . $suffix;
		if ($minutes < 60)
			return $prefix . self::pluralize($minutes, 'LBL_MINUTE') . $suffix;
		if ($hours < 24)
			return $prefix . self::pluralize($hours, 'LBL_HOUR') . $suffix;
		if ($days < 30)
			return $prefix . self::pluralize($days, 'LBL_DAY') . $suffix;
		if ($months < 12)
			return $prefix . self::pluralize($months, 'LBL_MONTH') . $suffix;
		if ($months > 11) {
			$month = $months % 12;
			$monthAgo = '';
			if ($month != 0) {
				$monthAgo = self::pluralize($month, 'LBL_MONTH');
			}
			$result = self::pluralize(floor($months / 12), 'LBL_YEAR') . ' ' . $monthAgo;
			return $prefix . $result . $suffix;
		}
	}

	/**
	 * Function returns singular or plural text
	 * @param <Number> $count
	 * @param string $text
	 * @return string
	 */
	public static function pluralize($count, $text)
	{
		return $count . " " . (($count == 1) ? vtranslate("$text") : vtranslate("${text}S"));
	}

	/**
	 * Function to make the input safe to be used as HTML
	 */
	public static function toSafeHTML($input)
	{
		global $default_charset;
		return htmlspecialchars($input, ENT_QUOTES, $default_charset);
	}

	/**
	 * Function that will strip all the tags while displaying
	 * @param string $input - html data
	 * @return string vtiger6 displayable data
	 */
	public static function toVtiger6SafeHTML($input)
	{
		$allowableTags = '<a><br>';
		return strip_tags($input, $allowableTags);
	}

	/**
	 * Function to parses date into string format
	 * @param <Date> $date
	 * @param <Time> $time
	 * @return string
	 */
	public static function formatDateIntoStrings($date, $time = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue($date . ' ' . $time);

		list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateTimeInUserFormat);
		list($hours, $minutes, $seconds) = explode(':', $timeInUserFormat);

		$displayTime = $hours . ':' . $minutes;
		if ($currentUser->get('hour_format') === '12') {
			$displayTime = Vtiger_Time_UIType::getTimeValueInAMorPM($displayTime);
		}

		$today = Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$tomorrow = Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d H:i:s', strtotime('tomorrow')));
		$userDate = DateTimeField::__convertToUserFormat($date, $currentUser->get('date_format'));

		if ($dateInUserFormat == $today) {
			$todayInfo = vtranslate('LBL_TODAY');
			if ($time) {
				$todayInfo .= ' ' . vtranslate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($todayInfo)";
		} elseif ($dateInUserFormat == $tomorrow) {
			$tomorrowInfo = vtranslate('LBL_TOMORROW');
			if ($time) {
				$tomorrowInfo .= ' ' . vtranslate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($tomorrowInfo)";
		} else {
			if ($currentUser->get('date_format') === 'mm-dd-yyyy') {
				$dateInUserFormat = str_replace('-', '/', $dateInUserFormat);
			}
			$date = strtotime($dateInUserFormat);
			$dayInfo = vtranslate('LBL_' . date('D', $date));
			if ($time) {
				$dayInfo .= ' ' . vtranslate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($dayInfo)";
		}
		return $formatedDate;
	}

	/**
	 * Function to replace spaces with under scores
	 * @param string $string
	 * @return string
	 */
	public static function replaceSpaceWithUnderScores($string)
	{
		return str_replace(' ', '_', $string);
	}

	/**
	 * Function to parse dateTime into Days
	 * @param <DateTime> $dateTime
	 * @return string
	 */
	public static function formatDateTimeIntoDayString($dateTime, $allday = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = explode(' ', Vtiger_Datetime_UIType::getDisplayDateTimeValue($dateTime));

		if (count($dateTimeInUserFormat) == 3) {
			list($dateInUserFormat, $timeInUserFormat, $meridiem) = $dateTimeInUserFormat;
		} else {
			list($dateInUserFormat, $timeInUserFormat) = $dateTimeInUserFormat;
			$meridiem = '';
		}
		$timeInUserFormat = explode(':', $timeInUserFormat);
		if (count($timeInUserFormat) == 3) {
			list($hours, $minutes, $seconds) = $timeInUserFormat;
		} else {
			list($hours, $minutes) = $timeInUserFormat;
			$seconds = '';
		}

		$dateDay = vtranslate(DateTimeField::getDayFromDate($dateTime), 'Calendar');
		$formatedDate = $dateInUserFormat;
		if (!$allday) {
			$displayTime = $hours . ':' . $minutes . ' ' . $meridiem;
			$formatedDate .= ' ' . vtranslate('LBL_AT') . ' ' . $displayTime;
		}
		$formatedDate .= " ($dateDay)";
		return $formatedDate;
	}

	/**
	 * Function gets the CRM's base Currency information
	 * @return Array
	 */
	public static function getBaseCurrency()
	{
		return(new \App\Db\Query())->from('vtiger_currency_info')->where(['<', 'defaultid', '0'])->one();
	}

	/**
	 * Function to get maximum upload size
	 * @return float maximum upload size
	 */
	public static function getMaxUploadSize()
	{
		$upload_maxsize = vglobal('upload_maxsize');
		return ceil($upload_maxsize / (1024 * 1024));
	}

	/**
	 * Function to get Owner name for ownerId
	 * @param integer $ownerId
	 * @return string $ownerName
	 */
	public static function getOwnerName($ownerId)
	{
		$cache = Vtiger_Cache::getInstance();
		if ($cache->hasOwnerDbName($ownerId)) {
			return $cache->getOwnerDbName($ownerId);
		}

		$ownerModel = Users_Record_Model::getInstanceById($ownerId, 'Users');
		$userName = $ownerModel->get('user_name');
		$ownerName = '';
		if ($userName) {
			$ownerName = $userName;
		} else {
			$ownerModel = Settings_Groups_Record_Model::getInstance($ownerId);
			if (!empty($ownerModel)) {
				$ownerName = $ownerModel->getName();
			}
		}
		if (!empty($ownerName)) {
			$cache->setOwnerDbName($ownerId, $ownerName);
		}
		return $ownerName;
	}

	/**
	 * Function decodes the utf-8 characters
	 * @param string $string
	 * @return string
	 */
	public static function getDecodedValue($string)
	{
		return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	public static function getActiveAdminCurrentDateTime()
	{
		$default_timezone = vglobal('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);
		$date = date('Y-m-d H:i:s');
		@date_default_timezone_set($default_timezone);
		return $date;
	}

	/**
	 * Function to get the datetime value in user preferred hour format
	 * @param <DateTime> $dateTime
	 * @param <Vtiger_Users_Model> $userObject
	 * @return string date and time with hour format
	 */
	public static function convertDateTimeIntoUsersDisplayFormat($dateTime, $userObject = null)
	{
		require_once 'include/runtime/LanguageHandler.php';
		require_once 'include/runtime/Globals.php';
		if ($userObject) {
			$userModel = Users_Privileges_Model::getInstanceFromUserObject($userObject);
		} else {
			$userModel = Users_Privileges_Model::getCurrentUserModel();
		}

		$date = new DateTime($dateTime);
		$dateTimeField = new DateTimeField($date->format('Y-m-d H:i:s'));

		$date = $dateTimeField->getDisplayDate($userModel);
		$time = $dateTimeField->getDisplayTime($userModel);
		return $date . ' ' . $time;
	}

	/**
	 * Function to get the time value in user preferred hour format
	 * @param <Time> $time
	 * @param <Vtiger_Users_Model> $userObject
	 * @return string time with hour format
	 */
	public static function convertTimeIntoUsersDisplayFormat($time, $userObject = null)
	{
		require_once 'include/runtime/LanguageHandler.php';
		require_once 'include/runtime/Globals.php';
		if ($userObject) {
			$userModel = Users_Privileges_Model::getInstanceFromUserObject($userObject);
		} else {
			$userModel = Users_Privileges_Model::getCurrentUserModel();
		}

		if ($userModel->get('hour_format') == '12') {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}

		return $time;
	}

	/**
	 * Function gets the CRM's base Currency information according to user preference
	 * @return Array
	 */
	public static function getCurrentInfoOfUser()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$result = $db->pquery('SELECT * FROM vtiger_currency_info WHERE id = ?', array($currentUser->get('currency_id')));
		if ($db->num_rows($result))
			return $db->query_result_rowdata($result, 0);
	}

	public static function getGroupsIdsForUsers($userId)
	{
		vimport('~include/utils/GetUserGroups.php');

		$userGroupInstance = new GetUserGroups();
		$userGroupInstance->getAllUserGroups($userId);
		return $userGroupInstance->user_groups;
	}

	public static function transferListSearchParamsToFilterCondition($searchParams, $moduleModel)
	{
		if (empty($searchParams)) {
			return [];
		}
		$advFilterConditionFormat = [];
		$glueOrder = ['and', 'or'];
		$groupIterator = 0;
		foreach ($searchParams as &$groupInfo) {
			if (empty($groupInfo)) {
				continue;
			}
			$groupColumnsInfo = $groupConditionInfo = [];
			foreach ($groupInfo as &$fieldSearchInfo) {
				list ($fieldName, $operator, $fieldValue, $specialOption) = $fieldSearchInfo;
				$fieldInfo = $moduleModel->getField($fieldName);
				if ($field->getFieldDataType() === 'tree' && $specialOption) {
					$fieldValue = Settings_TreesManager_Record_Model::getChildren($fieldValue, $fieldName, $moduleModel);
				}
				//Request will be having in terms of AM and PM but the database will be having in 24 hr format so converting
				if ($field->getFieldDataType() === 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($field->getFieldDataType() === 'currency') {
					$fieldValue = CurrencyField::convertToDBFormat($fieldValue);
				}
				if ($fieldName === 'date_start' || $fieldName === 'due_date' || $field->getFieldDataType() === 'datetime') {
					$dateValues = explode(',', $fieldValue);
					//Indicate whether it is fist date in the between condition
					$isFirstDate = true;
					foreach ($dateValues as $key => $dateValue) {
						$dateTimeCompoenents = explode(' ', $dateValue);
						if (empty($dateTimeCompoenents[1])) {
							if ($isFirstDate) {
								$dateTimeCompoenents[1] = '00:00:00';
							} else {
								$dateTimeCompoenents[1] = '23:59:59';
							}
						}
						$dateValue = implode(' ', $dateTimeCompoenents);
						$dateValues[$key] = $dateValue;
						$isFirstDate = false;
					}
					$fieldValue = implode(',', $dateValues);
				}
				$groupColumnsInfo[] = ['columnname' => $field->getCustomViewColumnName(), 'comparator' => $operator, 'value' => $fieldValue];
			}
			$advFilterConditionFormat[$glueOrder[$groupIterator]] = $groupColumnsInfo;
			$groupIterator++;
		}
		return $advFilterConditionFormat;
	}
	/*	 * *
	 * Function to set the default calendar activity types for new user
	 * @param <Integer> $userId - id of the user
	 */

	public static function setCalendarDefaultActivityTypesForUser($userId)
	{
		$db = PearDatabase::getInstance();
		$userEntries = $db->pquery('SELECT 1 FROM vtiger_calendar_user_activitytypes WHERE userid=?', array($userId));
		$activityIds = [];
		if ($db->num_rows($userEntries) <= 0) {
			$queryResult = $db->pquery('SELECT id, defaultcolor FROM vtiger_calendar_default_activitytypes', []);
			$numRows = $db->num_rows($queryResult);
			for ($i = 0; $i < $numRows; $i++) {
				$row = $db->query_result_rowdata($queryResult, $i);
				$activityIds[$row['id']] = $row['defaultcolor'];
			}
			$db = \App\Db::getInstance();
			foreach ($activityIds as $activityId => $color) {
				$columns = [
					'defaultid' => $activityId,
					'userid' => $userId,
					'color' => $color,
				];
				if (in_array($activityId, array(1, 2))) {
					$columns['visible'] = 1;
				}
				$db->createCommand()->insert('vtiger_calendar_user_activitytypes', $columns)->execute();
			}
		}
	}

	public static function getAllSkins()
	{
		return ['twilight' => '#404952', 'blue' => '#00509e', 'modern' => '#0d9605'];
	}

	public static function isUserDeleted($userid)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT deleted FROM vtiger_users WHERE id = ? && (status=? || deleted=?)', array($userid, 'Inactive', 1));
		$count = $db->num_rows($result);
		if ($count > 0)
			return true;

		return false;
	}
	/*
	 * Function used to get default value based on data type
	 * @param $dataType - data type of field
	 * @return returns default value for data type if match case found
	 * else returns empty string
	 */

	public function getDefaultMandatoryValue($dataType)
	{
		$value;
		switch ($dataType) {
			case 'date':
				$dateObject = new DateTime();
				$value = DateTimeField::convertToUserFormat($dateObject->format('Y-m-d'));
				break;
			case 'time' :
				$value = '00:00:00';
				break;
			case 'boolean':
				$value = false;
				break;
			case 'email':
				$value = '??@??.??';
				break;
			case 'url':
				$value = '???.??';
				break;
			case 'integer':
				$value = 0;
				break;
			case 'double':
				$value = 00.00;
				break;
			case 'currency':
				$value = 0.00;
				break;
			default :
				$value = '?????';
				break;
		}
		return $value;
	}
}
