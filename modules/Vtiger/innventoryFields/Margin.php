<?php

/**
 * Inventory Margin Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Margin_InnventoryField extends Vtiger_Basic_InnventoryField
{

	protected $name = 'Margin';
	protected $defaultLabel = 'LBL_MARGIN';
	protected $columnName = 'margin';
	protected $dbType = 'decimal(27,8) DEFAULT \'0\'';
	
	/**
	 * Geting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}
}
