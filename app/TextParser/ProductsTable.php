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
			$html .= '<table class="pTable colapseBorder">
				<thead>
					<tr>
						<th style="width: 60%;"></th>';
			foreach ($fields[0] as $field) {
				$html .= '<th style="' . $field->get('colspan') . '%;">
								<span>' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ':</span> ';
				switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
					case 'DetailViewBase.tpl':
						$html .= $field->getDisplayValue($firstRow[$field->getColumnName()]);
						break;
					case 'DetailViewTaxMode.tpl':
					case 'DetailViewDiscountMode.tpl':
						$html .= \App\Language::translate($field->getDisplayValue($firstRow[$field->getColumnName()]), $this->textParser->moduleName);
						break;
					default:
						break;
				}
				$html .= '</th>';
			}
			$html .= '</tr>
				</thead>
			</table>';

			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table class="pTable colapseBorder">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="' . $field->get('colspan') . '%; text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';

			foreach ($inventoryRows as $key => &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];

						$html .= '<td ' . ($field->getType() == 'Name' ? 'width="40%;" ' : '') . ' class="' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								foreach ($inventory->getFieldsByType('Comment') as $commentField) {
									if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
										$html .= '<br />' . $commentField->getDisplayValue($value);
									}
								}
								break;
							case 'DetailViewBase.tpl':
								$html .= $field->getDisplayValue($itemValue);
								break;
							default:
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody>
					<tfoot>
						<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<td style="text-align:right">';

					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => &$inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr>
					</tfoot>
				</table>';

			$taxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => &$inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}

			$html .= '<br /><table width="100%" style="vertical-align: top; text-align: center;">
							<tr>
								<td>';

			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				$discount = $inventory->getField('discount')->getSummaryValuesFromData($inventoryRows);
				$html .= '<table class="pTable colapseBorder">
							<thead>
								<tr>
									<th>' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td style="text-align:right">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
			}

			$html .= '</td><td>';
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$html .= '
						<table>
							<thead>
								<tr>
									<th colspan="2">
										<strong>' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . '</strong>
									</th>
								</tr>
							</thead>
							<tbody>';
				$tax_AMOUNT = 0;
				foreach ($taxes as $key => &$tax) {
					$tax_AMOUNT += $tax;
					$html .= '<tr>
										<td>' . $key . '%</td>
										<td>' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr>
									<td>' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td>' . \CurrencyField::convertToUserFormat($tax_AMOUNT, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>
					</div>';

				if ($inventory->isField('currency') && $baseCurrency['id'] != $currency) {
					$RATE = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
					$html .= '<br /><table>
								<thead>
									<tr>
										<th colspan="2">
											<strong>' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . '</strong>
										</th>
									</tr>
								</thead>
								<tbody>';
					$currencyAmount = 0;
					foreach ($taxes as $key => &$tax) {
						$currencyAmount += $tax;
						$html .= '<tr>
									<td>' . $key . '%</td>
									<td>' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
					}
					$html .= '<tr>
								<td>' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td>' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
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
