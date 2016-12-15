<?php
namespace App\QueryField;

/**
 * Time Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class TimeField extends BaseField
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return (new \DateTimeField(date('Y-m-d') . ' ' . $this->value))->getDBInsertTimeValue();
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
	 * Lower operator
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Before operator
	 * @return array
	 */
	public function operatorB()
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * After operator
	 * @return array
	 */
	public function operatorA()
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}
}
