<?php

namespace App\QueryField;

/**
 * Multipicklist Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class MultipicklistField extends BaseField
{
	/**
	 * Separator.
	 *
	 * @var string
	 */
	protected $separator = ' |##| ';

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
		$countArray = count($array);
		for ($i = 0; $i < $countArray; ++$i) {
			$splicedArray = $array;
			$element = array_splice($splicedArray, $i, 1); // removes and returns the i'th element
			if (count($splicedArray) > 0) {
				if (!is_array($result)) {
					$result = [];
				}
				$result = array_merge($result, $this->getCombinations($splicedArray, $tempString . $this->separator . $element[0]));
			} else {
				return [$tempString . $this->separator . $element[0]];
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
		$valueArray = explode('##', $value);
		if (in_array($this->operator, ['e', 'n'])) {
			foreach ($this->getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value, $this->separator);
			}
		}
		return $valueArray;
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		return ['not', [$this->getColumnName() => $this->getValue()]];
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		$condition = ['or'];
		foreach ($this->getValue() as $value) {
			array_push($condition, [$this->getColumnName() => $value], ['or like', $this->getColumnName(),
				[
					"%{$this->separator}{$value}{$this->separator}%",
					"{$value}{$this->separator}%",
					"%{$this->separator}{$value}"
				], false
			]);
		}
		return $condition;
	}

	/**
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK()
	{
		$condition = ['and'];
		foreach ($this->getValue() as $value) {
			array_push($condition, ['<>', $this->getColumnName(), $value], ['not', ['or like', $this->getColumnName(),
				[
					"%{$this->separator}{$value}{$this->separator}%",
					"{$value}{$this->separator}%",
					"%{$this->separator}{$value}"
				], false
			]]);
		}
		return $condition;
	}
}
