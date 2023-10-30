<?php
/**
 * Time query field conditions file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Time query field conditions class.
 */
class TimeField extends BaseField
{
	use \App\Conditions\QueryTraits\ComparisonField;

	/**
	 * Before operator.
	 *
	 * @return array
	 */
	public function operatorB()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * After operator.
	 *
	 * @return array
	 */
	public function operatorA(): array
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}
}
