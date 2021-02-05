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

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (\is_array($value)) {
			$value = implode(' |##| ', $value);
		}
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? implode('|', $value) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = explode(' |##| ', $value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		foreach ($value as $item) {
			if (!\is_string($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			if ($item != strip_tags($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return null;
		}
		$valueRaw = $valueHtml = '';
		$values = explode(' |##| ', $value);
		$trValueRaw = $trValue = [];
		$moduleName = $this->getFieldModel()->getModuleName();
		$fieldName = App\Colors::sanitizeValue($this->getFieldModel()->getFieldName());
		foreach ($values as $value) {
			$displayValue = App\Language::translate($value, $moduleName);
			$value = App\Colors::sanitizeValue($value);
			$trValueRaw[] = $displayValue;
			$trValue[] = "<span class=\"picklistValue picklistLb_{$moduleName}_{$fieldName}_{$value}\">$displayValue</span>";
		}
		if ($rawText) {
			$valueRaw = str_ireplace(' |##| ', ', ', implode(' |##| ', $trValueRaw));
			if (\is_int($length)) {
				$valueRaw = \App\TextParser::textTruncate($valueRaw, $length);
			}
		} else {
			$valueHtml = str_ireplace(' |##| ', ' ', implode(' |##| ', $trValue));
			if (\is_int($length)) {
				$valueHtml = \App\TextParser::htmlTruncate($valueHtml, $length);
			}
		}
		return $rawText ? $valueRaw : $valueHtml;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (\is_array($value)) {
			return $value;
		}
		return explode(' |##| ', \App\Purifier::encodeHtml($value));
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiPicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiPicklist.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'c', 'k', 'y', 'ny'];
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
		return 'ConditionBuilder/Picklist.tpl';
	}
}
