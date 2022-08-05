<?php

namespace App\TextParser;

/**
 * Table currency summary class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class TableCurrencySummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_CURRENCY_SUMMARY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$html = '';
		$inventory = \Vtiger_Inventory_Model::getInstance($this->textParser->moduleName);
		$fields = $inventory->getFieldsByBlocks();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			if (isset($firstRow['currencyparam'])) {
				$currencyParam = \App\Json::decode($firstRow['currencyparam']);
				if (isset($currencyParam[$currency])) {
					$rate = $currencyParam[$currency]['value'] ?? 0;
				}
			} else {
				$currencyData = \App\Fields\Currency::getById($currency);
				$rate = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
			}
		}
		if (!empty($fields[0])) {
			$taxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			if (!empty($currency) && !empty($rate) && $baseCurrency['id'] !== $currency && $inventory->isField('tax') && $inventory->isField('taxmode') && $inventory->isField('currency')) {
				$html .= '<table class="table-currency-summary" style="border-collapse:collapse;width:100%;border:1px solid #ddd;">
								<thead>
									<tr>
										<th colspan="2" style="padding:0px 4px;text-align:center;">
											<strong>' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . '</strong>
										</th>
									</tr>
								</thead>
								<tbody>';
				$currencyAmount = 0;
				foreach ($taxes as $key => &$tax) {
					$currencyAmount += $tax;
					$html .= '<tr>
									<td class="name" style="padding:0px 4px;">' . $key . '%</td>
									<td class="value" style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormatSymbol($tax * $rate, true, $baseCurrency['currency_symbol']) . ' </td>
								</tr>';
				}
				$html .= '<tr class="summary">
								<td class="name" style="padding:0px 4px;font-weight:bold;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td class="value" style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormatSymbol($currencyAmount * $rate, true, $baseCurrency['currency_symbol']) . ' </td>
							</tr>
						</tbody>
					</table>';
			}
		}
		return $html;
	}
}
