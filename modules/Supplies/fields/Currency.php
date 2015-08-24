<?php

/**
 * Supplies Currency Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Currency_Field extends Supplies_Basic_Field
{

	protected $name = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnName = 'currency';
	protected $dbType = 'int(10)';

	/**
	 * Geting value to display
	 * @param int $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return Vtiger_Functions::getCurrencyName($value,false);
	}
}
