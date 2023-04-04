<?php
/**
 * Picklist Inventory Query Field File.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

declare(strict_types=1);

namespace App\Conditions\QueryFields\Inventory;

/**
 * Picklist Inventory Query Field Class.
 */
class PicklistField extends BaseField
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
		if (\is_array($this->value)) {
			return $this->value;
		}
		return explode('##', $this->value);
	}
}
