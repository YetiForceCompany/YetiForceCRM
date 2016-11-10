<?php
namespace App\QueryFieldCondition;

/**
 * Currency Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class CurrencyCondition extends BaseFieldParser
{

	public function getValue()
	{
		$value = $this->value;
		$uiType = $this->fieldModel->getUIType();
		if ($uiType == 72) {
			$value = \CurrencyField::convertToDBFormat($value, null, true);
		} elseif ($uiType == 71) {
			$value = \CurrencyField::convertToDBFormat($value);
		}
		return $value;
	}

	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['<>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Lower operator
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater operator
	 * @return array
	 */
	public function operatorG()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Lower or equal operator
	 * @return array
	 */
	public function operatorM()
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater or equal operator
	 * @return array
	 */
	public function operatorH()
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}
}
