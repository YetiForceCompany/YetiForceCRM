<?php

/**
 * Inventory Quantity Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Quantity_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Quantity';
	protected $defaultLabel = 'LBL_QUANTITY';
	protected $defaultValue = '1';
	protected $columnName = 'qty';
	protected $dbType = 'decimal(25,3) DEFAULT 0';
	protected $customColumn = [
		'qtyparam' => 'smallint(1) DEFAULT 0',
	];

	/**
	 * Getting value to display.
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Purifier::encodeHtml(vtlib\Functions::formatDecimal($value));
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
		$insertData[$column] = CurrencyField::convertToDBFormat($request->getByType($column . $i, 'NumberInUserFormat'), null, true);
		$insertData['qtyparam'] = $request->isEmpty('qtyparam' . $i, true) ? 0 : $request->getInteger('qtyparam' . $i);
	}
}
