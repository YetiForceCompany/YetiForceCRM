<?php

/**
 * Inventory Currency Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Currency_InnventoryField extends Vtiger_Basic_InnventoryField
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
		return Vtiger_Functions::getCurrencyName($value, false);
	}
}
