<?php

namespace App\Conditions\QueryFields;

/**
 * Modules Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ModulesField extends BaseField
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
	 * Get value.
	 *
	 * @return array
	 */
	public function getValue()
	{
		return explode('##', $this->value);
	}
}
