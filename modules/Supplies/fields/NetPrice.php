<?php

/**
 * Supplies NetPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_NetPrice_Field extends Supplies_Basic_Field
{

	protected $name = 'NetPrice';
	protected $defaultLabel = 'LBL_NET_PRICE';
	protected $columnname = 'net';
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
