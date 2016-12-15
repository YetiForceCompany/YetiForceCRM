<?php
namespace App\QueryField;

/**
 * Date time Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DatetimeField extends DateField
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return array_shift(explode(' ', \DateTimeField::convertToDBFormat($this->value)));
	}

	/**
	 * Get array value
	 * @return mixed
	 */
	public function getArrayValue()
	{
		return array_map(function($row) {
			return \DateTimeField::convertToDBFormat(reset(explode(' ', $row)));
		}, explode(',', $this->value));
	}

	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		$value = $this->getValue();
		return ['between', $this->getColumnName(), $value . ' 00:00:00', $value . ' 23:59:59'];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		$value = $this->getValue();
		return ['not between', $this->getColumnName(), $value . ' 00:00:00', $value . ' 23:59:59'];
	}

	/**
	 * Between operator
	 * @return array
	 */
	public function operatorBw()
	{
		$value = $this->getArrayValue();
		return ['between', $this->getColumnName(), $value[0] . ' 00:00:00', $value[1] . ' 23:59:59'];
	}

	/**
	 * Before operator
	 * @return array
	 */
	public function operatorB()
	{
		return ['<', $this->getColumnName(), $this->getValue() . ' 00:00:00'];
	}

	/**
	 * After operator
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue() . ' 23:59:59'];
	}

	/**
	 * Get value
	 * @return mixed
	 */
	public function getStdValue()
	{
		if ($this->operator === 'custom') {
			$date = $this->getArrayValue();
		} else {
			$date = \DateTimeRange::getDateRangeByType($this->operator);
		}
		return [$date[0] . ' 00:00:00', $date[1] . ' 23:59:59'];
	}
}
