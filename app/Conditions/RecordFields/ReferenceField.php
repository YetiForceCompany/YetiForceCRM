<?php

namespace App\Conditions\RecordFields;

/**
 * Reference condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license		YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function operatorC()
	{
		return false !== strpos(\App\Record::getLabel($this->getValue(), true), $this->value);
	}

	/** {@inheritdoc} */
	public function operatorK()
	{
		return false === strpos(\App\Record::getLabel($this->getValue(), true), $this->value);
	}
}
