<?php

namespace App\TextParser;

/**
 * Products table long version class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableLongVersion extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_LONG_VERSION';

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
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbol = \App\Fields\Currency::getById($currency)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-long-version" style="width:100%;font-size:8px;border-collapse:collapse;">
				<thead>
					<tr>';
		$groupModels = [];
		foreach (['ItemNumber', 'Name', 'Value', 'Quantity', 'UnitPrice', 'TotalPrice', 'Discount', 'NetPrice', 'Tax', 'GrossPrice']  as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				if (!$fieldModel->isVisible()) {
					continue;
				}
				$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
				$groupModels[$fieldModel->getColumnName()] = $fieldModel;
			}
		}
		$html .= '</tr></thead>';
		if (!empty($groupModels)) {
			$html .= '<tbody>';
			$counter = 0;
			foreach ($inventoryRows as $inventoryRow) {
				++$counter;
				$html .= '<tr class="row-' . $counter . '">';
				foreach ($groupModels as $fieldModel) {
					$columnName = $fieldModel->getColumnName();
					$typeName = $fieldModel->getType();
					$fieldStyle = $bodyStyle;
					if ('ItemNumber' === $typeName) {
						$html .= "<td style=\"{$bodyStyle}font-weight:bold;\">" . $counter++ . '</td>';
					} elseif ('ean' === $columnName) {
						$code = $inventoryRow[$columnName];
						$html .= "<td class=\"col-type-barcode\" style=\"{$fieldStyle}font-weight:bold;text-align:center;\"><div data-barcode=\"EAN13\" data-code=\"{$code}\" data-size=\"1\" data-height=\"16\">{$code}</div></td>";
					} else {
						$itemValue = $inventoryRow[$columnName];
						if ('Name' === $typeName) {
							$fieldValue = '<strong>' . $fieldModel->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
									$fieldValue .= '<br />' . $comment;
								}
							}
						} elseif (\in_array($typeName, ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax']) && !empty($currencySymbol)) {
							$fieldValue = \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
							$fieldStyle = $bodyStyle . 'text-align:right;';
						} else {
							$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
						}
						$html .= "<td class=\"col-type-{$typeName}\" style=\"{$fieldStyle}\">{$fieldValue}</td>";
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($groupModels as $fieldModel) {
				$html .= "<th class=\"col-type-{$typeName}\" style=\"{$headerStyle}\">";
				if ($fieldModel->isSummary()) {
					$sum = 0;
					foreach ($inventoryRows as $inventoryRow) {
						$sum += $inventoryRow[$fieldModel->getColumnName()];
					}
					$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
				}
				$html .= '</th>';
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
