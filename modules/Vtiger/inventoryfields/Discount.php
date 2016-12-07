<?php

/**
 * Inventory Discount Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Discount_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Discount';
	protected $defaultLabel = 'LBL_DISCOUNT';
	protected $defaultValue = 0;
	protected $columnName = 'discount';
	protected $dbType = 'decimal(27,8) DEFAULT 0';
	protected $customColumn = [
		'discountparam' => 'string'
	];
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
