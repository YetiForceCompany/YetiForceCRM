<?php

namespace App\TextParser;

/**
 * Table discount summary class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TableDiscountSummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_DISCOUNT_SUMMARY';

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
			$discount = 0;
			foreach ($inventoryRows as $inventoryRow) {
				$discount += $inventoryRow['discount'];
			}
			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				$html .= '<table style="width:100%;vertical-align:top;border-collapse:collapse;">
				<thead>
								<tr>
									<th style="padding:0px 4px;font-weight:bold;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td style="padding:0px 4px;text-align:right;font-weight:bold;border:1px solid #ddd;">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
								</tbody>
						</table>';
			}
		}
		return $html;
	}
}
