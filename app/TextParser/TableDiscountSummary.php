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
			$currencyData = \App\Fields\Currency::getById($currency);
		}
		if (count($fields[0]) != 0) {
			$discount = 0;
			foreach ($inventoryRows as &$inventoryRow) {
				$discount += $inventoryRow['discount'];
			}
			if (in_array('discount', $columns) && in_array('discountmode', $columns)) {
				$html .= '<table style="width:100%;vertical-align:top;border-collapse:collapse;">
				<thead>
								<tr>
									<th style="font-weight:bold;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td style="text-align:right;font-weight:bold;border:1px solid #ddd;">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
								</tbody>
						</table>';
			}
		}
		return $html;
	}
}
