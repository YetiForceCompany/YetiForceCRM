<?php

/**
 * Date Query Field Class.
 *
 * @package   App
 */

namespace App\Conditions\QueryFields;

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
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		if ($order && 'DESC' === strtoupper($order)) {
			$sort = SORT_DESC;
		} else {
			$sort = SORT_ASC;
		}
		$orderBy = [$this->getColumnName() => $sort];
		if ('Calendar' === $this->getModuleName()) {
			if ('date_start' === $this->fieldModel->getColumnName()) {
				$field = $this->queryGenerator->getModuleField('time_start');
				if ($field) {
					$orderBy[$field->getTableName() . '.' . $field->getColumnName()] = $sort;
				}
			} elseif ('due_date' === $this->fieldModel->getColumnName()) {
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
	 * @param ?string $operator
	 *
	 * @return array|bool
	 */
	public function getCondition(?string $operator = null)
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (isset(\App\Condition::DATE_OPERATORS[$this->operator]) && !method_exists($this, $fn)) {
			$fn = 'getStdOperator';
		}
		if (!($methodExists = method_exists($this, $fn))) {
			\App\Log::error("Not found operator: {$fn}({$this->operator}) in  " . __CLASS__);
		}
		return $methodExists ? $this->{$fn}() : $methodExists;
	}

	/**
	 * Get array value.
	 *
	 * @return mixed
	 */
	public function getArrayValue()
	{
		return array_map(function ($row) {
			return \current(explode(' ', $row));
		}, explode(',', $this->value));
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

	/**
	 * MoreThanDaysAgo operator.
	 *
	 * @return bool
	 */
	public function operatorMoreThanDaysAgo()
	{
		return ['<=', $this->getColumnName(), date('Y-m-d', strtotime('-' . $this->getValue() . ' days'))];
	}
}
