<?php

namespace App\QueryField;

/**
 * Date time Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DatetimeField extends DateField
{
	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		$val = explode(' ', \DateTimeField::convertToDBFormat($this->value));
		return array_shift($val);
	}

	/**
	 * Get array value.
	 *
	 * @return mixed
	 */
	public function getArrayValue()
	{
		return array_map(function ($row) {
			$parts = explode(' ', $row);

			return \DateTimeField::convertToDBFormat(reset($parts));
		}, explode(',', $this->value));
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		$value = $this->getValue();

		return ['between', $this->getColumnName(), $value . ' 00:00:00', $value . ' 23:59:59'];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		$value = $this->getValue();

		return ['not between', $this->getColumnName(), $value . ' 00:00:00', $value . ' 23:59:59'];
	}

	/**
	 * Between operator.
	 *
	 * @return array
	 */
	public function operatorBw()
	{
		$value = $this->getArrayValue();

		return ['between', $this->getColumnName(), $value[0] . ' 00:00:00', $value[1] . ' 23:59:59'];
	}

	/**
	 * Before operator.
	 *
	 * @return array
	 */
	public function operatorB()
	{
		return ['<', $this->getColumnName(), $this->getValue() . ' 00:00:00'];
	}

	/**
	 * After operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue() . ' 23:59:59'];
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorGreaterthannow()
	{
		return ['>', $this->getColumnName(), date('Y-m-d H:i:s')];
	}

	/**
	 * Smaller operator.
	 *
	 * @return array
	 */
	public function operatorSmallerthannow()
	{
		return ['<', $this->getColumnName(), date('Y-m-d H:i:s')];
	}

	/**
	 * Get value.
	 *
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
