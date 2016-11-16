<?php
namespace App\QueryField;

/**
 * CategoryMultipicklist Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class CategoryMultipicklistField extends BaseField
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
