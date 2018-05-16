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

class Vtiger_Date_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			return self::getDBInsertedValue($value);
		}

		return '';
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
			list($y, $m, $d) = App\Fields\Date::explode($value, App\User::getCurrentUserModel()->getDetail('date_format'));
		} else {
			list($y, $m, $d) = explode('-', $value);
		}
		if (!checkdate($m, $d, $y)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		} else {
			$dateValue = App\Fields\Date::formatToDisplay($value);
		}
		if ($dateValue === '--') {
			return '';
		} else {
			return $dateValue;
		}
	}

	/**
	 * Function converts the date to database format.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function getDBInsertedValue($value)
	{
		return DateTimeField::convertToDBFormat($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value) || $value === ' ') {
			$value = trim($value);
			$fieldName = $this->getFieldModel()->getFieldName();
			$moduleName = $this->getFieldModel()->getModule()->getName();
			//Restricted Fields for to show Default Value
			if (($fieldName === 'birthday' && $moduleName === 'Contacts') || $moduleName === 'Products') {
				return \App\Purifier::encodeHtml($value);
			}

			//Special Condition for field 'support_end_date' in Contacts Module
			if ($fieldName === 'support_end_date' && $moduleName === 'Contacts') {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d', strtotime('+1 year')));
			} elseif ($fieldName === 'support_start_date' && $moduleName === 'Contacts') {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d'));
			}
		} else {
			$value = DateTimeField::convertToUserFormat($value);
		}

		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return null;
	}
}
