<?php

/**
 * Special function displaying products table
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Pdf_ProductsTable extends Vtiger_SpecialFunction_Pdf
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

		if ($fields[0] != 0) {
			$columns = $inventoryField->getColumns();
			$inventoryRows = $record->getInventoryData();
			$mainParams = $inventoryField->getMainParams($fields[1]);
			$countFields0 = count($fields[0]);
			$countFields1 = count($fields[1]);
			$countFields2 = count($fields[2]);
			$baseCurrency = Vtiger_Util_Helper::getBaseCurrency();
		}

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
			'.colapseBorder {border-collapse: collapse;}' .
			'.tBorder {border: 1px solid grey;}' .
			'.tHeader {background-color: lightgrey;}' .
			'.summaryBorder {border-left: 1px solid grey; border-bottom: 1px solid grey; border-right: 1px solid grey;}' .
			'.pTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.noBottomBorder {border-bottom: none;}' .
			'.noBorder {border: none !important;}' .
			'</style>';
		if (count($fields[0]) != 0) {
			$html .= '<table class="pTable colapseBorder">
				<thead>
					<tr>
						<th style="width: 60%;"></th>';
			foreach ($fields[0] as $field) {
				$html .= '<th style="' . $field->get('colspan') . '%;" class="tBorder noBottomBorder tHeader">
								<span>' . vtranslate($field->get('label'), $module) . ':</span>&nbsp;';
				switch ($field->getTemplateName('DetailView', $module)) {
					case 'DetailViewBase.tpl':
						$html .= $field->getDisplayValue($inventoryRows[0][$field->get('columnname')]);
						break;

					case 'DetailViewTaxMode.tpl':
					case 'DetailViewDiscountMode.tpl':
						$html .= vtranslate($field->getDisplayValue($inventoryRows[0][$field->get('columnname')]), $MODULE);
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
				if ($field->isVisible($inventoryRows)) {
					$html .= '<th style="' . $field->get('colspan') . '%;" class="textAlignCenter tBorder tHeader">' . vtranslate($field->get('label'), $module) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';

			foreach ($inventoryRows as $key => &$inventoryRow) {
				$rowNo = $key + 1;
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->isVisible($inventoryRows)) {
						$itemValue = $inventoryRow[$field->get('columnname')];

						$html .= '<td ' . ($field->getName() == 'Name' ? 'width="40%;" ' : '') . ' class="' . (in_array($field->getName(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $module)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								if (isset($fields[2]['comment' . $rowNo])) {
									$COMMENT_FIELD = $fields[2]['comment' . $rowNo];
									$html .= '<br/>' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->get('columnname')]);
								}
								break;

							case 'DetailViewBase.tpl':
								$html .= $field->getDisplayValue($itemValue);
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
				if ($field->isVisible($inventoryRows)) {
					$html .= '<td class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryBorder';
					}
					$html .= '">';

					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => &$inventoryRow) {
							$sum += $inventoryRow[$field->get('columnname')];
						}
						$html .= CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr>
					</tfoot>
				</table>';

			$discount = 0;
			$taxes = 0;
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$discount += $inventoryRow['discount'];
				$taxes = $inventoryField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
			}
			$html .= '<br /><table width="100%" style="vertical-align: top; text-align: center;">
							<tr>
								<td>';

			if (in_array('discount', $columns) && in_array('discountmode', $columns)) {
				$html .= '<table class="pTable colapseBorder">
							<thead>
								<tr>
									<th class="tBorder noBottomBorder tHeader">
										<strong>' . vtranslate('LBL_DISCOUNTS_SUMMARY', $module) . '</strong>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="textAlignRight tBorder">' . CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
			}

			$html .= '</td><td>';
			if (in_array('tax', $columns) && in_array('taxmode', $columns)) {
				$html .= '
						<table class="pTable colapseBorder">
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
			$html .= '</td></tr></table>';
		}

		return $html;
	}
}
