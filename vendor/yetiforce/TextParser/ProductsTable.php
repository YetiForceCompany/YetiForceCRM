<?php
namespace App\TextParser;

/**
 * Products table class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTable extends Base
{

	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);
		if ($fields[0] != 0) {
			$columns = $inventoryField->getColumns();
			$inventoryRows = $this->textParser->recordModel->getInventoryData();
			$mainParams = $inventoryField->getMainParams($fields[1]);
			$countFields0 = count($fields[0]);
			$countFields1 = count($fields[1]);
			$countFields2 = count($fields[2]);
			$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		}
		if (in_array('currency', $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] != NULL) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbolRate = \vtlib\Functions::getCurrencySymbolandRate($currency);
		}
		$html .= '<style>' .
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
								<span>' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ':</span>&nbsp;';
				switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
					case 'DetailViewBase.tpl':
						$html .= $field->getDisplayValue($inventoryRows[0][$field->get('columnname')]);
						break;

					case 'DetailViewTaxMode.tpl':
					case 'DetailViewDiscountMode.tpl':
						$html .= \App\Language::translate($field->getDisplayValue($inventoryRows[0][$field->get('columnname')]), $this->textParser->moduleName);
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
					$html .= '<th style="' . $field->get('colspan') . '%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
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
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								if (isset($fields[2]['comment' . $rowNo])) {
									$commentField = $fields[2]['comment' . $rowNo];
									$html .= '<br/>' . $commentField->getDisplayValue($inventoryRow[$commentField->get('columnname')]);
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
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
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
										<strong>' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</strong>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencySymbolRate['symbol'] . '</td>
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
			$html .= '</td></tr></table>';
		}
		return $html;
	}
}
