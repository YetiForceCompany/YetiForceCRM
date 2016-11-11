<?php
namespace App\QueryFieldCondition;

/**
 * Multipicklist Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class MultipicklistCondition extends BaseFieldParser
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		$value = $this->value;
		$valueArray = explode(',', $value);
		if (in_array($this->operator, ['e', 'n'])) {
			foreach (\App\QueryConditionParser::getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value, ' |##| ');
			}
		}
		return $valueArray;
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['not', [$this->getColumnName() => $this->getValue()]];
	}

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getColumnName(), implode(' |##| ', $this->getValue()) . '%', false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . implode(' |##| ', $this->getValue()), false];
	}

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		return ['or not like', $this->getColumnName(), $this->getValue()];
	}
}
