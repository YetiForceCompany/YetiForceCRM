<?php

namespace App\TextParser;

/**
 * Table discount two lang class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class TableDiscountSTwoLang extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_DISCOUNT_S_TWO_LANG';

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
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($this->textParser->moduleName);
		$fields = $inventoryField->getFields(true);

		if ($fields[0] != 0) {
			$columns = $inventoryField->getColumns();
			$inventoryRows = $this->textParser->recordModel->getInventoryData();
			$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		}
		if (in_array('currency', $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] !== null) {
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
										<strong>' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '/ ' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName, 'en_us') . '</strong>
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
