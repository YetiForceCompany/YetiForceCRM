<?php

/**
 * Inventory UnitPrice Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_UnitPrice_InnventoryField extends Vtiger_Basic_InnventoryField
{

	protected $name = 'UnitPrice';
	protected $defaultLabel = 'LBL_UNIT_PRICE';
	protected $columnName = 'price';
	protected $dbType = 'decimal(27,8)';
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
