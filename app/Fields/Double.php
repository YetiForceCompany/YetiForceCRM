<?php
/**
 * Tools for Double class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

/**
 * Double class.
 */
class Double
{
	/** @var int User format without rounding */
	public const FORMAT_USER_WITHOUT_ROUNDING = 0;
	/** @var int Rounds num to specified precision */
	public const FORMAT_ROUND = 1;
	/** @var int Truncate trailing zeros */
	public const FORMAT_TRUNCATE_TRAILING_ZEROS = 2;
	/** @var int Show digits up to precision */
	public const FORMAT_DIGITS_UP_TO_PRECISION = 4;

	/**
	 * Function to truncate zeros.
	 *
	 * @param string $value
	 * @param int    $precision
	 *
	 * @return string
	 */
	public static function truncateZeros(string $value, int $precision = 0)
	{
		$seperator = \App\User::getCurrentUserModel()->getDetail('currency_decimal_separator');
		if (false === strpos($value, $seperator)) {
			return $value;
		}
		for ($i = \strlen($value) - 1; $i >= 0; --$i) {
			if ($value[$i] === $seperator) {
				--$i;
				break;
			}
			if (isset($value[$i - $precision]) && $value[$i - $precision] === $seperator) {
				break;
			}
			if ('0' !== $value[$i]) {
				break;
			}
		}
		if (-1 !== $i) {
			$value = substr($value, 0, $i + 1);
		}

		return $value;
	}

	/**
	 * Function to display number in user format.
	 *
	 * @param string|null $value
	 * @param int         $fix   A bitmask of one or more of the mode flags
	 *
	 * @return string
	 */
	public static function formatToDisplay(?string $value, $fix = self::FORMAT_ROUND): string
	{
		if (empty($value)) {
			$value = 0;
		}
		$userModel = \App\User::getCurrentUserModel();
		if ($fix & self::FORMAT_ROUND) {
			$value = number_format((float) $value, $userModel->getDetail('no_of_currency_decimals'), '.', '');
		}
		[$integer, $decimal] = array_pad(explode('.', $value, 2), 2, false);

		$display = Integer::formatToDisplay($integer);
		$decimalSeperator = $userModel->getDetail('currency_decimal_separator');
		if ($userModel->getDetail('truncate_trailing_zeros')) {
			$display = static::truncateZeros($display . $decimalSeperator . $decimal);
		} elseif ($fix & self::FORMAT_TRUNCATE_TRAILING_ZEROS) {
			$display = static::truncateZeros($display . $decimalSeperator . $decimal, ($fix & self::FORMAT_DIGITS_UP_TO_PRECISION) ? $userModel->getDetail('no_of_currency_decimals') : 0);
		} elseif ($decimal) {
			$display .= $decimalSeperator . $decimal;
		}

		return $display;
	}

	/**
	 * Convert number to format for database.
	 *
	 * @param string|null $value
	 *
	 * @return float
	 */
	public static function formatToDb(?string $value): float
	{
		if (empty($value)) {
			return 0;
		}
		$userModel = \App\User::getCurrentUserModel();
		$decimalSeperator = $userModel->getDetail('currency_decimal_separator');
		$groupSeperator = $userModel->getDetail('currency_grouping_separator');
		$value = str_replace($groupSeperator, '', $value);
		$value = str_replace($decimalSeperator, '.', $value);
		return (float) preg_replace('/[^0-9\.-]/', '', $value);
	}
}
