<?php

namespace App\TextParser;

/**
 * Table tax two lang class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class TableTaxSTwoLang extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_TAX_S_TWO_LANG';

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
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$taxAmount = 0;
				$html .= '

						<table class="table-tax-s-two-lang" style="width:100%;border-collapse:collapse;border:1px solid #ddd;">
							<thead>
								<tr>
									<th colspan="2" style="padding:0px 4px;text-align:center;">' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</th>
								</tr>
							</thead>
							<tbody>';
				foreach ($taxes as $key => &$tax) {
					$taxAmount += $tax;
					$html .= '<tr>
								<td class="name" style="text-align:left;padding:0px 4px;">' . $key . '%</td>
								<td class="value" style="padding:0px 4px;text-align:right;">' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencySymbol . '</td>
							</tr>';
				}
				$html .= '<tr class="summary">
							<td class="name" style="padding:0px 4px;text-align:left;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</td>
							<td class="value" style="text-align:right;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($taxAmount, null, true) . ' ' . $currencySymbol . '</td>
						 </tr>
						</tbody>
					</table>';
			}
		}
		return $html;
	}
}
