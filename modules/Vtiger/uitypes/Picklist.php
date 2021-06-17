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
	/** {@inheritdoc} */
	public function validateValue($value)
	{
		if ($this->getFieldModel()->isRoleBased()) {
			$picklistValues = \App\Fields\Picklist::getRoleBasedValues($this->getFieldModel()->getFieldName(), \App\User::getCurrentUserModel()->getRole());
		} else {
			$picklistValues = App\Fields\Picklist::getValuesName($this->getFieldModel()->getFieldName());
		}
		return '' === $value || \in_array($value, $picklistValues);
	}

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
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ('' === $value) {
			return '';
		}
		$moduleName = $this->getFieldModel()->getModuleName();
		$displayValue = \App\Language::translate($value, $moduleName);
		if ($rawText) {
			return $displayValue;
		}
		if (\is_int($length)) {
			$displayValue = \App\TextParser::textTruncate($displayValue, $length);
		}
		$fieldName = App\Colors::sanitizeValue($this->getFieldModel()->getName());
		$value = App\Colors::sanitizeValue($value);
		return "<span class=\"picklistValue picklistLb_{$moduleName}_{$fieldName}_{$value}\">{$displayValue}</span>";
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $value;
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/PickList.tpl';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return !\App\Fields\Picklist::isDependentField($this->getFieldModel()->getModuleName(), $this->getFieldModel()->getFieldName());
	}

	/** {@inheritdoc} */
	public function getHeaderTypes()
	{
		return ['LBL_HEADER_TYPE_VALUE' => 'value', 'LBL_HEADER_TYPE_HIGHLIGHTS' => 'highlights', 'LBL_HEADER_TYPE_PROGRESS' => 'progress'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function getRecordOperators(): array
	{
		if ($this->getFieldModel()->getFieldParams()['isProcessStatusField'] ?? false) {
			return array_merge($this->getQueryOperators(), ['hs', 'ro', 'rc']);
		}
		return parent::getRecordOperators();
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

	/**
	 * Get progress header type.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getProgressHeader(Vtiger_Record_Model $recordModel): array
	{
		$fieldModel = $this->getFieldModel();
		$fieldName = $fieldModel->getName();
		$moduleName = $this->getFieldModel()->getModuleName();
		if (!($fieldValue = $recordModel->get($fieldName))) {
			return [];
		}
		$isEditable = $recordModel->isEditable() && App\Config::module($moduleName, 'headerProgressIsEditable', true) && $fieldModel->isAjaxEditable() && !$fieldModel->isEditableReadOnly();
		if ($isEditable) {
			$picklistOfField = $fieldModel->getPicklistValues();
		}
		$picklistDependency = \App\Fields\Picklist::getPicklistDependencyDatasource($moduleName);
		$dependentSourceField = \App\Fields\Picklist::getDependentSourceField($moduleName, $fieldName);
		$closeStates = \App\RecordStatus::getLockStatus($moduleName, false);
		$values = [];
		foreach (\App\Fields\Picklist::getValues($fieldModel->getName()) as $value) {
			if ($dependentSourceField && isset($picklistDependency[$dependentSourceField][$recordModel->get($dependentSourceField)][$fieldName]) && !\in_array($value['picklistValue'], $picklistDependency[$dependentSourceField][$recordModel->get($dependentSourceField)][$fieldName])) {
				continue;
			}
			$values[$value[$fieldName]] = [
				'label' => \App\Language::translate($value['picklistValue'], $moduleName),
				'isActive' => $value['picklistValue'] === $fieldValue,
				'isLocked' => isset($closeStates[$value['picklist_valueid']]),
				'isEditable' => $isEditable && $value['picklistValue'] !== $fieldValue && isset($picklistOfField[$value['picklistValue']]),
				'description' => $value['description'] ?? null,
				'color' => $value['color'] ?? null,
			];
		}
		return $values;
	}
}
