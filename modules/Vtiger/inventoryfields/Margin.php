<?php

/**
 * Inventory Margin Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Margin_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Margin';
	protected $defaultLabel = 'LBL_MARGIN';
	protected $defaultValue = 0;
	protected $columnName = 'margin';
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
