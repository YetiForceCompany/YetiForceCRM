<?php
namespace App\QueryField;

/**
 * Currency Query Field Class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
