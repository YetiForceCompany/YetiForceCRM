<?php
namespace App\QueryFieldCondition;

/**
 * String Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OperatorDouble extends BaseFieldParser
{

	/**
	 * Lower operator
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->value];
	}

	/**
	 * Greater operator
	 * @return array
	 */
	public function operatorG()
	{
		return ['>', $this->getColumnName(), $this->value];
	}

	/**
	 * Lower or equal operator
	 * @return array
	 */
	public function operatorM()
	{
		return ['<=', $this->getColumnName(), $this->value];
	}

	/**
	 * Greater or equal operator
	 * @return array
	 */
	public function operatorH()
	{
		return ['>=', $this->getColumnName(), $this->value];
	}
}
