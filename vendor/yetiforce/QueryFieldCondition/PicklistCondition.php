<?php
namespace App\QueryFieldCondition;

/**
 * Picklist Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PicklistCondition extends BaseFieldParser
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return explode(',', $this->value);
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['NOT IN', $this->getColumnName(), $this->getValue()];
	}
}
