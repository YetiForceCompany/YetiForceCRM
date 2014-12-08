<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Datetime_UIType extends Vtiger_Date_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/DateTime.tpl';
	}
	
	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		return $dateValue = self::getDisplayDateTimeValue($value);
	}
	
	/**
	 * Function to get Date and Time value for Display
	 * @param <type> $date
	 * @return <String>
	 */
	public static function getDisplayDateTimeValue($date) {
		$date = new DateTimeField($date);
		return $date->getDisplayDateTimeValue();
	}

	/**
	 * Function to get Date and Time value for Display
	 * @param <type> $date
	 * @return <String>
	 */
	public static function getDBDateTimeValue($date) {
		$date = new DateTimeField($date);
		return $date->getDBInsertDateTimeValue();
	}
	
	/**
	 * Function to get the datetime value in user preferred hour format
	 * @param <type> $dateTime
	 * @return <String> date and time with hour format
	 */
	public static function getDateTimeValue($dateTime){
		return Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($dateTime);
	}
}