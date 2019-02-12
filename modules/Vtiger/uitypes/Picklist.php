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

class Vtiger_Picklist_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value === '') {
			return '';
		}
		$moduleName = $this->getFieldModel()->getModuleName();
		$dispalyValue = \App\Language::translate($value, $moduleName);
		if (is_int($length)) {
			$dispalyValue = \App\TextParser::textTruncate($dispalyValue, $length);
		}
		if ($rawText) {
			return $dispalyValue;
		}
		$fieldName = App\Colors::sanitizeValue($this->getFieldModel()->getFieldName());
		$value = App\Colors::sanitizeValue($value);

		return "<span class=\"picklistValue picklistLb_{$moduleName}_{$fieldName}_{$value}\">$dispalyValue</span>";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/PickList.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Picklist.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEditable()
	{
		$moduleName = $this->getFieldModel()->getModuleName();
		if (!isset(\App\Fields\Picklist::$picklistDependencyFields[$moduleName])) {
			\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName);
		}
		return !isset(\App\Fields\Picklist::$picklistDependencyFields[$moduleName][$this->getFieldModel()->getFieldName()]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderTypes()
	{
		return ['LBL_HEADER_TYPE_VALUE' => 'value', 'LBL_HEADER_TYPE_PROGRESS' => 'progress'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'y', 'ny'];
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
