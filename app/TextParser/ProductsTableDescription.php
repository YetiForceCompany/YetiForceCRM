<?php

namespace App\TextParser;

/**
 * Print descriptions from products table.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$field = $inventory->getField('name');
		if ($field->isVisible()) {
			$comments = $inventory->getFieldsByType('Comment');
			$inventoryRows = $this->textParser->recordModel->getInventoryData();
			foreach ($inventoryRows as $inventoryRow) {
				$html .= $field->getDisplayValue($inventoryRow[$field->getColumnName()], $inventoryRow);
				foreach ($comments as $fieldComment) {
					if ($fieldComment->isVisible() && ($value = $inventoryRow[$fieldComment->getColumnName()]) && ($comment = $fieldComment->getDisplayValue($value, $inventoryRow))) {
						$html .= '<br />' . $comment;
					}
				}
			}
		}
		return $html;
	}
}
