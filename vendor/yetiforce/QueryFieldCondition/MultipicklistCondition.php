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

	public function getValue()
	{
		$value = $this->value;
		$valueArray = explode(',', $value);
		if (in_array($this->operator, ['e', 'n'])) {
			$valueArray = \App\QueryConditionParser::getCombinations($valueArray);
			foreach ($valueArray as $key => $value) {
				$valueArray[$key] = ltrim($value, ' |##| ');
			}
		}
		return $valueArray;
	}

	public function operatorE()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	public function operatorN()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	public function operatorC()
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	public function operatorK()
	{
		return ['or not like', $this->getColumnName(), $this->getValue()];
	}
}
