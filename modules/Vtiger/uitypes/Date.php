<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Date_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Date.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if (empty($value)) {
			return $value;
		} else {
			$dateValue = self::getDisplayDateValue($value);
		}

		if ($dateValue == '--') {
			return "";
		} else {
			return $dateValue;
		}
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			return self::getDBInsertedValue($value);
		} else {
			return '';
		}
	}

	/**
	 * Function converts the date to database format
	 * @param string $value
	 * @return string
	 */
	public static function getDBInsertedValue($value)
	{
		return DateTimeField::convertToDBFormat($value);
	}

	/**
	 * Function to get the display value in edit view
	 * @param $value
	 * @return converted value
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		if (empty($value) || $value === ' ') {
			$value = trim($value);
			$fieldInstance = $this->get('field')->getWebserviceFieldObject();
			$moduleName = $this->get('field')->getModule()->getName();
			$fieldName = $fieldInstance->getFieldName();

			//Restricted Fields for to show Default Value
			if (($fieldName === 'birthday' && $moduleName === 'Contacts') || $moduleName === 'Products') {
				return $value;
			}

			//Special Condition for field 'support_end_date' in Contacts Module
			if ($fieldName === 'support_end_date' && $moduleName === 'Contacts') {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+1 year")));
			} elseif ($fieldName === 'support_start_date' && $moduleName === 'Contacts') {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d'));
			}
		} else {
			$value = DateTimeField::convertToUserFormat($value);
		}
		return $value;
	}

	/**
	 * Function to get Date value for Display
	 * @param <type> $date
	 * @return string
	 */
	public static function getDisplayDateValue($date)
	{
		$date = new DateTimeField($date);
		return $date->getDisplayDate();
	}

	/**
	 * Function to get DateTime value for Display
	 * @param <type> $dateTime
	 * @return string
	 */
	public static function getDisplayDateTimeValue($dateTime)
	{
		$date = new DateTimeField($dateTime);
		return $date->getDisplayDateTimeValue();
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/DateFieldSearchView.tpl';
	}
}
