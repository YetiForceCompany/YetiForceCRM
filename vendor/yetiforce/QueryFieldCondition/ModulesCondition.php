<?php
namespace App\QueryFieldCondition;

/**
 * Modules Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ModulesCondition extends BaseFieldParser
{

	/**
	 * Get value
	 * @return array
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
