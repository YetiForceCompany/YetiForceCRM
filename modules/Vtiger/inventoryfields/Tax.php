<?php

/**
 * Inventory Tax Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Tax_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Tax';
	protected $defaultLabel = 'LBL_TAX';
	protected $defaultValue = 0;
	protected $columnName = 'tax';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $customColumn = [
		'taxparam' => 'string'
	];
	protected $summationValue = true;

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		return CurrencyField::convertToUserFormat($value, null, true);
	}

	public function getClassName($data)
	{
		if (count($data) > 0 && $data[0]['taxmode'] == 0) {
			return 'hide';
		}
		return '';
	}
}
