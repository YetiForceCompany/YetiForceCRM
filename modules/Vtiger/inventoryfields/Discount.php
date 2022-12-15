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
	/** {@inheritdoc} */
	protected $type = 'Discount';
	/** {@inheritdoc} */
	protected $defaultLabel = 'LBL_DISCOUNT';
	/** {@inheritdoc} */
	protected $defaultValue = 0;
	/** {@inheritdoc} */
	protected $columnName = 'discount';
	/** {@inheritdoc} */
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	/** {@inheritdoc} */
	protected $customColumn = [
		'discountparam' => 'string',
	];
	/** {@inheritdoc} */
	protected $summationValue = true;
	/** {@inheritdoc} */
	protected $maximumLength = '99999999999999999999';
	/** {@inheritdoc} */
	protected $customMaximumLength = [
		'discountparam' => 255,
	];
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::NUMBER;
	/** {@inheritdoc} */
	protected $customPurifyType = [
		'discountparam' => App\Purifier::TEXT,
	];
	/** {@inheritdoc} */
	protected $params = ['default_type', 'summary_enabled'];

	/** @var string[] Aggregation types */
	public const AGGREGATION_TYPES = [
		0 => 'global',
		1 => 'group',
		2 => 'individual',
		3 => 'additional',
	];

	/**
	 * Get aggregation discount type.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getAggregationNameById(int $id): string
	{
		return self::AGGREGATION_TYPES[$id];
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		$value = \App\Fields\Double::formatToDisplay($value);
		if (isset($rowData['currency']) && $currencySymbol = \App\Fields\Currency::getById($rowData['currency'])['currency_symbol'] ?? '') {
			$value = \CurrencyField::appendCurrencySymbol($value, $currencySymbol);
		}

		return $value;
	}

	/** {@inheritdoc} */
	public function getEditValue(array $itemData, string $column = '')
	{
		$value = parent::getEditValue($itemData, $column);
		if (!$column || $column === $this->getColumnName()) {
			$value = \App\Fields\Double::formatToDisplay($value, false);
		}

		return $value;
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
	 * @param string $aggregationMode 0-can not be combined, 1-summary, 2-cascade
	 *
	 * @return float
	 */
	private function getDiscountValue(array $discountParam, float $totalPrice, int $aggregationMode): float
	{
		$value = $discountValue = 0.0;
		$types = $discountParam['aggregationType'] ?? [];
		$isMarkup = $this->isMarkup($discountParam);
		if (!\is_array($types)) {
			$types = [$types];
		}
		foreach ($types as $type) {
			$discountValue = $this->getDiscountValueByType($type, $discountParam, $totalPrice);
			$value += $discountValue;
			if (2 === $aggregationMode) {
				$totalPrice = $isMarkup ? ($totalPrice + $discountValue) : ($totalPrice - $discountValue);
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

	/** {@inheritdoc} */
	public function compare($value, $prevValue, string $column): bool
	{
		return $column === $this->getColumnName() ? \App\Validator::floatIsEqual((float) $value, (float) $prevValue, 8) : parent::compare($value, $prevValue, $column);
	}

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$qualifiedModuleName = 'Settings:LayoutEditor';
		$data = parent::getConfigFieldsData();

		$data['default_type'] = [
			'name' => 'default_type',
			'label' => 'LBL_INV_DISCOUNT_MARKUP_MODE',
			'uitype' => 16,
			'maximumlength' => '1',
			'typeofdata' => 'V~M',
			'tooltip' => 'LBL_INV_DISCOUNT_MARKUP_MODE_DESC',
			'purifyType' => \App\Purifier::INTEGER,
			'defaultvalue' => 0,
			'picklistValues' => [
				'0' => \App\Language::translate('Discount', $qualifiedModuleName),
				'1' => \App\Language::translate('LBL_MARKUP', $qualifiedModuleName)
			]
		];

		return $data;
	}

	/**
	 * Check if markup is set by default.
	 *
	 * @return bool
	 */
	public function isMarkupDefault(): bool
	{
		return 1 === (int) ($this->getParamsConfig()['default_type'] ?? 0);
	}

	/**
	 * Check if markup.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function isMarkup(array $params): bool
	{
		return 'markup' === ($params['type'] ?? '');
	}

	/**
	 * Get discount info.
	 *
	 * @param array $rowData
	 * @param bool  $raw
	 *
	 * @return array
	 */
	public function getDiscountInfo(array $rowData, bool $raw = false): array
	{
		$discounts = [];
		if (empty($rowData['discountparam'])) {
			return $discounts;
		}
		$discountParam = \App\Json::decode($rowData['discountparam']);
		$types = $discountParam['aggregationType'] ?? [];
		if (!\is_array($types)) {
			$types = [$types];
		}
		foreach ($types as $type) {
			$discountType = $discountParam["{$type}DiscountType"] ?? 'percentage';
			$discount = $discountParam["{$type}Discount"];
			if (!$raw) {
				$discount = \App\Fields\Double::formatToDisplay($discount, false);
			}
			switch ($discountType) {
				case 'amount':
					$discounts[$discountType] = $discount;
					break;
				case 'percentage':
					$discounts[$discountType] = $raw ? $discount : $discount . '%';
					break;
				default: break;
			}
		}

		return $discounts;
	}
}
