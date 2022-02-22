<?php

namespace App\Conditions\QueryFields;

/**
 * Date time Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$val = \explode(' ', $this->value);
		return \current($val);
	}

	/**
	 * Get array value.
	 *
	 * @return mixed
	 */
	public function getArrayValue()
	{
		return explode(',', $this->value);
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE(): array
	{
		$value = $this->getValue();

		return ['between', $this->getColumnName(), $value . ' 00:00:00', $value . ' 23:59:59'];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN(): array
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

		return ['between', $this->getColumnName(), $value[0], $value[1]];
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
	public function operatorA(): array
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
		if ('custom' === $this->operator) {
			$date = $this->getArrayValue();
			return [$date[0], $date[1]];
		}
		$date = \DateTimeRange::getDateRangeByType($this->operator);
		return [$date[0] . ' 00:00:00', $date[1] . ' 23:59:59'];
	}
}
