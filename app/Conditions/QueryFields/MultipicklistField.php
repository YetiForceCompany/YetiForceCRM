<?php

namespace App\Conditions\QueryFields;

/**
 * Multipicklist Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultipicklistField extends BaseField
{
	/** @var string Separator. */
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
		if (\in_array($this->operator, ['e', 'n'])) {
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
	public function operatorN(): array
	{
		return ['not', [$this->getColumnName() => $this->getValue()]];
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC(): array
	{
		$condition = ['or'];
		foreach ($this->getValue() as $value) {
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
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK(): array
	{
		$condition = ['and'];
		foreach ($this->getValue() as $value) {
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
