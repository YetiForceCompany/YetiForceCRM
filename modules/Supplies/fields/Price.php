<?php

/**
 * Supplies Price Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Price_Field extends Supplies_Basic_Field
{

	protected $name = 'Price';
	protected $defaultLabel = 'LBL_PRICE';
	protected $columnname = 'price';
	protected $dbType = 'decimal(27,8)';

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
