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
		if (count($fields[1]) != 0) {
			$visibleColumns = ['Quantity', 'GrossPrice', 'Name', 'UnitPrice', 'TotalPrice'];
			$fieldsColumnQuotes = ['Quantity', 'GrossPrice', 'UnitPrice', 'TotalPrice'];
			$html .= '<table style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getType(), $visibleColumns)) {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr></thead><tbody>';
			foreach ($inventoryRows as &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || !in_array($field->getType(), $visibleColumns)) {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;"><strong>' . $inventoryRow['seq'] . '</strong></td>';
					} elseif ($field->getColumnName() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;"><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;' . (in_array($field->getType(), $fieldsColumnQuotes) ? 'text-align:right;' : '') . '">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue, [], true) . '</strong>';
								foreach ($fields[2] as $commentKey => $value) {
									$COMMENT_FIELD = $fields[2][$commentKey];
									$comment = $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->getColumnName()]);
									if ($comment) {
										$html .= '<br />' . $comment;
									}
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
				if ($field->isVisible() && in_array($field->getType(), $visibleColumns)) {
					$html .= '<th style="padding:0px 4px;text-align:right;">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as &$inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true) . ' ' . $currencySymbol;
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
