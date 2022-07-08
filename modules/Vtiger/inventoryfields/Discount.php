<?php

/**
 * Inventory Discount Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Discount_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Discount';
	protected $defaultLabel = 'LBL_DISCOUNT';
	protected $defaultValue = 0;
	protected $columnName = 'discount';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $customColumn = [
		'discountparam' => 'string',
	];
	protected $summationValue = true;
	protected $maximumLength = '99999999999999999999';
	protected $customMaximumLength = [
		'discountparam' => 255,
	];
	protected $purifyType = \App\Purifier::NUMBER;
	protected $customPurifyType = [
		'discountparam' => App\Purifier::TEXT,
	];

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
			$this->dbValue["{$value}"] = $name === $this->getColumnName() ? App\Fields\Double::formatToDb($value) : $value;
		}
		return $this->dbValue["{$value}"];
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($columnName === $this->getColumnName()) {
			if ($isUserFormat) {
				$value = $this->getDBValue($value, $columnName);
				if (null !== $originalValue) {
					$originalValue = $this->getDBValue($originalValue, $columnName);
				}
			}
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
			if (null !== $originalValue && !\App\Validator::floatIsEqualUserCurrencyDecimals($value, $originalValue)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . ($columnName ?? $this->getColumnName()) . "||{$this->getModuleName()}||$value($originalValue)", 406);
			}
		} elseif (App\TextUtils::getTextLength($value) > $this->customMaximumLength[$columnName]) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function getValueForSave(array $item, bool $userFormat = false, string $column = null)
	{
		if ($column === $this->getColumnName() || null === $column) {
			$value = 0.0;
			if (!\App\Json::isEmpty($item['discountparam'] ?? '')) {
				$aggregation = $item['discount_aggreg'] ?? \Vtiger_Inventory_Model::getDiscountsConfig('aggregation');
				if (\is_numeric($aggregation)) {
					$discountParam = \App\Json::decode($item['discountparam']) ?? [];
					$totalPrice = static::getInstance($this->getModuleName(), 'TotalPrice')->getValueForSave($item, $userFormat);
					$value = $this->getDiscountValue($discountParam, $totalPrice, (int) $aggregation);
				}
			}
		} else {
			$value = $userFormat ? $this->getDBValue($item[$column]) : $item[$column];
		}
		return $value;
	}

	/**
	 * Calculate the discount value.
	 *
	 * @param array  $discountParam
	 * @param float  $totalPrice
	 * @param string $mode          0-can not be combined, 1-summary, 2-cascade
	 *
	 * @return float
	 */
	private function getDiscountValue(array $discountParam, float $totalPrice, int $mode): float
	{
		$value = $discountValue = 0.0;
		$types = $discountParam['aggregationType'] ?? [];
		if (!\is_array($types)) {
			$types = [$types];
		}
		foreach ($types as $type) {
			$discountValue = $this->getDiscountValueByType($type, $discountParam, $totalPrice);
			$value += $discountValue;
			if (2 === $mode) {
				$totalPrice -= $discountValue;
			}
		}
		return $value;
	}

	/**
	 * Gets the discount by type.
	 *
	 * @param string $aggregationType
	 * @param array  $discountParam
	 * @param float  $totalPrice
	 *
	 * @return float
	 */
	private function getDiscountValueByType(string $aggregationType, array $discountParam, float $totalPrice): float
	{
		$discountType = $discountParam["{$aggregationType}DiscountType"] ?? 'percentage';
		$discount = $discountParam["{$aggregationType}Discount"];
		$value = 0.0;
		switch ($discountType) {
			case 'amount':
				$value = $discount;
				break;
			case 'percentage':
				$value = $totalPrice * $discount / 100.00;
				break;
			default:
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||discountType||{$discountType}", 406);
		}
		return $value;
	}
}
