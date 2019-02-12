<?php

namespace App\QueryField;

/**
 * Id Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IdField extends StringField
{
	/**
	 * Starts with operator.
	 *
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getColumnName(), $this->getValue() . '%', false];
	}

	/**
	 * Get column name.
	 *
	 * @return string
	 */
	public function getColumnName()
	{
		if ($this->fullColumnName) {
			return $this->fullColumnName;
		}
		return $this->fullColumnName = $this->queryGenerator->getColumnName('id');
	}

	/**
	 * Ends with operator.
	 *
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . $this->getValue(), false];
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}
}
