<?php
/**
 * Record comparison file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordTraits;

/**
 * Record comparison class.
 */
trait Comparison
{
	/**
	 * Less than operator.
	 *
	 * @return bool
	 */
	public function operatorL(): bool
	{
		return $this->getValue() < $this->value;
	}

	/**
	 * Greater than operator.
	 *
	 * @return bool
	 */
	public function operatorG(): bool
	{
		return $this->getValue() > $this->value;
	}

	/**
	 * Less than or equal to operator.
	 *
	 * @return bool
	 */
	public function operatorM(): bool
	{
		return $this->getValue() <= $this->value;
	}

	/**
	 * Greater than or equal to operator.
	 *
	 * @return bool
	 */
	public function operatorH(): bool
	{
		return $this->getValue() >= $this->value;
	}
}
