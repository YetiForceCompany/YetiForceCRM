<?php

/**
 * Inventory GrossPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_GrossPrice_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'GrossPrice';
	protected $defaultLabel = 'LBL_GROSS_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'gross';
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
