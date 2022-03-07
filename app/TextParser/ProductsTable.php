<?php

namespace App\TextParser;

/**
 * Products table class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTable extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE';

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
		$inventoryRows = [];
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$inventory = \Vtiger_Inventory_Model::getInstance($this->textParser->moduleName);
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		if ($inventory->isField('currency')) {
			$currency = $inventoryRows && $firstRow['currency'] ? $firstRow['currency'] : $baseCurrency['id'];
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		} else {
			$currencyData = \App\Fields\Currency::getDefault();
		}
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html = '<table  class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$groupModels = [];
		foreach (['Name', 'Quantity', 'Discount', 'Currency', 'DiscountMode', 'TaxMode', 'UnitPrice', 'GrossPrice', 'NetPrice', 'Tax', 'TotalPrice', 'Value'] as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				if (!$fieldModel->isVisible()) {
					continue;
				}
				if (\in_array($fieldModel->getType(), ['Currency', 'DiscountMode', 'TaxMode'])) {
					$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . ': ' . $fieldModel->getDisplayValue($firstRow[$columnName]) . '</th>';
				} else {
					$groupModels[$columnName] = $fieldModel;
				}
			}
		}
		$html .= '</tr></thead></table>';
		$html .= '<table class="products-table-header" style="border-collapse:collapse;width:100%;"><thead><tr>';
		foreach ($groupModels as $fieldModel) {
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
		}
		$html .= '</tr></thead>';
		if ($groupModels) {
			$html .= '<tbody>';
			foreach ($inventoryRows as $key => $inventoryRow) {
				$html .= '<tr>';
				foreach ($groupModels as $fieldModel) {
					$typeName = $fieldModel->getType();
					$itemValue = $inventoryRow[$fieldModel->getColumnName()];
					$styleField = $bodyStyle;
					if ('Name' === $typeName) {
						$fieldValue = "<strong>{$fieldModel->getDisplayValue($itemValue, $inventoryRow)}</strong>";
						foreach ($inventory->getFieldsByType('Comment') as $commentField) {
							$commentFieldName = $commentField->getColumnName();
							if ($inventory->isField($commentFieldName) && $commentField->isVisible() && ($value = $inventoryRow[$commentFieldName]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
								$fieldValue .= '<br />' . $comment;
							}
						}
					} elseif (\in_array($typeName, ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax'])) {
						$fieldValue = \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
						$styleField = $bodyStyle . ' text-align:right;';
					} else {
						$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
					}
					$html .= "<td class=\"col-type-{$typeName}\" style=\"{$styleField}\">{$fieldValue}</td>";
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($groupModels as $fieldModel) {
				$html .= "<th style=\"{$headerStyle}\">";
				if ($fieldModel->isSummary()) {
					$sum = 0;
					foreach ($inventoryRows as $key => $inventoryRow) {
						$sum += $inventoryRow[$fieldModel->getColumnName()];
					}
					$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
				}
				$html .= '</th>';
			}
			$html .= '</tr></tfoot></table>';
			$taxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			$html .= '<table class="products-table-summary" style="padding:10px 0px;border-collapse:collapse; font-family:\'Noto Sans\'; font-size:8px; margin:0px 0px 20px 0px; width:100%">
							<tr>
								<td style="vertical-align:top; width:50%">';
			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				$discount = $inventory->getField('discount')->getSummaryValuesFromData($inventoryRows);
				$html .= '<table class="products-table-summary-discount" style="width:100%;border-collapse:collapse;">
							<thead>
								<tr>
									<th style="text-align:center;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="border:1px solid #ddd;text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
			}
			$html .= '</td><td style="vertical-align:top">';
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$html .= '
						<table class="products-table-summary-tax" style="width:100%;border-collapse:collapse;">
							<thead>
								<tr>
									<th style="padding:0px 4px;font-weight:bold;" colspan="2">' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
							</thead>
							<tbody>';
				$tax_AMOUNT = 0;
				foreach ($taxes as $key => &$tax) {
					$tax_AMOUNT += $tax;
					$html .= '<tr>
										<td class="name" style="padding:0px 4px;">' . $key . '%</td>
										<td class="value" style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr class="summary" style="border:1px solid #ddd;">
									<td class="name" style="padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td class="value" style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax_AMOUNT, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
				if ($inventory->isField('currency') && $baseCurrency['id'] != $currency) {
					$RATE = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
					$html .= '<table class="products-table-summary-currency" style="width:100%;border-collapse:collapse;">
								<thead>
									<tr>
										<th style="padding:0px 4px;" colspan="2">' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . '</th>
									</tr>
								</thead>
								<tbody>';
					$currencyAmount = 0;
					foreach ($taxes as $key => &$tax) {
						$currencyAmount += $tax;
						$html .= '<tr>
									<td class="name" style="padding:0px 4px;">' . $key . '%</td>
									<td class="value" style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
					}
					$html .= '<tr class="summary" style="border:1px solid #ddd;">
								<td class="name" style="padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td class="value" style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
							</tr>
						</tbody>
					</table>';
				}
			}
			$html .= '</td></tr></table>';
		}
		return $html;
	}
}
