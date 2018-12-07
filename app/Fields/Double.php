<?php
/**
 * Tools for Double class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Fields;

/**
 * Double class.
 */
class Double
{
	/**
	 * Function to display number in user format.
	 *
	 * @param string|null $value
	 * @param bool        $fixed
	 *
	 * @return string
	 */
	public static function formatToDisplay(?string $value, $fixed = true): string
	{
		if (empty($value)) {
			$value = 0;
		}
		$userModel = \App\User::getCurrentUserModel();
		if ($fixed) {
			$value = number_format($value, $userModel->getDetail('no_of_currency_decimals'), '.', '');
		}
		[$integer, $decimal] = explode('.', $value, 2);

		$display = Integer::formatToDisplay($integer);
		$decimalSeperator = $userModel->getDetail('currency_decimal_separator');
		if ($userModel->getDetail('truncate_trailing_zeros')) {
			for ($i = strlen($decimal) - 1; $i >= 0; $i--) {
				if ($decimal[$i] !== '0') {
					break;
				}
			}
			if ($i !== -1) {
				$display .= $decimalSeperator . substr($decimal, 0, $i + 1);
			}
		} else {
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
