<?php

/**
 * Inventory GrossPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_GrossPrice_InnventoryField extends Vtiger_Basic_InnventoryField
{

	protected $name = 'GrossPrice';
	protected $defaultLabel = 'LBL_GROSS_PRICE';
	protected $columnName = 'gross';
	protected $dbType = 'decimal(27,8) DEFAULT \'0\'';
	protected $summationValue = true;
	
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
