<?php

/**
 * Inventory MarginP Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
	public function getDisplayValue($value)
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
}
