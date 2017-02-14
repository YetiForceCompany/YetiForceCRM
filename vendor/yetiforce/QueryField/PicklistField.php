<?php
namespace App\QueryField;

/**
 * Picklist Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PicklistField extends BaseField
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
