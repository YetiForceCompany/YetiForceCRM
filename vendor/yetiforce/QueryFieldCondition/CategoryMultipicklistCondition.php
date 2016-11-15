<?php
namespace App\QueryFieldCondition;

/**
 * CategoryMultipicklist Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class CategoryMultipicklistCondition extends BaseFieldParser
{
	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		return ['like', $this->getColumnName(), $this->getValue()];
	}
	
	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['not like', $this->getColumnName(), $this->getValue()];
	}
}
