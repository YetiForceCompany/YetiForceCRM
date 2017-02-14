<?php
namespace App\QueryField;

/**
 * Modules Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ModulesField extends BaseField
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
