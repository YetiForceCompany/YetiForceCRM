<?php

/**
 * Supplies DiscountMode Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_DiscountMode_Field extends Supplies_Basic_Field
{

	protected $name = 'DiscountMode';
	protected $defaultLabel = 'LBL_DISCOUNT_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'discountmode';
	protected $dbType = "tinyint(1) NOT NULL DEFAULT '0'";
	protected $values = [0 => 'group', 1 => 'individual'];
	
	/**
	 * Geting value to display
	 * @param int $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return 'LBL_' . strtoupper($this->values[$value]);
	}
}
