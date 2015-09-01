<?php

/**
 * Inventory Discount Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Discount_InnventoryField extends Vtiger_Basic_InnventoryField
{

	protected $name = 'Discount';
	protected $defaultLabel = 'LBL_DISCOUNT';
	protected $columnName = 'discount';
	protected $dbType = 'decimal(27,8) DEFAULT \'0\'';
	protected $customColumn = [
		'discountparam' => 'varchar(255) NOT NULL'
	];
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
