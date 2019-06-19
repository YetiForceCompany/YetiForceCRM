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
}
