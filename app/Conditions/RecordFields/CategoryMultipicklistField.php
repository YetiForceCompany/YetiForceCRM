<?php

namespace App\Conditions\RecordFields;

/**
 * Category multipicklist condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class CategoryMultipicklistField extends BaseField
{
	public $conditionSeparator = '##';

	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		return (bool) \array_intersect(explode($this->conditionSeparator, $this->value), $this->getValue());
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		$check = false;
		if (!$this->operatorE()) {
			return true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorC(): bool
	{
		return (bool) \array_intersect(explode($this->conditionSeparator, $this->value), $this->getValue());
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		return (bool) !\array_intersect(explode($this->conditionSeparator, $this->value), $this->getValue());
	}

	/**
	 * Contains hierarchy operator.
	 *
	 * @return array
	 */
	public function operatorCh(): bool
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode($this->conditionSeparator, $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->recordModel->getModuleName()));
		return (bool) \array_intersect(explode($this->conditionSeparator, $this->value), explode($this->conditionSeparator, $fieldValue));
	}

	/**
	 * Does not contain hierarchy operator.
	 *
	 * @return array
	 */
	public function operatorKh(): bool
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode($this->conditionSeparator, $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->recordModel->getModuleName()));
		return (bool) !\array_intersect(explode($this->conditionSeparator, $this->value), explode($this->conditionSeparator, $fieldValue));
	}

	/** {@inheritdoc} */
	public function getValue(): array
	{
		$value = parent::getValue();
		return $value ? explode(',', trim($value, ',')) : [];
	}
}
