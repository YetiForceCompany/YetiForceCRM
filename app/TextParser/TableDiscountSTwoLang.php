<?php

namespace App\TextParser;

/**
 * Table discount two lang class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
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
		$html .= '<style>' .
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;}' .
			'</style>';
		if (!empty($fields[0])) {
			$discount = 0;
			foreach ($inventoryRows as &$inventoryRow) {
				$discount += $inventoryRow['discount'];
			}
			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
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
									<td class="textAlignRight tBorder">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
			}
		}
		return $html;
	}
}
