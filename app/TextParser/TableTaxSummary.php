<?php

namespace App\TextParser;

/**
 * Table tax summary class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TableTaxSummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_TAX_SUMMARY';

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
			if (!empty($firstRow) && $firstRow['currency'] !== null) {
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
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$taxAmount = 0;
				$html .= '
						<table style="width:100%;vertical-align:top;border-collapse:collapse;border:1px solid #ddd;">
						<thead>
								<tr>
									<th colspan="2" style="font-weight:bold;padding:0px 4px;">' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
								</thead><tbody>';
				foreach ($taxes as $key => &$tax) {
					$taxAmount += $tax;
					$html .= '<tr>
										<td style="text-align:left;padding:0px 4px;">' . $key . '%</td>
										<td style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr>
									<td style="text-align:left;font-weight:bold;padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td style="text-align:right;font-weight:bold;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($taxAmount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
								</tbody>
						</table>';
			}
		}
		return $html;
	}
}
