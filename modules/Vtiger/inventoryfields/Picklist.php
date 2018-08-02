<?php

/**
 * Inventory Picklist Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Picklist_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Picklist';
	protected $defaultLabel = 'LBL_PICKLIST';
	protected $columnName = 'picklist';
	protected $onlyOne = false;

	public function getPicklistValues()
	{
		$values = [];
		$params = $this->getParamsConfig();
		if (isset($params['values'])) {
			$values = $params['values'];
		}
		return $values;
	}
}
