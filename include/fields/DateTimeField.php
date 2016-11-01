<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once 'include/utils/utils.php';

class DateTimeField
{

	static protected $databaseTimeZone = null;
	protected $datetime;
	private static $cache = [];

	/**
	 *
	 * @param type $value
	 */
	public function __construct($value)
	{
		if (empty($value)) {
			$value = date('Y-m-d H:i:s');
		}
		$this->date = null;
		$this->time = null;
		$this->datetime = $value;
	}

	/** Function to set date values compatible to database (YY_MM_DD)
	 * @param $user -- value :: Type Users
	 * @returns $insert_date -- insert_date :: Type string
	 */
	public function getDBInsertDateValue($user = null)
	{

		\App\Log::trace('Start ' . __METHOD__ . '(' . $this->datetime . ')');
		$value = explode(' ', $this->datetime);
		if (count($value) == 2) {
			$value[0] = self::convertToUserFormat($value[0]);
		}
		$insert_date = '';
		if (!empty($value[1])) {
			$date = self::convertToDBTimeZone($this->datetime, $user);
			$insert_date = $date->format('Y-m-d');
		} else {
			$insert_date = self::convertToDBFormat($value[0]);
		}
		\App\Log::trace('End ' . __METHOD__);
		return $insert_date;
	}

	/**
	 *
	 * @param Users $user
	 * @return String
	 */
	public function getDBInsertDateTimeValue($user = null)
	{

		\App\Log::trace(__METHOD__);
		return $this->getDBInsertDateValue($user) . ' ' . $this->getDBInsertTimeValue($user);
	}

	public function getDisplayDateTimeValue($user = null)
	{

		\App\Log::trace(__METHOD__);
		return $this->getDisplayDate($user) . ' ' . $this->getDisplayTime($user);
	}

	public function getFullcalenderDateTimevalue($user = null)
	{
		return $this->getDisplayDate($user) . ' ' . $this->getFullcalenderTime($user);
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $date
	 * @param Users $user
	 * @return type
	 */
	public static function convertToDBFormat($date, $user = null)
	{

		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		\App\Log::trace('Start ' . __METHOD__ . ' ' . serialize($date));
		if (empty($user)) {
			$user = $current_user;
		}

		$format = $current_user->date_format;
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		$return = self::__convertToDBFormat($date, $format);
		\App\Log::trace('End ' . __METHOD__);
		return $return;
	}

	/**
	 *
	 * @param type $date
	 * @param string $format
	 * @return string
	 */
	public static function __convertToDBFormat($date, $format)
	{

		\App\Log::trace('Start ' . __METHOD__ . ' ' . serialize($date) . ' | ' . $format);
		if (empty($date)) {
			\App\Log::trace('End ' . __METHOD__);
			return $date;
		}
		if ($format == '') {
			$format = 'yyyy-mm-dd';
		}
		$dbDate = '';
		switch ($format) {
			case 'dd-mm-yyyy': list($d, $m, $y) = explode('-', $date);
				break;
			case 'mm-dd-yyyy': list($m, $d, $y) = explode('-', $date);
				break;
			case 'yyyy-mm-dd': list($y, $m, $d) = explode('-', $date);
				break;
			case 'dd.mm.yyyy': list($d, $m, $y) = explode('.', $date);
				break;
			case 'mm.dd.yyyy': list($m, $d, $y) = explode('.', $date);
				break;
			case 'yyyy.mm.dd': list($y, $m, $d) = explode('.', $date);
				break;
			case 'dd/mm/yyyy': list($d, $m, $y) = explode('/', $date);
				break;
			case 'mm/dd/yyyy': list($m, $d, $y) = explode('/', $date);
				break;
			case 'yyyy/mm/dd': list($y, $m, $d) = explode('/', $date);
				break;
		}

		if (!$y || !$m || !$d) {
			if (strpos($date, '-') !== false) {
				$separator = '-';
			} elseif (strpos($date, '.') !== false) {
				$separator = '.';
			} elseif (strpos($date, '/') !== false) {
				$separator = '/';
			}
			$formatToConvert = str_replace(array('/', '.'), array('-', '-'), $format);
			$dateToConvert = str_replace($separator, '-', $date);
			switch ($formatToConvert) {
				case 'dd-mm-yyyy': list($d, $m, $y) = explode('-', $dateToConvert);
					break;
				case 'mm-dd-yyyy': list($m, $d, $y) = explode('-', $dateToConvert);
					break;
				case 'yyyy-mm-dd': list($y, $m, $d) = explode('-', $dateToConvert);
					break;
			}
			$dbDate = $y . '-' . $m . '-' . $d;
		} elseif (!$y && !$m && !$d) {
			$dbDate = '';
		} else {
			$dbDate = $y . '-' . $m . '-' . $d;
		}
		\App\Log::trace('End ' . __METHOD__);
		return $dbDate;
	}

	/**
	 *
	 * @param Mixed $date
	 * @return Array
	 */
	public static function convertToInternalFormat($date)
	{
		if (!is_array($date)) {
			$date = explode(' ', $date);
		}
		return $date;
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $date
	 * @param Users $user
	 * @return type
	 */
	public static function convertToUserFormat($date, $user = null)
	{
		if (empty($user)) {
			$user = vglobal('current_user');
		}
		$format = $user->date_format;
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		return self::__convertToUserFormat($date, $format);
	}

	/**
	 *
	 * @param type $date
	 * @param type $format
	 * @return type
	 */
	public static function __convertToUserFormat($date, $format)
	{

		\App\Log::trace('Start ' . __METHOD__ . ' ' . serialize($date) . ' | ' . $format);
		$date = self::convertToInternalFormat($date);
		$separator = '-';
		if (strpos($date[0], '-') !== false) {
			$separator = '-';
		} elseif (strpos($date[0], '.') !== false) {
			$separator = '.';
		} elseif (strpos($date[0], '/') !== false) {
			$separator = '/';
		}
		list($y, $m, $d) = explode($separator, $date[0]);

		switch ($format) {
			case 'dd-mm-yyyy': $date[0] = $d . '-' . $m . '-' . $y;
				break;
			case 'mm-dd-yyyy': $date[0] = $m . '-' . $d . '-' . $y;
				break;
			case 'yyyy-mm-dd': $date[0] = $y . '-' . $m . '-' . $d;
				break;
			case 'dd.mm.yyyy': $date[0] = $d . '.' . $m . '.' . $y;
				break;
			case 'mm.dd.yyyy': $date[0] = $m . '.' . $d . '.' . $y;
				break;
			case 'yyyy.mm.dd': $date[0] = $y . '.' . $m . '.' . $d;
				break;
			case 'dd/mm/yyyy': $date[0] = $d . '/' . $m . '/' . $y;
				break;
			case 'mm/dd/yyyy': $date[0] = $m . '/' . $d . '/' . $y;
				break;
			case 'yyyy/mm/dd': $date[0] = $y . '/' . $m . '/' . $d;
				break;
		}

		if (isset($date[1]) && $date[1] != '') {
			$userDate = $date[0] . ' ' . $date[1];
		} else {
			$userDate = $date[0];
		}
		\App\Log::trace('End ' . __METHOD__);
		return $userDate;
	}

	public static function getDayFromDate($date)
	{
		return date('l', strtotime($date));
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $value
	 * @param Users $user
	 */
	public static function convertToUserTimeZone($value, $user = null)
	{

		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		\App\Log::trace('Start ' . __METHOD__ . "($value) method ...");
		if (empty($user)) {
			$user = $current_user;
		}
		$timeZone = $user->time_zone ? $user->time_zone : AppConfig::main('default_timezone');
		$return = DateTimeField::convertTimeZone($value, self::getDBTimeZone(), $timeZone);
		\App\Log::trace('End ' . __METHOD__);
		return $return;
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $value
	 * @param Users $user
	 */
	public static function convertToDBTimeZone($value, $user = null, $formatDate = true)
	{

		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		\App\Log::trace('Start ' . __METHOD__ . "($value)");
		if (empty($user)) {
			$user = $current_user;
		}
		$timeZone = $user->time_zone ? $user->time_zone : AppConfig::main('default_timezone');

		if ($formatDate) {
			$value = self::sanitizeDate($value, $user);
		}

		$return = DateTimeField::convertTimeZone($value, $timeZone, self::getDBTimeZone());
		\App\Log::trace('End ' . __METHOD__);
		return $return;
	}

	/**
	 *
	 * @param type $time
	 * @param type $sourceTimeZoneName
	 * @param type $targetTimeZoneName
	 * @return DateTime
	 */
	public static function convertTimeZone($time, $sourceTimeZoneName, $targetTimeZoneName)
	{

		\App\Log::trace('Start ' . __METHOD__ . "($time, $sourceTimeZoneName, $targetTimeZoneName)");

		$sourceTimeZone = new DateTimeZone($sourceTimeZoneName);
		if ($time == '24:00')
			$time = '00:00';
		$current_user = vglobal('current_user');
		$format = $current_user->date_format;
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		$time = str_replace(".", "-", $time);
		$time = str_replace("/", "-", $time);

		$myDateTime = new DateTime($time, $sourceTimeZone);

		// convert this to target timezone using the DateTimeZone object
		$targetTimeZone = new DateTimeZone($targetTimeZoneName);
		$myDateTime->setTimeZone($targetTimeZone);
		self::$cache[$time][$targetTimeZoneName] = $myDateTime;
		//}
		$myDateTime = self::$cache[$time][$targetTimeZoneName];
		\App\Log::trace('End ' . __METHOD__);
		return $myDateTime;
	}

	/** Function to set timee values compatible to database (GMT)
	 * @param $user -- value :: Type Users
	 * @returns $insert_date -- insert_date :: Type string
	 */
	public function getDBInsertTimeValue($user = null)
	{

		\App\Log::trace('Start ' . __METHOD__ . '(' . $this->datetime . ')');
		$date = self::convertToDBTimeZone($this->datetime, $user);
		\App\Log::trace('End ' . __METHOD__);
		return $date->format("H:i:s");
	}

	/**
	 * This function returns the date in user specified format.
	 * @global Users $current_user
	 * @return string
	 */
	public function getDisplayDate($user = null)
	{
		$date_value = explode(' ', $this->datetime);
		if (isset($date_value[1]) && $date_value[1] != '') {
			$date = self::convertToUserTimeZone($this->datetime, $user);
			$date_value = $date->format('Y-m-d');
		}

		$display_date = self::convertToUserFormat($date_value, $user);
		return $display_date;
	}

	public function getDisplayTime($user = null)
	{

		\App\Log::trace('Start ' . __METHOD__ . '(' . $this->datetime . ')');
		$date = self::convertToUserTimeZone($this->datetime, $user);
		$time = $date->format("H:i");

		//Convert time to user preferred value
		if (\App\User::getCurrentUserModel()->getDetail('hour_format') === '12') {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}
		\App\Log::trace('End ' . __METHOD__);
		return $time;
	}

	public function getFullcalenderTime($user = null)
	{

		\App\Log::trace("Entering getDisplayTime(" . $this->datetime . ") method ...");
		$date = self::convertToUserTimeZone($this->datetime, $user);
		$time = $date->format("H:i:s");
		\App\Log::trace("Exiting getDisplayTime method ...");
		return $time;
	}

	static function getDBTimeZone()
	{
		if (empty(self::$databaseTimeZone)) {
			$defaultTimeZone = date_default_timezone_get();
			if (empty($defaultTimeZone)) {
				$defaultTimeZone = 'UTC';
			}
			self::$databaseTimeZone = $defaultTimeZone;
		}
		return self::$databaseTimeZone;
	}

	static function getPHPDateFormat($user = null)
	{
		$current_user = vglobal('current_user');
		if (empty($user)) {
			$user = $current_user;
		}
		$dateFormat = empty($user->date_format) ? 'Y-m-d' : $user->date_format;
		return str_replace(array('yyyy', 'mm', 'dd'), array('Y', 'm', 'd'), $dateFormat);
	}

	private static function sanitizeDate($value, $user)
	{
		$current_user = vglobal('current_user');
		if (empty($user)) {
			$user = $current_user;
		}
		if (strlen($value) < 8) {
			return $value;
		}

		$value = str_replace('T', ' ', $value);
		list($date, $time) = explode(' ', $value);
		if (!empty($date) && !in_array($time, ['AM', 'PM'])) {
			$date = self::__convertToDBFormat($date, $user->date_format);
			$value = $date;
			if (!empty($time)) {
				$value .= ' ' . rtrim($time);
			}
		}
		return $value;
	}
}
