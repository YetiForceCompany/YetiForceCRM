<?php

/**
 * Inventory MarginP Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MarginP_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'MarginP';
	protected $defaultLabel = 'LBL_MARGIN_PERCENT';
	protected $defaultValue = 0;
	protected $columnName = 'marginp';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $summationValue = true;
	protected $colSpan = 15;

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}

	public function getSummaryValuesFromData($data)
	{
		$sum = 0;
		if (is_array($data)) {
			foreach ($data as $row) {
				$purchase += $row['purchase'];
				$margin += $row['margin'];
			}
			if (!empty($purchase)) {
				$sum = ($margin / $purchase) * 100;
			}
		}
		return $sum;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$insertData[$column] = CurrencyField::convertToDBFormat($request->getByType($column . $i, 'NumberInUserFormat'), null, true);
	}
}
