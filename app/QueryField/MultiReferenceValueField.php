<?php

namespace App\QueryField;

/**
 * MultiReferenceValue Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class MultiReferenceValueField extends BaseField
{
	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	public function getValue()
	{
		$valueArray = explode('##', $this->value);
		foreach ($valueArray as $key => $value) {
			$valueArray[$key] = '|#|' . $value . '|#|';
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
		return ['or not like', $this->getColumnName(), $this->getValue()];
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
					'%' . $value . '%',
					'%' . $value,
					$value . '%',
				], false,
			]);
		}
		return $condition;
	}
}
