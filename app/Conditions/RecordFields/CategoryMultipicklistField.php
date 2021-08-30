<?php

namespace App\Conditions\RecordFields;

/**
 * Category multipicklist condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class CategoryMultipicklistField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		$check = false;
		foreach ($this->getValue() as $valueRecord) {
			if (\array_intersect(explode('##', $this->value), explode(',', $valueRecord))) {
				$check = true;
			}
		}
		return $check;
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
		$check = false;
		if (\array_intersect(explode('##', $this->value), $this->getValue())) {
			$check = true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		$check = false;
		if (!\array_intersect(explode('##', $this->value), $this->getValue())) {
			$check = true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorCh(): bool
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode('##', $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->recordModel->getModuleName()));
		$check = false;
		if (\array_intersect(explode('##', $this->value), explode('##', $fieldValue))) {
			$check = true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorKh(): bool
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode('##', $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->recordModel->getModuleName()));
		$check = false;
		if (!\array_intersect(explode('##', $this->value), explode('##', $fieldValue))) {
			$check = true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function getValue(): array
	{
		$valueArray = explode(',', trim(parent::getValue(), ','));
		if (\in_array($this->operator, ['e', 'n'])) {
			foreach ($this->getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value, ',');
			}
		}
		return $valueArray;
	}

	/**
	 * Function to get combinations of string from Array.
	 *
	 * @param array  $array
	 * @param string $tempString
	 *
	 * @return array
	 */
	public function getCombinations(array $array, string $tempString = ''): array
	{
		$countArray = \count($array);
		$result = '';
		for ($i = 0; $i < $countArray; ++$i) {
			$splicedArray = $array;
			$element = array_splice($splicedArray, $i, 1);
			if (\count($splicedArray) > 0) {
				if (!\is_array($result)) {
					$result = [];
				}
				$result = array_merge($result, $this->getCombinations($splicedArray, $tempString . ',' . $element[0]));
			} else {
				return [$tempString . ',' . $element[0]];
			}
		}
		return $result;
	}
}
