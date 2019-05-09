<?php

/**
 * Inventory Discount Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		'discountparam' => 255
	];
	protected $purifyType = \App\Purifier::NUMBER;
	protected $customPurifyType = [
		'discountparam' => App\Purifier::TEXT
	];

	/**
	 * {@inheritdoc}
	 */
	protected $isAutomaticValue = true;

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Fields\Double::formatToDisplay($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		return \App\Fields\Double::formatToDisplay($value, false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue[$value])) {
			$this->dbValue[$value] = $name === $this->getColumnName() ? App\Fields\Double::formatToDb($value) : $value;
		}
		return $this->dbValue[$value];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function validate($value, string $columnName, bool $isUserFormat, $originalValue)
	{
		if ($columnName === $this->getColumnName()) {
			if ($isUserFormat) {
				$value = $this->getDBValue($value, $columnName);
			}
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
			if (null !== $originalValue && !\App\Validator::floatIsEqual($value, $originalValue, (int) \App\User::getCurrentUserModel()->getDetail('no_of_currency_decimals'))) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $columnName ?? $this->getColumnName() . '||' . $this->getModuleName() . '||' . $value, 406);
			}
		} elseif (App\TextParser::getTextLength($value) > $this->customMaximumLength[$columnName]) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAutomaticValue(array $item, bool $userFormat = false)
	{
		$discountsConfig = Vtiger_Inventory_Model::getDiscountsConfig();
		$returnVal = 0.0;
		if (1 === (int) $discountsConfig['active'] && !\App\Json::isEmpty($item['discountparam'] ?? '')) {
			$discountParam = \App\Json::decode($item['discountparam']);
			$aggregationType = $discountParam['aggregationType'];
			$totalPrice = static::calculateFromField($this->getModuleName(), 'TotalPrice', $item, $userFormat);
			$method = 'calculateDiscount' . $this->getDiscountMethod((int) $discountsConfig['aggregation'], $aggregationType);
			$returnVal = $this->{$method}($totalPrice, $discountParam, $aggregationType);
		}
		return static::roundMethod((float) $returnVal);
	}

	/**
	 * Calculate the discount using the 'Can not be combined' method.
	 *
	 * @param float  $totalPrice
	 * @param array  $discountParam
	 * @param string $aggregationType
	 *
	 * @return float
	 */
	private function calculateDiscountCannotBeCombined(float $totalPrice, array $discountParam, string $aggregationType): float
	{
		$returnVal = 0.0;
		$discountType = $discountParam["{$aggregationType}DiscountType"] ?? 'percentage';
		$discount = $discountParam["{$aggregationType}Discount"];
		if ('amount' === $discountType) {
			$returnVal = $discount;
		} elseif ('percentage' === $discountType) {
			$returnVal = ($totalPrice * $discount / 100.00);
		} else {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||discountType||{$discountType}", 406);
		}
		return $returnVal;
	}

	/**
	 * Calculate the discount using the 'In total' method.
	 *
	 * @param float $totalPrice
	 * @param array $discountParam
	 * @param array $aggregationType
	 *
	 * @return float
	 */
	private function calculateDiscountInTotal(float $totalPrice, array $discountParam, array $aggregationType): float
	{
		$returnVal = 0;
		foreach ($aggregationType as $aggregationType) {
			$returnVal += $this->calculateDiscountCannotBeCombined($totalPrice, $discountParam, $aggregationType);
		}
		return $returnVal;
	}

	/**
	 * Calculate the discount using the 'Cascade' method.
	 *
	 * @param float $totalPrice
	 * @param array $discountParam
	 * @param array $aggregationType
	 *
	 * @return void
	 */
	private function calculateDiscountCascade(float $totalPrice, array $discountParam, array $aggregationType): float
	{
		$returnVal = 0.0;
		$totalPriceForDiscount = $totalPrice;
		foreach ($aggregationType as $aggregationType) {
			$discount = $this->calculateDiscountCannotBeCombined($totalPriceForDiscount, $discountParam, $aggregationType);
			$totalPriceForDiscount += $discount;
			$returnVal += $discount;
		}
		return $returnVal;
	}

	/**
	 * Recognize the discount counting method.
	 *
	 * @param int          $aggregation
	 * @param array|string $aggregationType
	 *
	 * @return string
	 */
	private function getDiscountMethod(int $aggregation, $aggregationType): string
	{
		if (!is_array($aggregationType)) {
			return 'CannotBeCombined';
		}
		switch ($aggregation) {
			case 0:
			case 1:
				$returnVal = 'Intotal';
				break;
			case 2:
				$returnVal = 'Cascade';
				break;
			default:
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||aggregation||$aggregation", 406);
		}
		return $returnVal;
	}
}
