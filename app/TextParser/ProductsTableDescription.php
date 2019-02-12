<?php

namespace App\TextParser;

/**
 * Print descriptions from products table.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class ProductsTableDescription extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_DESCRIPTION';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$inventory = \Vtiger_Inventory_Model::getInstance($this->textParser->moduleName);
		$fields = $inventory->getFieldsByBlocks();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		foreach ($inventoryRows as $inventoryRow) {
			foreach ($fields[1] as $field) {
				if ($field->getColumnName() === 'name') {
					$html .= $field->getDisplayValue($inventoryRow[$field->getColumnName()]);
				}
			}
			$html .= $inventoryRow['comment1'];
		}
		return $html;
	}
}
