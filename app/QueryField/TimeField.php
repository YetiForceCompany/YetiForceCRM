<?php

namespace App\QueryField;

/**
 * Time Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class TimeField extends BaseField
{
	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorG()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return \App\Fields\Time::formatToDB($this->value);
	}

	/**
	 * Lower operator.
	 *
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Before operator.
	 *
	 * @return array
	 */
	public function operatorB()
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * After operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}
}
