<?php
/**
 * Tools for datetime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

namespace App\Fields;

/**
 * DateTime class.
 */
class DateTime
{
	/**
	 * Function returns the date in user specified format.
	 *
	 * @param string $value Date time
	 *
	 * @return string
	 */
	public static function formatToDisplay($value)
	{
		if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
			return '';
		}
		if ($value === 'now') {
			$value = null;
		}
		return (new \DateTimeField($value))->getDisplayDateTimeValue();
	}

	/**
	 * Function to get date and time value for db format.
	 *
	 * @param string $value        Date time
	 * @param bool   $leadingZeros
	 *
	 * @return string
	 */
	public static function formatToDb($value, $leadingZeros = false)
	{
		if ($leadingZeros) {
			$delim = ['/', '.'];
			foreach ($delim as $delimiter) {
				$x = strpos($value, $delimiter);
				if ($x !== false) {
					$value = str_replace($delimiter, '-', $value);
					break;
				}
			}
			list($y, $m, $d) = explode('-', $value);
			if (strlen($y) == 1) {
				$y = '0' . $y;
			}
			if (strlen($m) == 1) {
				$m = '0' . $m;
			}
			if (strlen($d) == 1) {
				$d = '0' . $d;
			}
			$value = implode('-', [$y, $m, $d]);
			$valueList = explode(' ', $value);
			$dbTimeValue = $valueList[1];
			if (!empty($dbTimeValue) && strpos($dbTimeValue, ':') === false) {
				$dbTimeValue = $dbTimeValue . ':';
			}
			if (!empty($dbTimeValue) && strrpos($dbTimeValue, ':') == (strlen($dbTimeValue) - 1)) {
				$dbTimeValue = $dbTimeValue . '00';
			}

			return (new \DateTimeField($valueList[0] . ' ' . $dbTimeValue))->getDBInsertDateTimeValue();
		}
		return (new \DateTimeField($value))->getDBInsertDateTimeValue();
	}

	/**
	 * The function returns the date according to the user's settings.
	 *
	 * @param string $dateTime Date time
	 *
	 * @return string
	 */
	public static function formatToViewDate($dateTime)
	{
		switch (\App\User::getCurrentUserModel()->getDetail('view_date_format')) {
			case 'PLL_FULL':
				return '<span title="' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '">' . static::formatToDisplay($dateTime) . '</span>';
			case 'PLL_ELAPSED':
				return '<span title="' . static::formatToDay($dateTime) . '">' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '</span>';
			case 'PLL_FULL_AND_DAY':
				return '<span title="' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '">' . static::formatToDay($dateTime) . '</span>';
			default:
				break;
		}
		return '-';
	}

	/**
	 * Crop date if today and only return the hour.
	 *
	 * @param string $dateTime Date time
	 *
	 * @return string
	 */
	public static function formatToShort(string $dateTime)
	{
		if ((new \DateTime($dateTime))->format('Y-m-d') === date('Y-m-d')) {
			return \App\Fields\Time::formatToDisplay($dateTime);
		}
		return static::formatToDisplay($dateTime);
	}

	/**
	 * Function to parse dateTime into days.
	 *
	 * @param string $dateTime Date time
	 * @param bool   $allday
	 *
	 * @return string
	 */
	public static function formatToDay($dateTime, $allday = false)
	{
		[$formatedDate, $timeInUserFormat] = explode(' ', static::formatToDisplay($dateTime));
		$dateDay = Date::getDayFromDate($dateTime, false, true);
		if (!$allday) {
			$timeInUserFormat = explode(':', $timeInUserFormat);
			if (\count($timeInUserFormat) === 3) {
				[$hours, $minutes, $seconds] = $timeInUserFormat;
			} else {
				[$hours, $minutes] = $timeInUserFormat;
				$seconds = '';
			}
			$displayTime = $hours . ':' . $minutes . ' ' . $seconds;
			$formatedDate .= ' ' . \App\Language::translate('LBL_AT') . ' ' . $displayTime;
		}
		return $formatedDate . " ($dateDay)";
	}

	/**
	 * Time zone cache.
	 *
	 * @var string
	 */
	protected static $databaseTimeZone = false;

	/**
	 * Get system time zone.
	 *
	 * @return string
	 */
	public static function getTimeZone()
	{
		if (!static::$databaseTimeZone) {
			$defaultTimeZone = date_default_timezone_get();
			if (empty($defaultTimeZone)) {
				$defaultTimeZone = \AppConfig::main('default_timezone');
			}
			static::$databaseTimeZone = $defaultTimeZone;
		}
		return static::$databaseTimeZone;
	}
}
