<?php

namespace App\TextParser;

/**
 * Table currency two lang class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class TableCurrencySTwoLang extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_CURRENCY_S_TWO_LANG';

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
			if (!empty($firstRow) > 0 && $firstRow['currency'] !== null) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
		}
		if (!empty($fields[0])) {
			$taxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			if (!empty($currency) && !empty($currencyData) && $baseCurrency['id'] !== $currency && $inventory->isField('tax') && $inventory->isField('taxmode') && $inventory->isField('currency')) {
				$RATE = $baseCurrency['conversion_rate'] / $currencyData['conversion_rate'];
				$html .= '<table style="border-collapse:collapse;width:100%;">
								<thead>
									<tr>
										<th colspan="2" style="padding:0px 4px;text-align:center;">' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</th>
									</tr>
								</thead>
								<tbody>';
				$currencyAmount = 0;
				foreach ($taxes as $key => &$tax) {
					$currencyAmount += $tax;

					$html .= '<tr>
									<td style="padding:0px 4px;text-align:right;">' . $key . '%</td>
									<td style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
				}
				$html .= '<tr>
								<td style="text-align:right;padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</td>
								<td style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
							</tr>
						</tbody>
					</table>';
			}
		}
		return $html;
	}
}
