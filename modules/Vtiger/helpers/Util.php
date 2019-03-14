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
		if (0 === $seconds) {
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
			if (0 !== $month) {
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
		return $count . ' ' . ((1 === $count) ? \App\Language::translate("$text") : \App\Language::translate("${text}S"));
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

		[$dateInUserFormat, $timeInUserFormat] = explode(' ', $dateTimeInUserFormat);
		[$hours, $minutes] = explode(':', $timeInUserFormat);

		$displayTime = $hours . ':' . $minutes;
		if ('12' === $currentUser->get('hour_format')) {
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
			if ('mm-dd-yyyy' === $currentUser->get('date_format')) {
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
		if ($userObject) {
			$userModel = Users_Privileges_Model::getInstanceFromUserObject($userObject);
		} else {
			$userModel = Users_Privileges_Model::getCurrentUserModel();
		}

		if ('12' === $userModel->get('hour_format')) {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}
		return $time;
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
		return (new App\Db\Query())->from('vtiger_users')
			->where(['id' => $userid])
			->andWhere(['or', ['deleted' => 1], ['status' => 'Inactive']])
			->exists();
	}
}
