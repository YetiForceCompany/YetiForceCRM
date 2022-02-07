<?php

namespace App\Conditions\QueryFields;

/**
 * CategoryMultipicklist Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class CategoryMultipicklistField extends TaxesField
{
	/**
	 * Condition separator.
	 *
	 * @var string
	 */
	protected $conditionSeparator = '##';

	/**
	 * Function to get combinations of string from Array.
	 *
	 * @param array  $array
	 * @param string $tempString
	 *
	 * @return array
	 */
	public function getCombinations($array, $tempString = '')
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
				$result = array_merge($result, $this->getCombinations($splicedArray, $tempString . $this->separator . $element[0]));
			} else {
				return [$tempString . $this->separator . $element[0] . $this->separator];
			}
		}
		return $result;
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$value = $this->value;
		$valueArray = explode($this->conditionSeparator, $value);
		if (\in_array($this->operator, ['e', 'n'])) {
			foreach ($this->getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value);
			}
		}
		return $valueArray;
	}

	/**
	 * Contains hierarchy operator.
	 *
	 * @return array
	 */
	public function operatorCh()
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode($this->conditionSeparator, $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->getModuleName()));
		$condition = ['or'];
		foreach (explode($this->conditionSeparator, $fieldValue) as $value) {
			array_push($condition, [$this->getColumnName() => $value], ['or like', $this->getColumnName(),
				[
					"%{$this->separator}{$value}{$this->separator}%",
					"{$value}{$this->separator}%",
					"%{$this->separator}{$value}",
				], false,
			]);
		}
		return $condition;
	}

	/**
	 * Does not contain hierarchy operator.
	 *
	 * @return array
	 */
	public function operatorKh()
	{
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren(implode($this->conditionSeparator, $this->getValue()), $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->getModuleName()));
		$condition = ['and'];
		foreach (explode($this->conditionSeparator, $fieldValue) as $value) {
			array_push($condition, ['<>', $this->getColumnName(), $value], ['not', ['or like', $this->getColumnName(),
				[
					"%{$this->separator}{$value}{$this->separator}%",
					"{$value}{$this->separator}%",
					"%{$this->separator}{$value}",
				], false,
			]]);
		}
		return $condition;
	}
}
