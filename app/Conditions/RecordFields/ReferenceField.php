<?php

namespace App\Conditions\RecordFields;

/**
 * Reference condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license		YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author		Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author		Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ReferenceField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE()
	{
		return \App\Record::getLabel($this->getValue(), true) === $this->value;
	}

	/** {@inheritdoc} */
	public function operatorS(): bool
	{
		$referenceLabel = \App\Record::getLabel($this->getValue(), true);
		$lengthValueConditions = \strlen($this->value);
		if (\strlen($referenceLabel) >= $lengthValueConditions) {
			return 0 == substr_compare($referenceLabel, $this->value, 0, $lengthValueConditions, true);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function operatorEw(): bool
	{
		$referenceLabel = \App\Record::getLabel($this->getValue(), true);
		$lengthLabelRecord = \strlen($referenceLabel);
		$lengthValueConditions = \strlen($this->value);
		if ($lengthLabelRecord >= $lengthValueConditions) {
			return 0 == substr_compare($referenceLabel, $this->value, $lengthLabelRecord - $lengthValueConditions, $lengthValueConditions);
		}
		return false;
	}

	/** {@inheritdoc} */
	public function operatorC()
	{
		return false !== strpos(\App\Record::getLabel($this->getValue(), true), $this->value);
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		return false === strpos(\App\Record::getLabel($this->getValue(), true), $this->value);
	}
}
