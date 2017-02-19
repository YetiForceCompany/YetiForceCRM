<?php
namespace App\TextParser;

/**
 * Table tax summary class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TableTaxSummary extends Base
{

	/** @var string Class name */
	public $name = 'LBL_TABLE_TAX_SUMMARY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$html = '';
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		$columns = $inventoryField->getColumns();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		if (in_array('currency', $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] != NULL) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbolRate = \vtlib\Functions::getCurrencySymbolandRate($currency);
		}
		$html .= '<style>' .
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;}' .
			'</style>';
		if (count($fields[0]) != 0) {
			$taxes = 0;
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$taxes = $inventoryField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
			}
			if (in_array('tax', $columns) && in_array('taxmode', $columns)) {
				$html .= '
						<table class="productTable colapseBorder">
							<thead>
								<tr>
									<th colspan="2" class="tBorder noBottomBorder tHeader">
										<strong>' . \App\Language::translate('LBL_TAX_SUMMARY', $this->textParser->moduleName) . '</strong>
									</th>
								</tr>
							</thead>
							<tbody>';
				foreach ($taxes as $key => &$tax) {
					$tax_AMOUNT += $tax;
					$html .= '<tr>
										<td class="textAlignRight tBorder" width="70px">' . $key . '%</td>
										<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr>
									<td class="textAlignRight tBorder" width="70px">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($tax_AMOUNT, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
								</tr>
							</tbody>
						</table>
					</div>';

				if (in_array('currency', $columns) && $baseCurrency['id'] != $currency) {
					$RATE = $baseCurrency['conversion_rate'] / $currencySymbolRate['rate'];
					$html .= '<br /><table class="pTable colapseBorder">
								<thead>
									<tr>
										<th colspan="2" class="tBorder noBottomBorder tHeader">
											<strong>' . \App\Language::translate('LBL_CURRENCIES_SUMMARY', $this->textParser->moduleName) . '</strong>
										</th>
									</tr>
								</thead>
								<tbody>';
					foreach ($taxes as $key => &$tax) {
						$currencyAmount += $tax;
						$html .= '<tr>
									<td class="textAlignRight tBorder" width="70px">' . $key . '%</td>
									<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
					}
					$html .= '<tr>
								<td class="textAlignRight tBorder" width="70px">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
								<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
							</tr>
						</tbody>
					</table>';
				}
			}
		}
		return $html;
	}
}
