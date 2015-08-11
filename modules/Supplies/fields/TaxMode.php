<?php

/**
 * Supplies TaxMode Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_TaxMode_Field extends Supplies_Basic_Field
{

	protected $name = 'TaxMode';
	protected $defaultLabel = 'LBL_TAX_MODE';
	protected $defaultValue = '0';
	protected $columnname = 'taxmode';
	protected $dbType = "tinyint(1) NOT NULL DEFAULT '0'";
	protected $values = [0 => 'group', 1 => 'individual'];
	
	/**
	 * Geting value to display
	 * @param int $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return $this->values[$value];
	}
}
