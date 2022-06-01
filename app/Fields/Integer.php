<?php
/**
 * Tools for Integer class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Fields;

/**
 * Integer class.
 */
class Integer
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
		if (empty($value)) {
			return '0';
		}
		$userModel = \App\User::getCurrentUserModel();
		$groupSeperator = $userModel->getDetail('currency_grouping_separator');
		$groupPatern = $userModel->getDetail('currency_grouping_pattern');
		if (($length = \App\TextUtils::getTextLength($value)) > 3) {
			switch ($groupPatern) {
				case '123,456,789':
					$value = preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/', "$1{$groupSeperator}", $value);
					break;
				case '123456,789':
					$value = substr($value, 0, $length - 3) . $groupSeperator . substr($value, $length - 3);
					break;
				case '12,34,56,789':
					$value = preg_replace('/(\d)(?=(\d\d)+(?!\d))/', "$1{$groupSeperator}", substr($value, 0, $length - 3)) . $groupSeperator . substr($value, $length - 3);
					break;
				default:

					break;
			}
		}
		return $value;
	}

	/**
	 * Convert number to format for database.
	 *
	 * @param string|null $value
	 *
	 * @return int
	 */
	public static function formatToDb(?string $value): int
	{
		if (empty($value)) {
			return 0;
		}
		$userModel = \App\User::getCurrentUserModel();
		$groupSeperator = $userModel->getDetail('currency_grouping_separator');
		$value = str_replace($groupSeperator, '', $value);
		return (int) preg_replace('/[^0-9\.-]/', '', $value);
	}
}
