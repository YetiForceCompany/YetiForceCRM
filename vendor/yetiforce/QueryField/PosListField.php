<?php
namespace App\QueryField;

/**
 * PosList Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class PosListField extends PicklistField
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
