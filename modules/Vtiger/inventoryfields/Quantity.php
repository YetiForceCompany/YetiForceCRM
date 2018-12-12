<?php

/**
 * Inventory Quantity Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
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
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getByType($column . $i, 'NumberInUserFormat');
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
		$value = $request->isEmpty('qtyparam' . $i, true) ? 0 : $request->getInteger('qtyparam' . $i);
		$this->validate($value, $column, true);
		$insertData['qtyparam'] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if (App\TextParser::getTextLength($value) > $this->maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . ($columnName ?? $this->getColumnName()) . "||$value", 406);
		}
	}
}
