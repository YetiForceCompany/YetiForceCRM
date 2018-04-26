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
	 * Function parses date into readable format.
	 *
	 * @param <Date Time> $dateTime
	 *
	 * @return string
	 */
	public static function formatDateDiffInStrings($dateTime)
	{
		// http://www.php.net/manual/en/datetime.diff.php#101029
		$seconds = strtotime('now') - strtotime($dateTime);
		if ($seconds === 0) {
			return \App\Language::translate('LBL_JUSTNOW');
		}
		if ($seconds > 0) {
			$prefix = '';
			$suffix = ' ' . \App\Language::translate('LBL_AGO');
		} elseif ($seconds < 0) {
			$prefix = \App\Language::translate('LBL_DUE') . ' ';
			$suffix = '';
			$seconds = -($seconds);
		}

		$minutes = floor($seconds / 60);
		$hours = floor($minutes / 60);
		$days = floor($hours / 24);
		$months = floor($days / 30);

		if ($seconds < 60) {
			return $prefix . self::pluralize($seconds, 'LBL_SECOND') . $suffix;
		}
		if ($minutes < 60) {
			return $prefix . self::pluralize($minutes, 'LBL_MINUTE') . $suffix;
		}
		if ($hours < 24) {
			return $prefix . self::pluralize($hours, 'LBL_HOUR') . $suffix;
		}
		if ($days < 30) {
			return $prefix . self::pluralize($days, 'LBL_DAY') . $suffix;
		}
		if ($months < 12) {
			return $prefix . self::pluralize($months, 'LBL_MONTH') . $suffix;
		}
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
	 * Function returns singular or plural text.
	 *
	 * @param <Number> $count
	 * @param string   $text
	 *
	 * @return string
	 */
	public static function pluralize($count, $text)
	{
		return $count . ' ' . (($count == 1) ? \App\Language::translate("$text") : \App\Language::translate("${text}S"));
	}

	/**
	 * Function that will strip all the tags while displaying.
	 *
	 * @param string $input - html data
	 *
	 * @return string vtiger6 displayable data
	 */
	public static function toVtiger6SafeHTML($input)
	{
		$allowableTags = '<a><br />';

		return strip_tags($input, $allowableTags);
	}

	/**
	 * Function to parses date into string format.
	 *
	 * @param <Date> $date
	 * @param <Time> $time
	 *
	 * @return string
	 */
	public static function formatDateIntoStrings($date, $time = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = App\Fields\DateTime::formatToDisplay($date . ' ' . $time);

		list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateTimeInUserFormat);
		list($hours, $minutes) = explode(':', $timeInUserFormat);

		$displayTime = $hours . ':' . $minutes;
		if ($currentUser->get('hour_format') === '12') {
			$displayTime = Vtiger_Time_UIType::getTimeValueInAMorPM($displayTime);
		}

		$today = App\Fields\Date::formatToDisplay(date('Y-m-d H:i:s'));
		$tomorrow = App\Fields\Date::formatToDisplay(date('Y-m-d H:i:s', strtotime('tomorrow')));
		$userDate = DateTimeField::__convertToUserFormat($date, $currentUser->get('date_format'));

		if ($dateInUserFormat == $today) {
			$todayInfo = \App\Language::translate('LBL_TODAY');
			if ($time) {
				$todayInfo .= ' ' . \App\Language::translate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($todayInfo)";
		} elseif ($dateInUserFormat == $tomorrow) {
			$tomorrowInfo = \App\Language::translate('LBL_TOMORROW');
			if ($time) {
				$tomorrowInfo .= ' ' . \App\Language::translate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($tomorrowInfo)";
		} else {
			if ($currentUser->get('date_format') === 'mm-dd-yyyy') {
				$dateInUserFormat = str_replace('-', '/', $dateInUserFormat);
			}
			$date = strtotime($dateInUserFormat);
			$dayInfo = \App\Language::translate('LBL_' . date('D', $date));
			if ($time) {
				$dayInfo .= ' ' . \App\Language::translate('LBL_AT') . ' ' . $displayTime;
			}
			$formatedDate = $userDate . " ($dayInfo)";
		}
		return $formatedDate;
	}

	/**
	 * Function to replace spaces with under scores.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function replaceSpaceWithUnderScores($string)
	{
		return str_replace(' ', '_', $string);
	}

	/**
	 * Function gets the CRM's base Currency information.
	 *
	 * @return array
	 */
	public static function getBaseCurrency()
	{
		return (new \App\Db\Query())->from('vtiger_currency_info')->where(['<', 'defaultid', '0'])->one();
	}

	/**
	 * Function to get maximum upload size.
	 *
	 * @return float maximum upload size
	 */
	public static function getMaxUploadSize()
	{
		$upload_maxsize = \AppConfig::main('upload_maxsize');
		return ceil($upload_maxsize / (1024 * 1024));
	}

	/**
	 * Function decodes the utf-8 characters.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function getDecodedValue($string)
	{
		return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	public static function getActiveAdminCurrentDateTime()
	{
		$default_timezone = \AppConfig::main('default_timezone');
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		date_default_timezone_set($adminTimeZone);
		$date = date('Y-m-d H:i:s');
		date_default_timezone_set($default_timezone);
		return $date;
	}

	/**
	 * Function to get the time value in user preferred hour format.
	 *
	 * @param <Time>               $time
	 * @param <Vtiger_Users_Model> $userObject
	 *
	 * @return string time with hour format
	 */
	public static function convertTimeIntoUsersDisplayFormat($time, $userObject = null)
	{
		require_once 'include/runtime/LanguageHandler.php';
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
			$groupColumnsInfo = [];
			foreach ($groupInfo as &$fieldSearchInfo) {
				list($fieldName, $operator, $fieldValue, $specialOption) = $fieldSearchInfo;
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
			++$groupIterator;
		}
		return $advFilterConditionFormat;
	}

	public static function getAllSkins()
	{
		return [
			'twilight' => '#404952',
			//'modern' => '#0d9605'
		];
	}

	public static function isUserDeleted($userid)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT deleted FROM vtiger_users WHERE id = ? && (status=? || deleted=?)', [$userid, 'Inactive', 1]);
		$count = $db->numRows($result);
		if ($count > 0) {
			return true;
		}
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
		switch ($dataType) {
			case 'date':
				$dateObject = new DateTime();
				$value = DateTimeField::convertToUserFormat($dateObject->format('Y-m-d'));
				break;
			case 'time':
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
			default:
				$value = '?????';
				break;
		}
		return $value;
	}
}
