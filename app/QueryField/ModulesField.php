<?php

namespace App\QueryField;

/**
 * Modules Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ModulesField extends BaseField
{
	/**
	 * Get value.
	 *
	 * @return array
	 */
	public function getValue()
	{
		return explode('##', $this->value);
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		return ['NOT IN', $this->getColumnName(), $this->getValue()];
	}
}
