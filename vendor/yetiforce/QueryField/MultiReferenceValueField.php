<?php
namespace App\QueryField;

/**
 * MultiReferenceValue Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class MultiReferenceValueField extends BaseField
{

	public function getValue()
	{
		$valueArray = explode(',', $this->value);
		foreach ($valueArray as $key => $value) {
			$valueArray[$key] = '|#|' . $value . '|#|';
		}
		return$valueArray;
	}

	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['or not like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Contains operator
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
					$value . '%'
				], false
			]);
		}
		return $condition;
	}
}
