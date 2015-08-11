<?php

/**
 * Supplies Quantity Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Quantity_Field extends Supplies_Basic_Field
{

	protected $name = 'Quantity';
	protected $defaultLabel = 'LBL_QUANTITY';
	protected $defaultValue = '1';
	protected $columnname = 'qty';
	protected $dbType = 'decimal(25,3)';
	
	/**
	 * Geting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		return Vtiger_Functions::formatDecimal($value);
	}
}
