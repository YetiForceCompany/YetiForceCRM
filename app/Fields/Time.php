<?php
/**
 * Tools for time class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafał Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Fields;

/**
 * Time class.
 */
class Time
{
	/**
	 * Returns time in user format.
	 *
	 * @param string $time
	 * @param bool   $convertTimeZone
	 *
	 * @return string
	 */
	public static function formatToDisplay($time, bool $convertTimeZone = true): string
	{
		return (new \DateTimeField($time))->getDisplayTime(null, $convertTimeZone);
	}

	/**
	 * Returns time in database format.
	 *
	 * @param string|null $time
	 * @param bool        $convertTimeZone
	 *
	 * @return mixed
	 */
	public static function formatToDB($time, bool $convertTimeZone = true)
	{
		return (new \DateTimeField(date(Date::currentUserJSDateFormat()) . ' ' . $time))->getDBInsertTimeValue($convertTimeZone);
	}

	/**
	 * Convert seconds to decimal time format.
	 *
	 * @param int $seconds
	 *
	 * @return float
	 */
	public static function secondsToDecimal(int $seconds)
	{
		$h = floor($seconds / 60 / 60);
		$m = floor(($seconds - ($h * 60 * 60)) / 60);
		return self::timeToDecimal(sprintf('%02d:%02d:%02d', $h, $m, $seconds - ($h * 60 * 60) - ($m * 60)));
	}

	/**
	 * Convert elapsed time from "H:i:s" to decimal equivalent.
	 *
	 * @param string $time "12:00:00"
	 *
	 * @return float
	 */
	public static function timeToDecimal(string $time)
	{
		$hms = explode(':', $time);
		return $hms[0] + ($hms[1] / 60) + ($hms[2] / 3600);
	}

	/**
	 * Function changes the time format to the database format without changing the time zone.
	 *
	 * @param string $time
	 *
	 * @return string
	 */
	public static function getTimeByDBFormat(string $time)
	{
		if ($time) {
			$timeDetails = array_pad(explode(' ', $time), 2, '');
			[$hours, $minutes, $seconds] = array_pad(explode(':', $timeDetails[0]), 3, '00');
			if ('PM' === $timeDetails[1] && '12' !== $hours) {
				$hours = $hours + 12;
			}
			if ('AM' === $timeDetails[1] && '12' === $hours) {
				$hours = '00';
			}
			$time = "$hours:$minutes:$seconds";
		}
		return $time;
	}
}
