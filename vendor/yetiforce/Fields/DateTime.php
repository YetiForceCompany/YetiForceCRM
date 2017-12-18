<?php
/**
 * Tools for datetime class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
namespace App\Fields;

/**
 * DateTime class
 */
class DateTime
{

	/**
	 * The function returns the date according to the user's settings
	 * @param string $dateTime
	 * @return string
	 */
	public static function getViewDateFormat($dateTime)
	{
		switch (\App\User::getCurrentUserModel()->getDetail('view_date_format')) {
			case 'PLL_FULL':
				return '<span title="' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '">' . \Vtiger_Datetime_UIType::getDisplayDateTimeValue($dateTime) . '</span>';
			case 'PLL_ELAPSED':
				return '<span title="' . \Vtiger_Datetime_UIType::getDisplayDateTimeValue($dateTime) . '">' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '</span>';
		}
		return '-';
	}
}
