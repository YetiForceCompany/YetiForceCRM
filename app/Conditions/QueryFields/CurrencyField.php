<?php

namespace App\Conditions\QueryFields;

/**
 * Currency Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class CurrencyField extends IntegerField
{
	/**
	 * Get value.
	 *
	 * @return float
	 */
	public function getValue()
	{
		$value = $this->value;
		$uiType = $this->fieldModel->getUIType();
		if (72 === $uiType) {
			$value = \CurrencyField::convertToDBFormat($value, null, true);
		} elseif (71 === $uiType) {
			$value = \CurrencyField::convertToDBFormat($value);
		}
		return $value;
	}
}
