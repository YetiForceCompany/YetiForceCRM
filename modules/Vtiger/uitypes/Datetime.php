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

/**
 * Uitype: 80
 */
class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		$arrayDateTime = explode(' ', $value, 2);
		$cnt = count($arrayDateTime);
		if ($cnt === 1) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
		} elseif ($cnt === 2) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
			(new Vtiger_Time_UIType())->validate($arrayDateTime[1], $isUserFormat); //Time
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		switch ($this->getFieldModel()->getUIType()) {
			case 80:
				return $rawText ? Vtiger_Util_Helper::formatDateDiffInStrings($value) : '<span title="' . self::getDisplayDateTimeValue($value) . '">' . Vtiger_Util_Helper::formatDateDiffInStrings($value) . '</span>';
			default:
				return self::getDisplayDateTimeValue($value);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		switch ($this->getFieldModel()->getUIType()) {
			case 80:
				return $rawText ? \App\Fields\DateTime::getViewDateFormat($value) : '<span title="' . self::getDisplayDateTimeValue($value) . '">' . \App\Fields\DateTime::getViewDateFormat($value) . '</span>';
		}
		return \vtlib\Functions::textLength($this->getDisplayValue($value, $record, $recordModel, $rawText), $this->getFieldModel()->get('maxlengthtext'));
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getDisplayDateTimeValue($date)
	{
		if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
			return '';
		}
		$date = new DateTimeField($date);
		return $date->getDisplayDateTimeValue();
	}

	/**
	 * Function to get Date and Time value for Display
	 * @param <type> $date
	 * @return string
	 */
	public static function getDBDateTimeValue($date)
	{
		$date = new DateTimeField($date);
		return $date->getDBInsertDateTimeValue();
	}

	/**
	 * Function to get the datetime value in user preferred hour format
	 * @param <type> $dateTime
	 * @return string date and time with hour format
	 */
	public static function getDateTimeValue($dateTime)
	{
		return Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($dateTime);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/DateTime.tpl';
	}
}
