<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Time_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($this->getFieldModel()->get('uitype') === 14) {
			return self::getDBTimeFromUserValue($value);
		}

		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$value = static::getTimeValueWithSeconds($value);
		}
		$timeFormat = 'H:i:s';
		$d = DateTime::createFromFormat($timeFormat, $value);
		if (!($d && $d->format($timeFormat) === $value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = DateTimeField::convertToUserTimeZone(date('Y-m-d') . ' ' . $value)->format('H:i');
		if (App\User::getCurrentUserModel()->getDetail('hour_format') === '12') {
			return self::getTimeValueInAMorPM($value);
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Time.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Time.tpl';
	}

	public static function getDBTimeFromUserValue($value)
	{
		return DateTimeField::convertToDBTimeZone(date('Y-m-d') . ' ' . $value)->format('H:i:s');
	}

	/**
	 * Function to get display value for time.
	 *
	 * @param string time
	 *
	 * @return string time
	 */
	public static function getDisplayTimeValue($time)
	{
		$date = new DateTimeField($time);

		return $date->getDisplayTime();
	}

	/**
	 * Function to get time value in AM/PM format.
	 *
	 * @param string $time
	 *
	 * @return string time
	 */
	public static function getTimeValueInAMorPM($time)
	{
		if ($time) {
			list($hours, $minutes) = explode(':', $time);
			$format = \App\Language::translate('PM');

			if ($hours > 12) {
				$hours = (int) $hours - 12;
			} elseif ($hours < 12) {
				$format = \App\Language::translate('AM');
			}

			//If hours zero then we need to make it as 12 AM
			if ($hours == '00') {
				$hours = '12';
				$format = \App\Language::translate('AM');
			}

			return "$hours:$minutes $format";
		} else {
			return '';
		}
	}

	/**
	 * Function to get Time value with seconds.
	 *
	 * @param string $time
	 *
	 * @return string time
	 */
	public static function getTimeValueWithSeconds($time)
	{
		if ($time) {
			$timeDetails = array_pad(explode(' ', $time), 2, '');
			list($hours, $minutes, $seconds) = array_pad(explode(':', $timeDetails[0]), 3, 0);

			//If pm exists and if it not 12 then we need to make it to 24 hour format
			if ($timeDetails[1] === 'PM' && $hours !== '12') {
				$hours = $hours + 12;
			}

			if ($timeDetails[1] === 'AM' && $hours === '12') {
				$hours = '00';
			}

			if (empty($seconds)) {
				$seconds = '00';
			}

			return "$hours:$minutes:$seconds";
		} else {
			return '';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return null;
	}
}
