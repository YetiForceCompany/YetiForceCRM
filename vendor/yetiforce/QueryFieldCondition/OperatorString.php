<?php

namespace App\QueryFieldCondition;

/**
 * String Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OperatorString extends BaseFieldParser
{

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getColumnName(), $this->value . '%', false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . $this->value, false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorC()
	{
		return ['like', $this->getColumnName(), $this->value];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorK()
	{
		return ['not like', $this->getColumnName(), $this->value];
	}
}
