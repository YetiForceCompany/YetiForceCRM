<?php

/**
 * Supplies Total Price Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_TotalPrice_Field extends Supplies_Basic_Field
{

	protected $name = 'TotalPrice';
	protected $defaultLabel = 'LBL_TOTAL_PRICE';
	protected $columnname = 'total';
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
