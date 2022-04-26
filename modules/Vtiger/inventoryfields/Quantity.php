<?php

/**
 * Inventory Quantity Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Quantity_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Quantity';
	protected $defaultLabel = 'LBL_QUANTITY';
	protected $defaultValue = '1';
	protected $columnName = 'qty';
	protected $dbType = 'decimal(25,3) DEFAULT 0';
	protected $maximumLength = '9999999999999999999999';
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
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($this->maximumLength < $value || -$this->maximumLength > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
