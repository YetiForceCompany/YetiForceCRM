<?php
namespace App\TextParser;

/**
 * Print descriptions from products table
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ProductsTableDescription extends Base
{

	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_DESCRIPTION';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		foreach ($inventoryRows as $inventoryRow) {
			foreach ($fields[1] as $field) {
				if ($field->get('columnname') === 'name') {
					$html .= $field->getDisplayValue($inventoryRow[$field->get('columnname')]);
				}
			}
			$html .= $inventoryRow['comment1'];
		}
		return $html;
	}
}
