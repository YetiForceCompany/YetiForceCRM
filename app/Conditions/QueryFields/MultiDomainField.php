<?php
/**
 * MultiDomain Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Class MultiDomainField.
 */
class MultiDomainField extends BaseField
{
	/** {@inheritdoc} */
	public function getValue()
	{
		return trim($this->value, ',');
	}

	/** {@inheritdoc} */
	public function getOperator(): string
	{
		return 'a' === $this->operator ? 'c' : $this->operator;
	}
}
