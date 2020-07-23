<?php

/**
 * Inventory TaxCountMode Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Vtiger_TaxCountMode_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'TaxCountMode';
	protected $defaultLabel = 'LBL_TAX_COUNT_MODE';
	protected $defaultValue = 'netto';
	protected $columnName = 'taxcountmode';
	protected $dbType = 'varchar(255) DEFAULT 0';
	protected $values = ['brutto' => 'brutto', 'netto' => 'netto'];
	protected $blocks = [0];
	protected $maximumLength = '255';
	protected $purifyType = \App\Purifier::TEXT;

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return '' !== $value ? \App\Language::translate('LBL_COUNT_FROM_' . strtoupper($this->values[$value] ?? 'brutto'), $this->getModuleName()) : $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		return $value;
	}
}
