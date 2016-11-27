<?php
namespace App\QueryField;

/**
 * Integer Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class IntegerField extends BaseField
{

	/**
	 * Lower operator
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater operator
	 * @return array
	 */
	public function operatorG()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Lower or equal operator
	 * @return array
	 */
	public function operatorM()
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater or equal operator
	 * @return array
	 */
	public function operatorH()
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}
}
