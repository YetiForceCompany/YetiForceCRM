<?php

namespace App\TextParser;

/**
 * Products table new class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTableNew extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_NEW';

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
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && $firstRow['currency'] !== null) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		$html .= '<style>' .
			'.productTable{color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable .tHeader {background:#ddd, text-transform: capitalize !important;}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;padding:5px}' .
			'.barcode {padding: 1.5mm;margin: 0;vertical-align: top;color: #000000}' .
			'</style>';
		if (!empty($fields[1])) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table  border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="width:' . $field->get('colSpan') . '%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			$counter = 1;
			foreach ($inventoryRows as $inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible()) {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td><strong>' . $counter++ . '</strong></td>';
					} elseif ($field->getColumnName() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} else {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td class="' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						if ($field->getType() === 'Name') {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$html .= '<br />' . $commentField->getDisplayValue($value, $inventoryRow);
								}
							}
						} elseif ($field->getType() === 'Quantity') {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow);
						} else {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow) . ' ' . $currencySymbol;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<td class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryContainer';
					}
					$html .= '">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true) . ' ' . $currencySymbol;
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr>
					</tfoot>
				</table>';
		}
		return $html;
	}
}
