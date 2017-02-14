<?php

/**
 * Inventory TaxMode Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TaxMode_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'TaxMode';
	protected $defaultLabel = 'LBL_TAX_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'taxmode';
	protected $dbType = 'smallint(1) DEFAULT 0';
	protected $values = [0 => 'group', 1 => 'individual'];
	protected $blocks = [0];

	/**
	 * Getting value to display
	 * @param int $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		if ($value === '') {
			return '';
		}
		return 'LBL_' . strtoupper($this->values[$value]);
	}
}
