<?php

/**
 * Special function displaying products table
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Pdf_TableTaxSummary extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['all'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$html = '';
		$recordId = $id;
		$record = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleModel = $record->getModule();
		if (!$moduleModel->isInventory()) {
			return $html;
		}
		$inventoryField = Vtiger_InventoryField_Model::getInstance($module);
		$fields = $inventoryField->getFields(true);

		$columns = $inventoryField->getColumns();
		$inventoryRows = $record->getInventoryData();
		$baseCurrency = Vtiger_Util_Helper::getBaseCurrency();

		if (in_array("currency", $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] != NULL) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbolRate = vtlib\Functions::getCurrencySymbolandRate($currency);
		}
		$html .=
			'<style>' .
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: uppercase;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ccc;}' .
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
										<strong>' . vtranslate('LBL_TAX_SUMMARY', $module) . '</strong>
									</th>
								</tr>
							</thead>
							<tbody>';
				foreach ($taxes as $key => &$tax) {
					$tax_AMOUNT += $tax;
					$html .= '<tr>
										<td class="textAlignRight tBorder" width="70px">' . $key . '%</td>
										<td class="textAlignRight tBorder">' . CurrencyField::convertToUserFormat($tax, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
									</tr>';
				}
				$html .= '<tr>
									<td class="textAlignRight tBorder" width="70px">' . vtranslate('LBL_AMOUNT', $module) . '</td>
									<td class="textAlignRight tBorder">' . CurrencyField::convertToUserFormat($tax_AMOUNT, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
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
											<strong>' . vtranslate('LBL_CURRENCIES_SUMMARY', $module) . '</strong>
										</th>
									</tr>
								</thead>
								<tbody>';
					foreach ($taxes as $key => &$tax) {
						$currencyAmount += $tax;
						$html .= '<tr>
									<td class="textAlignRight tBorder" width="70px">' . $key . '%</td>
									<td class="textAlignRight tBorder">' . CurrencyField::convertToUserFormat($tax * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
								</tr>';
					}
					$html .= '<tr>
								<td class="textAlignRight tBorder" width="70px">' . vtranslate('LBL_AMOUNT', $module) . '</td>
								<td class="textAlignRight tBorder">' . CurrencyField::convertToUserFormat($currencyAmount * $RATE, null, true) . ' ' . $baseCurrency['currency_symbol'] . '</td>
							</tr>
						</tbody>
					</table>';
				}
			}
		}
		return $html;
	}
}
