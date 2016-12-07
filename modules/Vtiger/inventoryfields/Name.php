<?php

/**
 * Inventory Name Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Name_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int DEFAULT 0';
	protected $params = ['modules', 'limit'];
	protected $colSpan = 30;

	/**
	 * Getting value to display
	 * @param type $value
	 * @return type
	 */
	public function getDisplayValue($value)
	{
		if ($value != 0)
			return vtlib\Functions::getCRMRecordLabel($value);
		return '';
	}

	/**
	 * Getting value to display
	 * @return array
	 */
	public function limitValues()
	{
		return [
				['id' => 0, 'name' => 'LBL_NO'],
				['id' => 1, 'name' => 'LBL_YES']
		];
	}

	public function getConfig()
	{
		return \App\Json::decode($this->get('params'));
	}
}
