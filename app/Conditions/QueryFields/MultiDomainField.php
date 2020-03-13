<?php
/**
 * MultiDomain Query Field Class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Class MultiDomainField.
 */
class MultiDomainField extends BaseField
{
	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return trim($this->value, ',');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperator()
	{
		return 'a' === $this->operator ? 'c' : $this->operator;
	}
}
