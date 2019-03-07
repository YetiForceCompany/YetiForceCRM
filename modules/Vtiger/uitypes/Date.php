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

	public function getDbConditionBuilderValue($value, string $operator)
	{
		if ($operator === 'bw') {
			$values = explode(',', $value);
			foreach ($values as &$val) {
				$this->validate($val, true);
				$val = $this->getDBValue($val);
			}
			return implode(',', $values);
		}
		$this->validate($value, true);
		return $this->getDBValue($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ($isUserFormat) {
			list($y, $m, $d) = App\Fields\Date::explode($value, App\User::getCurrentUserModel()->getDetail('date_format'));
		} else {
			list($y, $m, $d) = explode('-', $value);
		}
		if (!is_numeric($m) || !is_numeric($d) || !is_numeric($y) || !checkdate($m, $d, $y)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
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
	public function getDefaultEditTemplateName()
	{
		return 'Edit/DefaultField/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultValueFromRequest(\App\Request $request)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		$value = $request->getByType($fieldName, 'Text');
		if (!\App\TextParser::isVaribleToParse($value)) {
			$this->validate($value, true);
			$value = $this->getDBValue($value);
		}
		$this->getFieldModel()->set('defaultvalue', $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValue()
	{
		$defaultValue = $this->getFieldModel()->get('defaultvalue');
		if ($defaultValue && \App\TextParser::isVaribleToParse($defaultValue)) {
			$textParser = \App\TextParser::getInstance($this->getFieldModel()->getModuleName());
			$textParser->setContent($defaultValue)->parse();
			$defaultValue = $textParser->getContent();
		}
		return $defaultValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'bw', 'b', 'a', 'y', 'ny'] + array_keys(App\CustomView::DATE_FILTER_CONDITIONS);
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator = '')
	{
		if ($operator === 'bw') {
			return 'ConditionBuilder/DateRange.tpl';
		} else {
			return 'ConditionBuilder/Date.tpl';
		}
	}

	/**
	 * Generate valid sample value.
	 *
	 * @throws \Exception
	 *
	 * @return false|string
	 */
	public function getSampleValue()
	{
		return date('Y-m-d', random_int(strtotime('-1 month'), strtotime('+1 month')));
	}
}
