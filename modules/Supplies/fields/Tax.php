<?php

/**
 * Supplies Tax Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Tax_Field extends Supplies_Basic_Field
{

	protected $name = 'Tax';
	protected $defaultLabel = 'LBL_TAX';
	protected $defaultValue = 'tax';
	protected $columnname = 'tax';
	protected $dbType = 'decimal(27,8)';
	protected $customColumn = [
		'taxtype' => 'int(19)'
	];
	
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
