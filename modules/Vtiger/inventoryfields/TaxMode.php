<?php

/**
 * Inventory TaxMode Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TaxMode_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'TaxMode';
	protected $defaultLabel = 'LBL_TAX_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'taxmode';
	protected $dbType = 'smallint(1) DEFAULT 0';
	protected $modes = [0 => 'LBL_INV_TAX_MODE_GLOBAL', 1 => 'LBL_INDIVIDUAL'];
	protected $blocks = [0];
	protected $maximumLength = '-32768,32767';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return '' !== $value && null !== $value ? \App\Language::translate($this->modes[$value], $this->getModuleName()) : '';
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!is_numeric($value) || !isset($this->modes[$value])) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function getEditValue(array $itemData, string $column = '')
	{
		$value = parent::getEditValue($itemData, $column);
		return is_numeric($value) ? $value : Vtiger_Inventory_Model::getTaxesConfig('default_mode');
	}

	/** {@inheritdoc} */
	public function compare($value, $prevValue, string $column): bool
	{
		return (int) $value === (int) $prevValue;
	}

	/**
	 * Get available modes.
	 *
	 * @return array
	 */
	public function getModes(): array
	{
		return $this->modes;
	}
}
