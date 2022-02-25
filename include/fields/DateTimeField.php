<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';

class DateTimeField
{
	protected $datetime;
	private static $cache = [];

	/**
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

	/** Function to set date values compatible to database (YY_MM_DD).
	 * @param $user -- value :: Type Users
	 * @returns $insert_date -- insert_date :: Type string
	 */
	public function getDBInsertDateValue()
	{
		$value = explode(' ', $this->datetime, 2);
		$insert_date = '';
		if (!empty($value[1])) {
			$date = self::convertToDBTimeZone($this->datetime);
			$insert_date = $date->format('Y-m-d');
		} else {
			$insert_date = self::convertToDBFormat($value[0]);
		}
		return $insert_date;
	}

	/**
	 * @param Users $user
	 *
	 * @return string
	 */
	public function getDBInsertDateTimeValue()
	{
		\App\Log::trace(__METHOD__);
		return $this->getDBInsertDateValue() . ' ' . $this->getDBInsertTimeValue();
	}

	public function getDisplayDateTimeValue($user = null)
	{
		\App\Log::trace(__METHOD__);
		return $this->getDisplayDate($user) . ' ' . $this->getDisplayTime($user);
	}

	/**
	 * Get full datetime value (with seconds).
	 *
	 * @param App\User|null $user
	 *
	 * @return string
	 */
	public function getDisplayFullDateTimeValue($user = null): string
	{
		return $this->getDisplayDate($user) . ' ' . $this->getDisplayTime($user, true, true);
	}

	public function getFullcalenderDateTimevalue($user = null)
	{
		return $this->getDisplayDate($user) . ' ' . $this->getFullcalenderTime($user);
	}

	/**
	 * @param string    $date
	 * @param \App\User $user
	 *
	 * @return string
	 */
	public static function convertToDBFormat($date, $user = null)
	{
		\App\Log::trace('Start ' . __METHOD__ . ' ' . serialize($date));
		if (empty($user)) {
			$user = \App\User::getCurrentUserModel();
		}
		$format = $user->getDetail('date_format');
		if (empty($format)) {
			$format = 'yyyy-mm-dd';
		}
		$return = self::__convertToDBFormat($date, $format);
		\App\Log::trace('End ' . __METHOD__);
		return $return;
	}

	/**
	 * @param string $date
	 * @param string $format
	 *
	 * @return string
	 */
	public static function __convertToDBFormat($date, $format)
	{
		if (empty($date)) {
			\App\Log::trace('End ' . __METHOD__);

			return $date;
		}
		if ('' == $format) {
			$format = 'yyyy-mm-dd';
		}
		return \App\Fields\Date::sanitizeDbFormat($date, $format);
	}

	/**
	 * @param string $date
	 * @param Users  $user
	 *
	 * @return string
	 */
	public static function convertToUserFormat($date)
	{
		$userDate = '';
		if (!empty($date)) {
			$format = \App\User::getCurrentUserModel()->getDetail('date_format') ?: 'yyyy-mm-dd';
			$userDate = self::__convertToUserFormat($date, $format);
		}

		return $userDate;
	}

	/**
	 * @param string $date
	 * @param string $format
	 *
	 * @return string
	 */
	public static function __convertToUserFormat($date, $format)
	{
		\App\Log::trace('Start ' . __METHOD__ . ' ' . serialize($date) . ' | ' . $format);
		if (!\is_array($date)) {
			$date = explode(' ', $date);
		}
		$separator = '-';
		if (false !== strpos($date[0], '-')) {
			$separator = '-';
		} elseif (false !== strpos($date[0], '.')) {
			$separator = '.';
		} elseif (false !== strpos($date[0], '/')) {
			$separator = '/';
		}
		[$y, $m, $d] = array_pad(explode($separator, $date[0]), 3, null);

		switch ($format) {
			case 'dd-mm-yyyy':
				$date[0] = $d . '-' . $m . '-' . $y;
				break;
			case 'mm-dd-yyyy':
				$date[0] = $m . '-' . $d . '-' . $y;
				break;
			case 'yyyy-mm-dd':
				$date[0] = $y . '-' . $m . '-' . $d;
				break;
			case 'dd.mm.yyyy':
				$date[0] = $d . '.' . $m . '.' . $y;
				break;
			case 'mm.dd.yyyy':
				$date[0] = $m . '.' . $d . '.' . $y;
				break;
			case 'yyyy.mm.dd':
				$date[0] = $y . '.' . $m . '.' . $d;
				break;
			case 'dd/mm/yyyy':
				$date[0] = $d . '/' . $m . '/' . $y;
				break;
			case 'mm/dd/yyyy':
				$date[0] = $m . '/' . $d . '/' . $y;
				break;
			case 'yyyy/mm/dd':
				$date[0] = $y . '/' . $m . '/' . $d;
				break;
			default:
				break;
		}

		if (isset($date[1]) && '' != $date[1]) {
			$userDate = $date[0] . ' ' . $date[1];
		} else {
			$userDate = $date[0];
		}
		\App\Log::trace('End ' . __METHOD__);
		return $userDate;
	}

	/**
	 * @param string $value
	 * @param Users  $user
	 */
	public static function convertToUserTimeZone($value, $user = null)
	{
		$current_user = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		\App\Log::trace('Start ' . __METHOD__ . "($value) method ...");
		if (empty($user)) {
			$user = $current_user;
		}
		$timeZone = \is_object($user) ? $user->time_zone : App\Config::main('default_timezone');
		$return = self::convertTimeZone($value, App\Fields\DateTime::getTimeZone(), $timeZone);
		\App\Log::trace('End ' . __METHOD__);
		return $return;
	}

	/**
	 * @param string $value
	 * @param Users  $user
	 * @param mixed  $formatDate
	 */
	public static function convertToDBTimeZone($value, $user = null, $formatDate = true)
	{
		if (empty($user)) {
			$user = \App\User::getCurrentUserModel();
		}
		$timeZone = $user->getDetail('time_zone');
		if ($formatDate) {
			$value = self::sanitizeDate($value, $user);
		}
		return self::convertTimeZone($value, $timeZone, App\Fields\DateTime::getTimeZone());
	}

	/**
	 * @param type $time
	 * @param type $sourceTimeZoneName
	 * @param type $targetTimeZoneName
	 *
	 * @return DateTime
	 */
	public static function convertTimeZone($time, $sourceTimeZoneName, $targetTimeZoneName)
	{
		\App\Log::trace('Start ' . __METHOD__ . "($time, $sourceTimeZoneName, $targetTimeZoneName)");
		$sourceTimeZone = new DateTimeZone($sourceTimeZoneName);
		if ('24:00' == $time) {
			$time = '00:00';
		}
		$time = str_replace('.', '-', $time);
		$time = str_replace('/', '-', $time);
		$myDateTime = new DateTime($time, $sourceTimeZone);
		// convert this to target timezone using the DateTimeZone object
		$targetTimeZone = new DateTimeZone($targetTimeZoneName);
		$myDateTime->setTimeZone($targetTimeZone);
		self::$cache[$time][$targetTimeZoneName] = $myDateTime;
		$myDateTime = self::$cache[$time][$targetTimeZoneName];
		\App\Log::trace('End ' . __METHOD__);
		return $myDateTime;
	}

	/**
	 * Function to set time values compatible to database (GMT).
	 *
	 * @param bool $convertTimeZone
	 *
	 * @return string
	 */
	public function getDBInsertTimeValue(bool $convertTimeZone = true)
	{
		if ($convertTimeZone) {
			$date = self::convertToDBTimeZone($this->datetime);
		} else {
			$date = new DateTime($this->datetime);
		}
		return $date->format('H:i:s');
	}

	/**
	 * This function returns the date in user specified format.
	 *
	 * @param App\User|null $user
	 * @param bool          $convertTimeZone
	 *
	 * @return string
	 */
	public function getDisplayDate($user = null, bool $convertTimeZone = true): string
	{
		$date_value = explode(' ', $this->datetime);
		if (isset($date_value[1]) && '' != $date_value[1]) {
			if ($convertTimeZone) {
				$date = self::convertToUserTimeZone($this->datetime, $user);
			} else {
				$date = new DateTime($this->datetime);
			}
			$date_value = $date->format('Y-m-d');
		}
		return self::convertToUserFormat($date_value);
	}

	/**
	 * Get display time.
	 *
	 * @param \App\User|null $user
	 * @param bool           $convertTimeZone
	 * @param bool           $fullTime        (with seconds)
	 *
	 * @return string
	 */
	public function getDisplayTime($user = null, bool $convertTimeZone = true, bool $fullTime = false): string
	{
		if ($convertTimeZone) {
			$date = self::convertToUserTimeZone($this->datetime, $user);
		} else {
			$date = new DateTime($this->datetime);
		}
		$time = $fullTime ? $date->format('H:i:s') : $date->format('H:i');
		//Convert time to user preferred value
		if ('12' === \App\User::getCurrentUserModel()->getDetail('hour_format')) {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}

		return $time;
	}

	public function getFullcalenderTime($user = null)
	{
		\App\Log::trace('Entering getDisplayTime(' . $this->datetime . ') method ...');
		$date = self::convertToUserTimeZone($this->datetime, $user);
		$time = $date->format('H:i:s');
		\App\Log::trace('Exiting getDisplayTime method ...');
		return $time;
	}

	/**
	 * Sanitize date.
	 *
	 * @param string         $value
	 * @param \App\User|null $user
	 *
	 * @return string $value
	 */
	private static function sanitizeDate(string $value, $user): string
	{
		if (empty($user)) {
			$user = \App\User::getCurrentUserModel();
		}
		if (\strlen($value) < 8) {
			return $value;
		}
		$value = str_replace('T', ' ', $value);
		return \App\Fields\DateTime::sanitizeDbFormat($value, $user->getDetail('date_format'));
	}
}
