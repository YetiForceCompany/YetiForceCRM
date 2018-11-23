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
	 *
	 * @return string
	 */
	public static function formatToDisplay(?string $value): string
	{
		$valueParts = explode('.', $value, 2);
		$valueToDisplay = Integer::formatToDisplay($valueParts[0]);
		if (isset($valueParts[1])) {
			$userModel = \App\User::getCurrentUserModel();
			$decimalSeperator = $userModel->getDetail('currency_decimal_separator');
			if ($userModel->getDetail('truncate_trailing_zeros')) {
				for ($i = strlen($valueParts[1]) -1; $i >= 0; $i--) {
					if ($valueParts[1][$i] !== '0') {
						break;
					}
				}
				if ($i !== -1) {
					$valueToDisplay .= $decimalSeperator . substr($valueParts[1], 0, $i + 1);
				}
			} else {
				$valueToDisplay .= $decimalSeperator . $valueParts[1];
			}
		}
		return $valueToDisplay;
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
