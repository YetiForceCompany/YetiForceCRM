<?php

namespace App\TextParser;

/**
 * Products table short version class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableShortVersion extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_SHORT_VERSION';

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
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (\count($firstRow) > 0 && $firstRow['currency'] !== null) {
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
		if (count($fields[1]) != 0) {
			$fieldsColumnQuotes = ['Quantity', 'GrossPrice', 'Name', 'UnitPrice', 'TotalPrice'];
			$html .= '<table  border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getType(), $fieldsColumnQuotes)) {
					$html .= '<th style="width:' . $field->get('colSpan') . '%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || !in_array($field->getType(), $fieldsColumnQuotes)) {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td><strong>' . $inventoryRow['seq'] . '</strong></td>';
					} elseif ($field->getType() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td class="' . (in_array($field->getColumnName(), $fieldsColumnQuotes) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue, true) . '</strong>';
								foreach ($fields[2] as $commentKey => $value) {
									$COMMENT_FIELD = $fields[2][$commentKey];
									$html .= '<br />' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->getColumnName()]);
								}
								break;
							case 'DetailViewBase.tpl':
								if ($field->getType() === 'GrossPrice' || $field->getType() === 'UnitPrice' || $field->getType() === 'TotalPrice') {
									$html .= $field->getDisplayValue($itemValue) . ' ' . $currencySymbol;
								} elseif ($field->getType() === 'Quantity') {
									$html .= $field->getDisplayValue($itemValue);
								}
								break;
							default:
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getColumnName(), $fieldsColumnQuotes)) {
					$html .= '<td class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryContainer';
					}
					$html .= '">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as &$inventoryRow) {
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
