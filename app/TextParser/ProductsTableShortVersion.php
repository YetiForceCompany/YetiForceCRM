<?php

namespace App\TextParser;

/**
 * Products table short version class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
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
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		$currencyColumns = $inventoryField->getColumns();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();

		if (in_array('currency', $currencyColumns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] !== null) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbolRate = \vtlib\Functions::getCurrencySymbolandRate($currency);
			$currencySymbol = $currencySymbolRate['symbol'];
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
				if ($field->isVisible() && in_array($field->getName(), $fieldsColumnQuotes)) {
					$html .= '<th style="width:' . $field->get('colspan') . '%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$html .= '<tr>';

				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || !in_array($field->getName(), $fieldsColumnQuotes)) {
						continue;
					}
					if ($field->getName() == 'ItemNumber') {
						$html .= '<td><strong>' . $inventoryRow['seq'] . '</strong></td>';
					} elseif ($field->get('columnname') == 'ean') {
						$code = $inventoryRow[$field->get('columnname')];
						$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->get('columnname')];

						$html .= '<td class="' . (in_array($field->getName(), $fieldsColumnQuotes) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue, true) . '</strong>';
								foreach ($fields[2] as $commentKey => $value) {
									$COMMENT_FIELD = $fields[2][$commentKey];
									$html .= '<br />' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->get('columnname')]);
								}
								break;
							case 'DetailViewBase.tpl':
								if ($field->getName() === 'GrossPrice' || $field->getName() === 'UnitPrice' || $field->getName() === 'TotalPrice') {
									$html .= $field->getDisplayValue($itemValue) . ' ' . $currencySymbol;
								} elseif ($field->getName() == 'Quantity') {
									$html .= $field->getDisplayValue($itemValue);
								}
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getName(), $fieldsColumnQuotes)) {
					$html .= '<td class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryContainer';
					}
					$html .= '">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => &$inventoryRow) {
							$sum += $inventoryRow[$field->get('columnname')];
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
