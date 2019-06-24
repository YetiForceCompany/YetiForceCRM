<?php
/**
 * Tools for time class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Time class.
 */
class TimePeriod
{
	/**
	 * Unit labels.
	 *
	 * @var array
	 */
	public static $unitLabels = [
		'd' => 'LBL_DAYS',
		'H' => 'LBL_HOURS',
		'i' => 'LBL_MINUTES',
	];

	/**
	 * Convert to minutes.
	 *
	 * @param string $value
	 *
	 * @return int
	 */
	public static function convertToMinutes(string $value): int
	{
		[$number,$timePeriod] = explode(':', $value);
		$multiplier = 1;
		switch ($timePeriod) {
			case 'H':
				$multiplier = 60;
				break;
			case 'd':
				$multiplier = 60 * 24;
				break;
		}
		return $number * $multiplier;
	}

	/**
	 * Get time period label.
	 *
	 * @param string $value
	 */
	public static function getLabel(string $value)
	{
		$time = explode(':', $value);
		return $time[0] . ' ' . \App\Language::translate(static::$unitLabels[$time[1]]);
	}
}
