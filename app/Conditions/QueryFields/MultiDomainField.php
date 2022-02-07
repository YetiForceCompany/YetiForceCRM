<?php
/**
 * MultiDomain Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
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
	public function getOperator()
	{
		return 'a' === $this->operator ? 'c' : $this->operator;
	}
}
