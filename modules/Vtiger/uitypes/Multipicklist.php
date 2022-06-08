<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		foreach ($value as $item) {
			if (!\is_string($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			if ($item != strip_tags($item)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
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
		$fieldName = App\Colors::sanitizeValue($this->getFieldModel()->getName());
		foreach ($values as $value) {
			$displayValue = App\Language::translate($value, $moduleName);
			if ($icon = \App\Fields\Picklist::getIcon($this->getFieldModel()->getName(), $value) ?: '') {
				['type' => $type, 'name' => $name] = $icon;
				$icon = '';
				if ('icon' === $type) {
					$icon = "<span class=\"{$name} mr-1\"></span>";
				} elseif ('image' === $type && ($src = \App\Layout\Media::getImageUrl($name))) {
					$icon = '<img class="icon-img--picklist mr-1" src="' . $src . '">';
				}
			}
			$value = App\Colors::sanitizeValue($value);
			$trValueRaw[] = $displayValue;
			$trValue[] = "<span class=\"picklistValue picklistLb_{$moduleName}_{$fieldName}_{$value}\">{$icon}{$displayValue}</span>";
		}
		if ($rawText) {
			$valueRaw = str_ireplace(' |##| ', ', ', implode(' |##| ', $trValueRaw));
			if (\is_int($length)) {
				$valueRaw = \App\TextUtils::textTruncate($valueRaw, $length);
			}
		} else {
			$valueHtml = str_ireplace(' |##| ', ' ', implode(' |##| ', $trValue));
			if (\is_int($length)) {
				$valueHtml = \App\TextUtils::htmlTruncateByWords($valueHtml, $length);
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

		return $value ? explode(' |##| ', \App\Purifier::encodeHtml($value)) : [];
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		$trimmedValue = trim($value);
		if ('' === $trimmedValue) {
			return $defaultValue ?? '';
		}
		$explodedValue = explode(' |##| ', $trimmedValue);
		foreach ($explodedValue as $key => $value) {
			$explodedValue[$key] = trim($value);
		}
		return implode(' |##| ', $explodedValue);
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
		return ['e', 'n', 'c', 'k', 'y', 'ny', 'ef', 'nf'];
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
