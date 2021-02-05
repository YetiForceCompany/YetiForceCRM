<?php
/**
 * Tools for RangeTime class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

/**
 * DateTime class.
 */
class RangeTime
{
	/**
	 * @var array Interval labels
	 */
	const DIFF_INTERVAL_LABELS = [
		'y' => ['short' => 'LBL_Y', 'plural' => 'LBL_YEARS', 'singular' => 'LBL_YEAR'],
		'a' => ['short' => 'LBL_D', 'plural' => 'LBL_DAYS', 'singular' => 'LBL_DAY'],
		'h' => ['short' => 'LBL_H', 'plural' => 'LBL_HOURS', 'singular' => 'LBL_HOUR'],
		'i' => ['short' => 'LBL_M', 'plural' => 'LBL_MINUTES', 'singular' => 'LBL_MINUTE'],
		's' => ['short' => 'LBL_S', 'plural' => 'LBL_SECONDS', 'singular' => 'LBL_SECOND']
	];

	/**
	 * Get the current interval in a human readable format.
	 *
	 * @param int|float $timePeriod Elapse time
	 * @param string    $formatIn   y,h,m,s
	 * @param string    $formatOut  y,h,m,s
	 * @param bool      $short
	 * @param mixed     $interval
	 *
	 * @return string
	 */
	public static function displayElapseTime($interval, string $formatIn = 'i', string $formatOut = 'i', bool $short = true): string
	{
		$dateFormat = [];
		$multiplier = 1;
		switch ($formatIn) {
			case 'y':
				$multiplier = 60 * 24 * 365;
				break;
			case 'h':
				$multiplier = 60 * 60;
				break;
			case 'i':
				$multiplier = 60;
				break;
			default:
		}
		$seconds = (int) ((float) $interval * $multiplier);
		if ($seconds) {
			$dtF = new \DateTime('@0');
			$dtT = new \DateTime("@{$seconds}");
			$dateInterval = $dtF->diff($dtT);
			foreach (self::getIntervalPart($dateInterval) as [$val, $part]) {
				if ($val) {
					$dateFormat[] = $short ? $val . \App\Language::translate(self::DIFF_INTERVAL_LABELS[$part]['short']) : "{$val} " . \App\Language::translate(self::DIFF_INTERVAL_LABELS[$part][(1 === $val ? 'singular' : 'plural')]);
				}
			}
		} elseif ($formatOut) {
			$dateFormat[] = $short ? $seconds . \App\Language::translate(self::DIFF_INTERVAL_LABELS[$formatOut]['short']) : "{$seconds} " . \App\Language::translate(self::DIFF_INTERVAL_LABELS[$formatOut]['plural']);
		}

		return implode(' ', $dateFormat);
	}

	/**
	 * Get data interval part.
	 *
	 * @param \DateInterval $dateInterval
	 *
	 * @return Generator
	 */
	public static function getIntervalPart(\DateInterval $dateInterval)
	{
		foreach (['a', 'h', 'i', 's'] as $part) {
			$val = (int) $dateInterval->format("%{$part}");
			if ('a' === $part && $val > 365) {
				$years = (int) floor($val / 365);
				$val = (int) static::myBcmod(($val), 365);
				yield [$years, 'y'];
			}
			yield [$val, $part];
		}
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
				$full[] = 1 === $years ? $years . ' ' . \App\Language::translate('LBL_YEAR') : $years . ' ' . \App\Language::translate('LBL_YEARS');
			}
		}
		if ('y' === $unit || 'd' === $unit) {
			$days = static::myBcmod(($value), (60 * 24 * 365));
			$days = ($days) / (24 * 60);
			$days = floor($days);
			if (!empty($days)) {
				$short[] = $days . \App\Language::translate('LBL_D');
				$full[] = 1 === $days ? $days . ' ' . \App\Language::translate('LBL_DAY') : $days . ' ' . \App\Language::translate('LBL_DAYS');
			}
			$hours = static::myBcmod(($value), (24 * 60));
		}
		$hours = ($hours) / (60);
		$hours = floor($hours);
		if (!empty($hours)) {
			$short[] = $hours . \App\Language::translate('LBL_H');
			$full[] = 1 === $hours ? $hours . ' ' . \App\Language::translate('LBL_HOUR') : $hours . ' ' . \App\Language::translate('LBL_HOURS');
		}
		$minutes = static::myBcmod(($value), (60));
		$minutes = floor($minutes);
		if (!empty($minutes) || $showEmptyValue) {
			$short[] = $minutes . \App\Language::translate('LBL_M');
			$full[] = 1 === $minutes ? $minutes . ' ' . \App\Language::translate('LBL_MINUTE') : $minutes . ' ' . \App\Language::translate('LBL_MINUTES');
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
		} while (\strlen($x));
		return (int) $mod;
	}
}
