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
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class MultipicklistField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		$recordValue = $this->getValue();
		$conditionValue = explode('##', $this->value);
		sort($recordValue);
		sort($conditionValue);
		return $recordValue === $conditionValue;
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		return !$this->operatorE();
	}

	/** {@inheritdoc} */
	public function operatorC(): bool
	{
		return !$this->operatorK();
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		return empty(array_intersect(explode('##', $this->value), $this->getValue()));
	}

	/** {@inheritdoc} */
	public function getValue(): array
	{
		return explode(' |##| ', parent::getValue());
	}
}
