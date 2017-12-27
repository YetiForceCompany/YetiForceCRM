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
	 * Function returns the date in user specified format.
	 * @param string $value Date time
	 * @return string
	 */
	public static function formatToDisplay($value)
	{
		if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
			return '';
		}
		return (new \DateTimeField($value))->getDisplayDateTimeValue();
	}

	/**
	 * Function to get date and time value for db format
	 * @param string $value Date time
	 * @return string
	 */
	public static function formatToDb($value)
	{
		return (new \DateTimeField($value))->getDBInsertDateTimeValue();
	}

	/**
	 * The function returns the date according to the user's settings
	 * @param string $dateTime Date time
	 * @return string
	 */
	public static function formatToViewDate($dateTime)
	{
		switch (\App\User::getCurrentUserModel()->getDetail('view_date_format')) {
			case 'PLL_FULL':
				return '<span title="' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '">' . static::formatToDisplay($dateTime) . '</span>';
			case 'PLL_ELAPSED':
				return '<span title="' . static::formatToDay($dateTime) . '">' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '</span>';
			case 'PLL_FULL_AND_DAY':
				return '<span title="' . \Vtiger_Util_Helper::formatDateDiffInStrings($dateTime) . '">' . static::formatToDay($dateTime) . '</span>';
		}
		return '-';
	}

	/**
	 * Function to parse dateTime into days
	 * @param string $dateTime Date time
	 * @param bool $allday
	 * @return string
	 */
	public static function formatToDay($dateTime, $allday = false)
	{
		$dateTimeInUserFormat = explode(' ', static::formatToDisplay($dateTime));
		if (count($dateTimeInUserFormat) === 3) {
			list($dateInUserFormat, $timeInUserFormat, $meridiem) = $dateTimeInUserFormat;
		} else {
			list($dateInUserFormat, $timeInUserFormat) = $dateTimeInUserFormat;
			$meridiem = '';
		}
		$formatedDate = $dateInUserFormat;
		$dateDay = \App\Language::translate(\DateTimeField::getDayFromDate($dateTime), 'Calendar');
		if (!$allday) {
			$timeInUserFormat = explode(':', $timeInUserFormat);
			if (count($timeInUserFormat) === 3) {
				list($hours, $minutes, $seconds) = $timeInUserFormat;
			} else {
				list($hours, $minutes) = $timeInUserFormat;
				$seconds = '';
			}
			$displayTime = $hours . ':' . $minutes . ' ' . $meridiem;
			$formatedDate .= ' ' . \App\Language::translate('LBL_AT') . ' ' . $displayTime;
		}
		$formatedDate .= " ($dateDay)";
		return $formatedDate;
	}
}
