<?php

namespace App\QueryField;

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
		if ($uiType === 72) {
			$value = \CurrencyField::convertToDBFormat($value, null, true);
		} elseif ($uiType === 71) {
			$value = \CurrencyField::convertToDBFormat($value);
		}
		return $value;
	}
}
