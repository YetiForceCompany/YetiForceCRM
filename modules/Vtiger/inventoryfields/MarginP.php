<?php

/**
 * Inventory MarginP Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MarginP_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'MarginP';
	protected $defaultLabel = 'LBL_MARGIN_PERCENT';
	protected $defaultValue = 0;
	protected $columnName = 'marginp';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $summationValue = true;
	protected $colSpan = 15;
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

	public function getSummaryValuesFromData($data)
	{
		$sum = $purchase = $totalOrNet = 0;
		if (\is_array($data)) {
			foreach ($data as $row) {
				$purchase += $row['qty'] * ($row['purchase'] ?? 0);
				if (isset($row['net'])) {
					$totalOrNet += $row['net'];
				} else {
					$totalOrNet += $row['total'];
				}
			}
			if (!empty($purchase) && !empty($totalOrNet)) {
				$subtraction = ($totalOrNet - $purchase);
				$sum = ($subtraction * 100) / $purchase;
			}
		}
		return $sum;
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
		if (!\App\Validator::float($value)) {
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
		$value = 0.0;
		$quantity = static::getInstance($this->getModuleName(), 'Quantity')->getValueForSave($item, $userFormat);
		$purchase = static::getInstance($this->getModuleName(), 'Purchase')->getValueForSave($item, $userFormat);
		$totalPurchase = $purchase * $quantity;
		if (!empty($totalPurchase)) {
			$value = 100.0 * static::getInstance($this->getModuleName(), 'Margin')->getValueForSave($item, $userFormat) / $totalPurchase;
		}
		return $value;
	}
}
