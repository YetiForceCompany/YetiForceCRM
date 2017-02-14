<?php
namespace App\TextParser;

/**
 * Table discount summary class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TableDiscountSummary extends Base
{

	/** @var string Class name */
	public $name = 'LBL_TABLE_DISCOUNT_SUMMARY';

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
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;}' .
			'</style>';
		if (count($fields[0]) != 0) {
			$discount = 0;
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$discount += $inventoryRow['discount'];
			}
			if (in_array('discount', $columns) && in_array('discountmode', $columns)) {
				$html .= '<table class="productTable colapseBorder">
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
		}
		return $html;
	}
}
