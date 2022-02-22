<?php
/**
 * Country query field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Country query field class.
 */
class CountryField extends BaseField
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
	 * @return mixed
	 */
	public function getValue()
	{
		return explode('##', $this->value);
	}

	/** {@inheritdoc} */
	public function getOperator(): string
	{
		return 'a' === $this->operator ? 'e' : $this->operator;
	}
}
