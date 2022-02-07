<?php

/**
 * Tax percent field.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Inventory TaxPercent Field Class.
 */
class Vtiger_TaxPercent_InventoryField extends Vtiger_Tax_InventoryField
{
	protected $type = 'TaxPercent';
	protected $defaultLabel = 'LBL_TAX_IN_PERCENT';
	protected $defaultValue = 0;
	protected $summationValue = false;
	protected $columnName = 'tax_percent';
	protected $dbType = 'decimal(12,8) DEFAULT 0';
	protected $maximumLength = '9999';
	protected $purifyType = \App\Purifier::NUMBER;
	/**
	 * @var array List of shared fields
	 */
	public $shared = ['taxparam' => 'tax'];

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return App\Fields\Double::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getValueForSave(array $item, bool $userFormat = false, string $column = null)
	{
		if ($column === $this->getColumnName() || null === $column) {
			$value = 0.0;
			if (!\App\Json::isEmpty($item['taxparam'] ?? '')) {
				$taxParam = \App\Json::decode($item['taxparam']);
				$types = (array) $taxParam['aggregationType'];
				foreach ($types as $type) {
					$value += $taxParam["{$type}Tax"];
				}
			}
		} else {
			$value = $userFormat ? $this->getDBValue($item[$column]) : $item[$column];
		}
		return $value;
	}
}
