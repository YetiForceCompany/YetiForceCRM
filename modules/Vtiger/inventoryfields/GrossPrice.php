<?php

/**
 * Inventory GrossPrice Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_GrossPrice_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'GrossPrice';
	protected $defaultLabel = 'LBL_GROSS_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'gross';
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
