<?php

/**
 * Inventory Picklist Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Picklist_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Picklist';
	protected $defaultLabel = 'LBL_PICKLIST';
	protected $columnName = 'picklist';
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::TEXT;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Picklist.tpl';
	}

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
