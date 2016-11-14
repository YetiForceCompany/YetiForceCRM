<?php
namespace App\QueryFieldCondition;

/**
 * Date Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DateCondition extends BaseFieldParser
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return \DateTimeField::convertToDBFormat($this->value);
	}

	/**
	 * Get array value
	 * @return mixed
	 */
	public function getArrayValue()
	{
		$value = explode(',', $this->value);
		return array_map('\DateTimeField::convertToDBFormat', $value);
	}

	/**
	 * Between operator
	 * @return array
	 */
	public function operatorBw()
	{
		$value = $this->getArrayValue();
		return ['between', $this->getColumnName(), $value[0], $value[1]];
	}

	/**
	 * Before operator
	 * @return array
	 */
	public function operatorB()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * After operator
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}
}
