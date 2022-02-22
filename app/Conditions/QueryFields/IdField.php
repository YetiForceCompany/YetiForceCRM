<?php

namespace App\Conditions\QueryFields;

/**
 * Id Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function getColumnName(): string
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
	public function operatorA(): array
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}
}
