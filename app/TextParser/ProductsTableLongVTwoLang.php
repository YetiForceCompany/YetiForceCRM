<?php

namespace App\TextParser;

/**
 * Products table long two lang class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableLongVTwoLang extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_LONG_TWO_LANG';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleName = $this->textParser->moduleName;
		$productsTable = new ProductsTableLongVersion($this->textParser);
		$html = $productsTable->getTableStyle($this->textParser->recordModel->getModule()->isInventory());
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$currencySymbol = $productsTable->getSymbol($inventoryField, $inventoryRows);
		if (count($fields[1]) != 0) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table  border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && ($field->get('columnname') !== 'subunit')) {
					if ($field->getName() === 'Quantity' || $field->getName() === 'Value') {
						$html .= '<th style="width: 8%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '/ ' . \App\Language::translate($field->get('label'), $moduleName, 'en_us') . '</th>';
					} elseif ($field->getName() === 'Name') {
						$html .= '<th style="width:' . $field->get('colspan') . '%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '/ ' . \App\Language::translate($field->get('label'), $moduleName, 'en_us') . '</th>';
					} else {
						$html .= '<th style="width: 13%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '/ ' . \App\Language::translate($field->get('label'), $moduleName, 'en_us') . '</th>';
					}
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			$html = $productsTable->getTableBody($inventoryRows, $fields, $fieldsTextAlignRight, $moduleName, $html, $currencySymbol);
			$html .=  '</tbody><tfoot><tr>';
			$html = $productsTable->getTableFoot($inventoryRows, $fields, $html, $currencySymbol);
			$html .=  '</tr></tfoot></table>';
		}
		return $html;
	}
}
