<?php
/**
 * Tools for RangeTime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * DateTime class.
 */
class RangeTime
{
	/**
	 * Format elapsed time to short display value.
	 *
	 * @param float    $decTime     time in decimal format 1.5 = 1h 30m
	 * @param string   $type        hour text format 'short' or 'full'
	 * @param bool|int $withSeconds if is provided as int then will be displayed
	 *
	 * @return string
	 */
	public static function formatHourToDisplay($decTime, $type = 'short', $withSeconds = false)
	{
		$short = 'short' === $type;

		$hour = floor($decTime);
		$min = floor(($decTime - $hour) * 60);
		$sec = round((($decTime - $hour) * 60 - $min) * 60);

		$result = '';
		if ($hour) {
			$result .= $short ? $hour . \App\Language::translate('LBL_H') : "{$hour} " . \App\Language::translate('LBL_HOURS');
		}
		if ($hour || $min) {
			$result .= $short ? " {$min}" . \App\Language::translate('LBL_M') : " {$min} " . \App\Language::translate('LBL_MINUTES');
		}
		if (false !== $withSeconds) {
			$result .= $short ? " {$sec}" . \App\Language::translate('LBL_S') : " {$sec} " . \App\Language::translate('LBL_SECONDS');
		}
		if (!$hour && !$min && false === $withSeconds) {
			$result = $short ? '0' . \App\Language::translate('LBL_M') : '0 ' . \App\Language::translate('LBL_MINUTES');
		}
		return trim($result);
	}

	/**
	 * Function returns the date in user specified format.
	 *
	 * @param string $value          Date time
	 * @param mixed  $mode
	 * @param mixed  $showEmptyValue
	 * @param mixed  $unit
	 *
	 * @return string
	 */
	public static function formatToRangeText($value, $mode = 'short', $showEmptyValue = true, $unit = 'h')
	{
		$full = $short = [];
		$hours = (int) $value;
		if ('y' === $unit) {
			$years = ((int) $value) / (60 * 24 * 365);
			$years = floor($years);
			if (!empty($years)) {
				$short[] = 1 === $years ? $years . \App\Language::translate('LBL_Y') : $years . \App\Language::translate('LBL_YRS');
				$full[] = 1 === $years ? $years . \App\Language::translate('LBL_YEAR') : $years . \App\Language::translate('LBL_YEARS');
			}
		}
		if ('y' === $unit || 'd' === $unit) {
			$days = static::myBcmod(($value), (60 * 24 * 365));
			$days = ($days) / (24 * 60);
			$days = floor($days);
			if (!empty($days)) {
				$short[] = $days . \App\Language::translate('LBL_D');
				$full[] = 1 === $days ? $days . \App\Language::translate('LBL_DAY') : $days . \App\Language::translate('LBL_DAYS');
			}
			$hours = static::myBcmod(($value), (24 * 60));
		}
		$hours = ($hours) / (60);
		$hours = floor($hours);
		if (!empty($hours)) {
			$short[] = $hours . \App\Language::translate('LBL_H');
			$full[] = 1 === $hours ? $hours . \App\Language::translate('LBL_HOUR') : $hours . \App\Language::translate('LBL_HOURS');
		}
		$minutes = static::myBcmod(($value), (60));
		$minutes = floor($minutes);
		if (!empty($value) || $showEmptyValue) {
			$short[] = $minutes . \App\Language::translate('LBL_M');
			$full[] = 1 === $minutes ? $minutes . \App\Language::translate('LBL_MINUTE') : $minutes . \App\Language::translate('LBL_MINUTES');
		}
		if ($mode && isset(${$mode})) {
			return implode(' ', ${$mode});
		}
		return [
			'short' => implode(' ', $short),
			'full' => implode(' ', $full),
		];
	}

	/**
	 * myBcmod - get modulus (substitute for bcmod)
	 * string my_bcmod ( string left_operand, int modulus )
	 * left_operand can be really big, but be carefull with modulus :(
	 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania.
	 *
	 * @param mixed $x
	 * @param mixed $y
	 * */
	private static function myBcmod($x, $y)
	{
		// how many numbers to take at once? carefull not to exceed (int)
		$take = 5;
		$mod = '';
		do {
			$a = (int) $mod . substr($x, 0, $take);
			$x = substr($x, $take);
			$mod = $a % $y;
		} while (strlen($x));
		return (int) $mod;
	}
}
