<?php

namespace App\TextParser;

/**
 * Table tax summary class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbol = \App\Fields\Currency::getById($currency)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		if (!empty($fields[0])) {
			$taxes = [];
			if ($inventory->isField('tax') && ($inventory->isField('net') || $inventory->isField('total'))) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'] ?? $inventoryRow['total'], $taxes);
				}
			}
			if (\in_array('showNames', $this->params)) {
				foreach (\Vtiger_Inventory_Model::getGlobalTaxes() as $gt) {
					$key = (string) (float) $gt['value'];
					if (isset($taxes[$key])) {
						$taxes[$gt['name'] . ': ' . $key] = $taxes[$key];
						unset($taxes[$key]);
					}
				}
			}
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$taxAmount = 0;
				$html .= '<table class="table-tax-summary" style="width:100%;vertical-align:top;border-collapse:collapse;border:1px solid #ddd;">
						<thead>
							<tr>
								<th colspan="2" style="font-weight:bold;padding:0px 4px;background-color:#ddd;">' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . '</th>
							</tr>
						</thead><tbody>';
				foreach ($taxes as $key => &$tax) {
					$taxAmount += $tax;
					$html .= '<tr>
								<td class="name" style="text-align:left;padding:0px 4px;">' . $key . '%</td>
								<td class="value" style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormatSymbol($tax, true, $currencySymbol) . ' </td>
							</tr>';
				}
				$html .= '<tr class="summary">
						<td class="name" style="text-align:left;font-weight:bold;padding:0px 4px;border:1px solid #ddd;border-right:0;">' . (\in_array('hideSumName', $this->params) ? '' : \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName)) . '</td>
						<td class="value" style="text-align:right;font-weight:bold;padding:0px 4px;border:1px solid #ddd;border-left:0;">' . \CurrencyField::convertToUserFormatSymbol($taxAmount, true, $currencySymbol) . ' </td>
					</tr>
				</tbody>
			</table>';
			}
		}
		return $html;
	}
}
