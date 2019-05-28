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
	 *
	 * @return string
	 */
	public static function formatToDisplay($time)
	{
		return (new \DateTimeField($time))->getDisplayTime();
	}

	/**
	 * Returns time in database format.
	 *
	 * @param $time
	 *
	 * @return mixed
	 */
	public static function formatToDB($time)
	{
		return (new \DateTimeField(date(Date::currentUserJSDateFormat()) . ' ' . $time))->getDBInsertTimeValue();
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
}
