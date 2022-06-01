<?php

/**
 * Inventory Tax Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Tax_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Tax';
	protected $defaultLabel = 'LBL_TAX';
	protected $defaultValue = 0;
	protected $columnName = 'tax';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $customColumn = [
		'taxparam' => 'string'
	];
	protected $summationValue = true;
	protected $maximumLength = '99999999999999999999';
	protected $customMaximumLength = [
		'taxparam' => 255
	];
	protected $purifyType = \App\Purifier::NUMBER;
	protected $customPurifyType = [
		'taxparam' => \App\Purifier::TEXT
	];
	/**
	 * @var array List of shared fields
	 */
	public $shared = ['taxparam' => 'tax_percent'];

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}

	public function getClassName($data)
	{
		if (\count($data) > 0 && 0 == $data[0]['taxmode']) {
			return 'hide';
		}
		return '';
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if ($name !== $this->getColumnName()) {
			$valid = $value ? \App\Json::decode($value) : [];
			if (isset($valid['individualTax'])) {
				$valid['individualTax'] = $valid['individualTax'] ?? 0;
				$valid['globalTax'] = $valid['globalTax'] ?? 0;
				$value = \App\Json::encode($valid);
			}
		} else {
			$value = App\Fields\Double::formatToDb($value);
		}
		return $value;
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
			if (!is_numeric($value)) {
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
			}
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
			if (null !== $originalValue && !\App\Validator::floatIsEqualUserCurrencyDecimals($value, $originalValue)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . ($columnName ?? $this->getColumnName()) . "||{$this->getModuleName()}||$value($originalValue)", 406);
			}
		} else {
			if (App\TextUtils::getTextLength($value) > $this->customMaximumLength[$columnName]) {
				$module = $this->getModuleName();
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$module||$value", 406);
			}
		}
	}

	/**
	 * Get configuration parameters for taxes.
	 *
	 * @param string     $taxParam String parameters json encode
	 * @param float      $net
	 * @param array|null $return
	 *
	 * @return array
	 */
	public function getTaxParam(string $taxParam, float $net, ?array $return = []): array
	{
		$taxParam = json_decode($taxParam, true);
		if (empty($taxParam)) {
			return [];
		}
		if (\is_string($taxParam['aggregationType'])) {
			$taxParam['aggregationType'] = [$taxParam['aggregationType']];
		}
		if (!$return || empty($taxParam['aggregationType'])) {
			$return = [];
		}
		if (isset($taxParam['aggregationType'])) {
			foreach ($taxParam['aggregationType'] as $aggregationType) {
				$percent = (string) ($taxParam[$aggregationType . 'Tax'] ?? 0);
				if (!isset($return[$percent])) {
					$return[$percent] = 0;
				}
				$return[$percent] += $net * ($percent / 100);
			}
		}
		return $return;
	}

	/** {@inheritdoc} */
	public function getValueForSave(array $item, bool $userFormat = false, string $column = null)
	{
		if ($column === $this->getColumnName() || null === $column) {
			$value = 0.0;
			if (!\App\Json::isEmpty($item['taxparam'] ?? '') && ($taxesConfig = \Vtiger_Inventory_Model::getTaxesConfig())) {
				$taxParam = \App\Json::decode($item['taxparam']);
				$netPrice = static::getInstance($this->getModuleName(), 'NetPrice')->getValueForSave($item, $userFormat);
				$value = $this->getTaxValue($taxParam, $netPrice, (int) $taxesConfig['aggregation']);
			}
		} else {
			$value = $userFormat ? $this->getDBValue($item[$column]) : $item[$column];
		}
		return $value;
	}

	/**
	 * Calculate the tax value.
	 *
	 * @param array  $taxParam
	 * @param float  $netPrice
	 * @param string $mode     0-can not be combined, 1-summary, 2-cascade
	 *
	 * @return float
	 */
	public function getTaxValue(array $taxParam, float $netPrice, int $mode): float
	{
		$value = 0.0;
		if ($taxParam) {
			$types = $taxParam['aggregationType'];
			if (!\is_array($types)) {
				$types = [$types];
			}
			foreach ($types as $type) {
				$taxValue = $netPrice * $taxParam["{$type}Tax"] / 100.00;
				$value += $taxValue;
				if (2 === $mode) {
					$netPrice += $taxValue;
				}
			}
		}
		return $value;
	}
}
