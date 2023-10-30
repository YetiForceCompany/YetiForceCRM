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

class Vtiger_Picklist_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validateValue($value)
	{
		if ($this->getFieldModel()->isRoleBased()) {
			$picklistValues = \App\Fields\Picklist::getRoleBasedValues($this->getFieldModel()->getName(), \App\User::getCurrentUserModel()->getRole());
		} else {
			$picklistValues = App\Fields\Picklist::getValuesName($this->getFieldModel()->getName());
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
		if (null === $value || '' === $value) {
			return '';
		}
		$moduleName = $this->getFieldModel()->getModuleName();
		$displayValue = \App\Language::translate($value, $moduleName);
		if ($rawText) {
			return $displayValue;
		}
		if (\is_int($length)) {
			$displayValue = \App\TextUtils::textTruncate($displayValue, $length);
		}
		$fieldName = App\Colors::sanitizeValue($this->getFieldModel()->getName());
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

		return "<span class=\"picklistValue picklistLb_{$moduleName}_{$fieldName}_{$value}\">{$icon}{$displayValue}</span>";
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $value ?? '';
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		$value = trim($value);
		if ('' === $value) {
			return $defaultValue ?? '';
		}
		$picklistValues = array_keys($this->getFieldModel()->getPicklistValues());
		$picklistValueInLowerCase = mb_strtolower(htmlentities($value, ENT_QUOTES, \App\Config::main('default_charset', 'UTF-8')));
		$allPicklistValuesInLowerCase = array_map('mb_strtolower', $picklistValues);
		$picklistDetails = array_combine($allPicklistValuesInLowerCase, $picklistValues);
		if (\in_array($picklistValueInLowerCase, $allPicklistValuesInLowerCase)) {
			$value = $picklistDetails[$picklistValueInLowerCase];
		} else {
			if (\App\Config::module('Import', 'ADD_PICKLIST_VALUE')) {
				$this->getFieldModel()->setPicklistValues([$value]);
			}
		}
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
		return !\App\Fields\Picklist::isDependentField($this->getFieldModel()->getModuleName(), $this->getFieldModel()->getName());
	}

	/** {@inheritdoc} */
	public function getHeaderTypes()
	{
		return ['LBL_HEADER_TYPE_VALUE' => 'value', 'LBL_HEADER_TYPE_HIGHLIGHTS' => 'highlights', 'LBL_HEADER_TYPE_PROGRESS' => 'progress'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getRecordOperators(): array
	{
		if ($this->getFieldModel() && ($this->getFieldModel()->getFieldParams()['isProcessStatusField'] ?? false)) {
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
		$moduleName = $fieldModel->getModuleName();
		if (!($fieldValue = $recordModel->get($fieldName))) {
			return [];
		}
		$isEditable = $recordModel->isEditable() && App\Config::module($moduleName, 'headerProgressIsEditable', true) && $fieldModel->isAjaxEditable() && !$fieldModel->isEditableReadOnly();
		if ($isEditable) {
			$picklistOfField = $fieldModel->getPicklistValues();
		}
		$picklistDependency = \App\Fields\Picklist::getDependencyForModule($moduleName);
		$closeStates = \App\RecordStatus::getLockStatus($moduleName, false);
		$values = [];
		foreach (\App\Fields\Picklist::getValues($fieldModel->getName()) as $value) {
			$isEditableValue = true;
			if ($isEditable && isset($picklistDependency['conditions'][$fieldName][$value['picklistValue']])) {
				$isEditableValue = \App\Condition::checkConditions($picklistDependency['conditions'][$fieldName][$value['picklistValue']], $recordModel);
			}
			$values[$value[$fieldName]] = [
				'label' => \App\Language::translate($value['picklistValue'], $moduleName),
				'isActive' => $value['picklistValue'] === $fieldValue,
				'isLocked' => isset($value['picklist_valueid']) && isset($closeStates[$value['picklist_valueid']]),
				'isEditable' => $isEditable && $isEditableValue && $value['picklistValue'] !== $fieldValue && isset($picklistOfField[$value['picklistValue']]),
				'description' => $value['description'] ?? null,
				'color' => $value['color'] ?? null,
			];
		}
		return $values;
	}
}
