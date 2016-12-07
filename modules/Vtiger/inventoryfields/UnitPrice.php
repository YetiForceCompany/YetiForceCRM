<?php

/**
 * Inventory UnitPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_UnitPrice_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'UnitPrice';
	protected $defaultLabel = 'LBL_UNIT_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'price';
	protected $dbType = 'decimal(27,8) DEFAULT 0';
	protected $summationValue = true;

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}
}
