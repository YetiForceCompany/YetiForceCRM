<?php
/**
 * Tools for time class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafał Pospiech <r.pospiech@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		return (new \DateTimeField(date('Y-m-d') . ' ' . $time))->getDBInsertTimeValue($convertTimeZone);
	}

	/**
	 * Function changes the time format to the database format without changing the time zone.
	 *
	 * @param string $time
	 *
	 * @return string
	 */
	public static function sanitizeDbFormat(string $time)
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
