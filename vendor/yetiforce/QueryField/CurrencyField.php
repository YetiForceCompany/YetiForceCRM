<?php
namespace App\QueryField;

/**
 * Currency Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class CurrencyField extends IntegerField
{

	/**
	 * Get value
	 * @return double
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
