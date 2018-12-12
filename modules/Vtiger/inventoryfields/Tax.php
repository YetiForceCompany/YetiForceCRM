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
		'taxparam' => 'string',
	];
	protected $summationValue = true;
	protected $maximumLength = '99999999999999999999';
	protected $customMaximumLength = [
		'taxparam' => 255
	];

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}

	public function getClassName($data)
	{
		if (count($data) > 0 && $data[0]['taxmode'] == 0) {
			return 'hide';
		}
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getByType($column . $i, 'NumberInUserFormat');
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
		$taxparam = $request->getArray('taxparam' . $i);
		if (isset($taxparam['individualTax'])) {
			$taxparam['individualTax'] = \App\Purifier::purifyByType($taxparam['individualTax'], 'NumberInUserFormat');
		}
		$value = \App\Json::encode($taxparam);
		$this->validate($value, 'taxparam', true);
		$insertData['taxparam'] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if ($columnName === $this->getColumnName()) {
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
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
	 * @param int        $net
	 * @param array|null $return
	 *
	 * @return array
	 */
	public function getTaxParam(string $taxParam, int $net, ?array $return = []): array
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
				$precent = $taxParam[$aggregationType . 'Tax'];
				if (!isset($return[$precent])) {
					$return[$precent] = 0;
				}
				$return[$precent] += $net * ($precent / 100);
			}
		}
		return $return;
	}
}
