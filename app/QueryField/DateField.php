<?php

/**
 * Date Query Field Class.
 *
 * @package   App
 */

namespace App\QueryField;

/**
 * Date Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DateField extends BaseField
{
	/**
	 * Get order by.
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		if ($order && strtoupper($order) === 'DESC') {
			$sort = SORT_DESC;
		} else {
			$sort = SORT_ASC;
		}
		$orderBy = [$this->getColumnName() => $sort];
		if ($this->getModuleName() === 'Calendar') {
			if ($this->fieldModel->getColumnName() === 'date_start') {
				$field = $this->queryGenerator->getModuleField('time_start');
				if ($field) {
					$orderBy[$field->getTableName() . '.' . $field->getColumnName()] = $sort;
				}
			} elseif ($this->fieldModel->getColumnName() === 'due_date') {
				$field = $this->queryGenerator->getModuleField('time_end');
				if ($field) {
					$orderBy[$field->getTableName() . '.' . $field->getColumnName()] = $sort;
				}
			}
		}
		return $orderBy;
	}

	/**
	 * Get condition.
	 *
	 * @return bool|array
	 */
	public function getCondition()
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (in_array($this->operator, \App\CustomView::STD_FILTER_CONDITIONS)) {
			\App\Log::trace('Entering to getStdOperator in ' . __CLASS__);

			return $this->getStdOperator();
		} elseif (method_exists($this, $fn)) {
			\App\Log::trace("Entering to $fn in " . __CLASS__);

			return $this->$fn();
		}
		\App\Log::error("Not found operator: $fn in  " . __CLASS__);

		return false;
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return \DateTimeField::convertToDBFormat($this->value);
	}

	/**
	 * Get array value.
	 *
	 * @return mixed
	 */
	public function getArrayValue()
	{
		return array_map(function ($row) {
			return \DateTimeField::convertToDBFormat(\current(explode(' ', $row)));
		}, explode(',', $this->value));
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
		return $date;
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getStdOperator()
	{
		$value = $this->getStdValue();

		return ['between', $this->getColumnName(), $value[0], $value[1]];
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
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * After operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * DashBoard Calendar widget listview operator.
	 *
	 * @return array
	 */
	public function operatorIr()
	{
		$value = $this->getValue();

		return ['and', ['<=', $this->getColumnName(), $value], ['>=', 'vtiger_activity.due_date', $value]];
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorGreaterthannow()
	{
		return ['>', $this->getColumnName(), date('Y-m-d')];
	}

	/**
	 * Smaller operator.
	 *
	 * @return array
	 */
	public function operatorSmallerthannow()
	{
		return ['<', $this->getColumnName(), date('Y-m-d')];
	}
}
