<?php

namespace App\TextParser;

/**
 * Products table class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$fields = $inventory->getFieldsByBlocks();
		if (isset($fields[0])) {
			$inventoryRows = $this->textParser->recordModel->getInventoryData();
			$firstRow = current($inventoryRows);
			$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
			if ($inventory->isField('currency')) {
				$currency = $inventoryRows && $firstRow['currency'] ? $firstRow['currency'] : $baseCurrency['id'];
				$currencyData = \App\Fields\Currency::getById($currency);
			}
		}
		if (isset($fields[0])) {
			$html .= '<table style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>
						<th></th>';
			foreach ($fields[0] as $field) {
				$html .= '<th style="text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ': ' . $field->getDisplayValue($firstRow[$field->getColumnName()]) . '</th>';
			}
			$html .= '</tr>
				</thead>
			</table>';
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as $key => $inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="font-size:8px;border:1px solid #ddd;padding:0px 4px;' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						if ($field->getType() === 'Name') {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$comment = $commentField->getDisplayValue($value, $inventoryRow);
									if ($comment) {
										$html .= '<br />' . $comment;
									}
								}
							}
						} else {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow);
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:right;">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => $inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';

			$taxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			$html .= '<table style="padding:10px 0px;border-collapse:collapse; font-family:\'Noto Sans\'; font-size:8px; margin:0px 0px 20px 0px; width:100%">
							<tr>
								<td style="vertical-align:top; width:50%">';

			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				$discount = $inventory->getField('discount')->getSummaryValuesFromData($inventoryRows);
				$html .= '<table style="width:100%;border-collapse:collapse;">
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
						<table style="width:100%;border-collapse:collapse;">
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
										<td style="padding:0px 4px;">' . $key . '%</td>
										<td style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr style="border:1px solid #ddd;">
									<td style="padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax_AMOUNT, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>
					</div>';

				if ($inventory->isField('currency') && $baseCurrency['id'] != $currency) {
					$RATE = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
					$html .= '<table>
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
									<td style="padding:0px 4px;">' . $key . '%</td>
									<td style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
					}
					$html .= '<tr style="border:1px solid #ddd;">
								<td style="padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
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
