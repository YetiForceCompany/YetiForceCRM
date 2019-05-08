<?php

/**
 * Inventory Tax Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	protected $isAutomaticValue = true;

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}

	public function getClassName($data)
	{
		if (count($data) > 0 && 0 == $data[0]['taxmode']) {
			return 'hide';
		}
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		if ($name !== $this->getColumnName()) {
			$valid = $value ? \App\Json::decode($value) : [];
			if (isset($valid['individualTax'])) {
				$valid['individualTax'] = App\Fields\Double::formatToDb($valid['individualTax']);
				$valid['globalTax'] = App\Fields\Double::formatToDb($valid['globalTax'] ?? 0);
				$value = \App\Json::encode($valid);
			}
		} else {
			$value = App\Fields\Double::formatToDb($value);
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function validate($value, string $columnName, bool $isUserFormat, $originalValue)
	{
		if ($columnName === $this->getColumnName()) {
			if (!is_numeric($value)) {
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
			}
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
			if (null !== $originalValue && !\App\Validator::floatIsEqual($value, $originalValue, (int) \App\User::getCurrentUserModel()->getDetail('no_of_currency_decimals'))) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $columnName ?? $this->getColumnName() . '||' . $this->getModuleName() . '||' . $value, 406);
			}
		} else {
			if (App\TextParser::getTextLength($value) > $this->customMaximumLength[$columnName]) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		}
	}

	/**
	 * Get configuration parameters for taxes.
	 *
	 * @param string     $taxParam String parameters json encode
	 * @param float      $net
	 * @param null|array $return
	 *
	 * @return array
	 */
	public function getTaxParam(string $taxParam, float $net, ?array $return = []): array
	{
		$taxParam = json_decode($taxParam, true);
		if (empty($taxParam)) {
			return [];
		}
		if (is_string($taxParam['aggregationType'])) {
			$taxParam['aggregationType'] = [$taxParam['aggregationType']];
		}
		if (!$return || empty($taxParam['aggregationType'])) {
			$return = [];
		}
		if (isset($taxParam['aggregationType'])) {
			foreach ($taxParam['aggregationType'] as $aggregationType) {
				$precent = (string) $taxParam[$aggregationType . 'Tax'];
				if (!isset($return[$precent])) {
					$return[$precent] = 0;
				}
				$return[$precent] += $net * ($precent / 100);
			}
		}
		return $return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAutomaticValue(array $item, bool $userFormat = false)
	{
		$returnVal = 0.0;
		if (!\App\Json::isEmpty($item['taxparam'] ?? '')) {
			$taxParam = \App\Json::decode($item['taxparam']);
			$aggregationType = $taxParam['aggregationType'];
			$returnVal = static::calculateFromField($this->getModuleName(), 'NetPrice', $item, $userFormat) * (float) ($taxParam["{$aggregationType}Tax"]) / 100.00;
		}
		return static::roundMethod((float) $returnVal);
	}
}
