<?php
/**
 * Query comparison file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryTraits;

/**
 * Query comparison class.
 */
trait Comparison
{
	/**
	 * Lower operator.
	 *
	 * @return array
	 */
	public function operatorL(): array
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorG(): array
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Lower or equal operator.
	 *
	 * @return array
	 */
	public function operatorM(): array
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater or equal operator.
	 *
	 * @return array
	 */
	public function operatorH(): array
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}
}
