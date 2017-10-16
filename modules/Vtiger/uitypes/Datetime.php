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

class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{

	/**
	 * Verification of data
	 * @param string $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\SaveRecord
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		$arrayDateTime = explode(' ', $value, 2);
		$cnt = count($arrayDateTime);
		if (!$isUserFormat && $cnt !== 2) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		if ($cnt === 1) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
		} elseif ($cnt === 2) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
			(new Vtiger_Time_UIType())->validate($arrayDateTime[1], $isUserFormat); //Time
		}
		$this->validate = true;
	}

	/**
	 * Function to get the display value, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @param int $record
	 * @param type $recordModel
	 * @param Vtiger_Record_Model $rawText
	 * @return mixed
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		return self::getDisplayDateTimeValue($value);
	}

	/**
	 * Function to get Date and Time value for Display
	 * @param string $date
	 * @return string
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
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/DateTime.tpl';
	}
}
