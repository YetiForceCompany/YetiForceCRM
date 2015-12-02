<?php

/**
 * Inventory MarginP Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_MarginP_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'MarginP';
	protected $defaultLabel = 'LBL_MARGIN_PERCENT';
	protected $defaultValue = 0;
	protected $columnName = 'marginp';
	protected $dbType = 'decimal(27,8) DEFAULT 0';

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
