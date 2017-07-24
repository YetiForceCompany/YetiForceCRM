<?php

/**
 * Inventory NetPrice Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_NetPrice_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'NetPrice';
	protected $defaultLabel = 'LBL_DISCOUNT_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'net';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
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
