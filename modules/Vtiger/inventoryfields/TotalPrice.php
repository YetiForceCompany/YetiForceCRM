<?php

/**
 * Inventory TotalPrice Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TotalPrice_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'TotalPrice';
	protected $defaultLabel = 'LBL_TOTAL_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'total';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $summationValue = true;
	protected $maximumLength = '99999999999999999999';
	protected $purifyType = \App\Purifier::NUMBER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Fields\Double::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return \App\Fields\Double::formatToDisplay($value, false);
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue["{$value}"])) {
			$this->dbValue["{$value}"] = App\Fields\Double::formatToDb($value);
		}
		return $this->dbValue["{$value}"];
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($isUserFormat) {
			$value = $this->getDBValue($value, $columnName);
			if (null !== $originalValue) {
				$originalValue = $this->getDBValue($originalValue, $columnName);
			}
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($this->maximumLength < $value || -$this->maximumLength > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
		if (null !== $originalValue && !\App\Validator::floatIsEqualUserCurrencyDecimals($value, $originalValue)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . ($columnName ?? $this->getColumnName()) . "||{$this->getModuleName()}||$value($originalValue)", 406);
		}
	}

	/** {@inheritdoc} */
	public function getValueForSave(array $item, bool $userFormat = false, string $column = null)
	{
		if ($column === $this->getColumnName() || null === $column) {
			$quantity = 1;
			$quantityModel = static::getInstance($this->getModuleName(), 'Quantity');
			if (isset($item[$quantityModel->getColumnName()])) {
				$quantity = $quantityModel->getValueForSave($item, $userFormat);
			}
			$unitPriceModel = static::getInstance($this->getModuleName(), 'UnitPrice');
			if (isset($item[$unitPriceModel->getColumnName()])) {
				$price = $unitPriceModel->getValueForSave($item, $userFormat);
				$value = (float) ($quantity * $price);
			} else {
				$value = $userFormat ? $this->getDBValue($item[$this->getColumnName()]) : $item[$this->getColumnName()];
			}
		} else {
			$value = $userFormat ? $this->getDBValue($item[$column]) : $item[$column];
		}
		return $value;
	}
}
