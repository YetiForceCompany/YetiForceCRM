<?php

/**
 * Inventory NetPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_NetPrice_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'NetPrice';
	protected $defaultLabel = 'LBL_DISCOUNT_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'net';
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
