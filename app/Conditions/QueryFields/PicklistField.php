<?php

namespace App\Conditions\QueryFields;

/**
 * Picklist Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PicklistField extends BaseField
{
	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN(): array
	{
		return ['NOT IN', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Record open operator.
	 *
	 * @return array
	 */
	public function operatorRo()
	{
		return [$this->getColumnName() => \App\RecordStatus::getStates($this->getModuleName(), \App\RecordStatus::RECORD_STATE_OPEN)];
	}

	/**
	 * Record closed operator.
	 *
	 * @return array
	 */
	public function operatorRc()
	{
		return [$this->getColumnName() => \App\RecordStatus::getStates($this->getModuleName(), \App\RecordStatus::RECORD_STATE_CLOSED)];
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (\is_array($this->value)) {
			return $this->value;
		}
		return explode('##', $this->value);
	}
}
