<?php
namespace App\QueryFieldCondition;

/**
 * PosList Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class PosListCondition extends PicklistCondition
{
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
}
