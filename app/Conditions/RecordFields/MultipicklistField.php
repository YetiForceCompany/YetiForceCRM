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
		return (bool) \array_intersect(explode('##', $this->value), $this->getValue());
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		return !$this->operatorE();
	}

	/** {@inheritdoc} */
	public function operatorC(): bool
	{
		$check = false;
		foreach ($this->getValue() as $valueRecord) {
			if (strpos($valueRecord, $this->value) || $valueRecord === $this->value) {
				$check = true;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		$check = true;
		foreach ($this->getValue() as $valueRecord) {
			if ($valueRecord === $this->value) {
				return false;
			}
			if (!(false == strpos($valueRecord, $this->value))) {
				$check = false;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function getValue(): array
	{
		return explode(' |##| ', parent::getValue());
	}
}
