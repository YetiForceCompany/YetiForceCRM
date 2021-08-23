<?php

namespace App\Conditions\RecordFields;

/**
 * Multi picklist condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultipicklistField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		$check = false;
		foreach (explode('##', $this->value) as $value) {
			if (\in_array($value, explode(' |##| ', $this->getValue()))) {
				$check = true;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		$check = false;
		if (!$this->operatorE()) {
			return true;
		}
		return $check;
	}
}
