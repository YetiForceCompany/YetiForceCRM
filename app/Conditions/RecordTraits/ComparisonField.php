<?php
/**
 * Record comparison field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordTraits;

/**
 * Record comparison field class.
 */
trait ComparisonField
{
	/**
	 * Less than field operator.
	 *
	 * @return bool
	 */
	public function operatorLf(): bool
	{
		return $this->getValue() < $this->getValueFromSource();
	}

	/**
	 * Greater than field operator.
	 *
	 * @return bool
	 */
	public function operatorGf(): bool
	{
		return $this->getValue() > $this->getValueFromSource();
	}

	/**
	 * Less than field or equal operator.
	 *
	 * @return bool
	 */
	public function operatorMf(): bool
	{
		return $this->getValue() <= $this->getValueFromSource();
	}

	/**
	 * Greater than field or equal operator.
	 *
	 * @return bool
	 */
	public function operatorHf(): bool
	{
		return $this->getValue() >= $this->getValueFromSource();
	}
}
