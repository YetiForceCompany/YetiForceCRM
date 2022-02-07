<?php
/**
 * Tools for datetime class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		if (empty($value) || '0000-00-00' === $value || '0000-00-00 00:00:00' === $value) {
			return '';
		}
		if ('now' === $value) {
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
				if (false !== $x) {
					$value = str_replace($delimiter, '-', $value);
					break;
				}
			}
			[$y, $m, $d] = explode('-', $value);
			if (1 == \strlen($y)) {
				$y = '0' . $y;
			}
			if (1 == \strlen($m)) {
				$m = '0' . $m;
			}
			if (1 == \strlen($d)) {
				$d = '0' . $d;
			}
			$value = implode('-', [$y, $m, $d]);
			$valueList = explode(' ', $value);
			$dbTimeValue = $valueList[1];
			if (!empty($dbTimeValue) && false === strpos($dbTimeValue, ':')) {
				$dbTimeValue = $dbTimeValue . ':';
			}
			if (!empty($dbTimeValue) && strrpos($dbTimeValue, ':') == (\strlen($dbTimeValue) - 1)) {
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
			if (3 === \count($timeInUserFormat)) {
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
				$defaultTimeZone = \App\Config::main('default_timezone');
			}
			static::$databaseTimeZone = $defaultTimeZone;
		}
		return static::$databaseTimeZone;
	}

	/**
	 * Function returning difference in format between date times.
	 *
	 * @param string $start  ex. '2017-07-10 11:45:56
	 * @param string $end    ex. 2017-07-30 12:08:19
	 * @param string $format Default %a
	 *
	 * @see https://secure.php.net/manual/en/class.dateinterval.php
	 * @see https://secure.php.net/manual/en/dateinterval.format.php
	 *
	 * @return int|string difference in format
	 */
	public static function getDiff($start, $end, $format = '%a')
	{
		$interval = (new \DateTime($start))->diff(new \DateTime($end));
		switch ($format) {
			case 'years':
				return $interval->format('%Y');
			case 'months':
				$years = $interval->format('%Y');
				$months = 0;
				if ($years) {
					$months += $years * 12;
				}
				return $months + $interval->format('%m');
			case 'days':
				return $interval->format('%a');
			case 'hours':
				$days = $interval->format('%a');
				$hours = 0;
				if ($days) {
					$hours += 24 * $days;
				}
				return $hours + $interval->format('%H');
			case 'minutes':
				$days = $interval->format('%a');
				$minutes = 0;
				if ($days) {
					$minutes += 24 * 60 * $days;
				}
				$hours = $interval->format('%H');
				if ($hours) {
					$minutes += 60 * $hours;
				}
				return $minutes + $interval->format('%i');
			case 'seconds':
				$days = $interval->format('%a');
				$seconds = 0;
				if ($days) {
					$seconds += 24 * 60 * 60 * $days;
				}
				$hours = $interval->format('%H');
				if ($hours) {
					$seconds += 60 * 60 * $hours;
				}
				$minutes = $interval->format('%i');
				if ($minutes) {
					$seconds += 60 * $minutes;
				}
				return $seconds + $interval->format('%s');
			default:
				break;
		}
		return $interval->format($format);
	}

	/**
	 * Function changes the datetime format to the database format without changing the time zone.
	 *
	 * @param string $value
	 * @param string $fromFormat
	 *
	 * @return string
	 */
	public static function sanitizeDbFormat(string $value, string $fromFormat): string
	{
		[$date, $time] = array_pad(explode(' ', $value, 2), 2, '');
		if (!empty($date)) {
			$date = \App\Fields\Date::sanitizeDbFormat($date, $fromFormat);
			$value = $date;
			if (!empty($time)) {
				$value .= ' ' . \App\Fields\Time::sanitizeDbFormat($time);
			}
		}
		return $value;
	}
}
