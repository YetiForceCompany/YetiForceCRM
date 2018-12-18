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
				$html .= '<table style="border-collapse:collapse;width:100%;">
							<thead>
								<tr><th style="padding:0px 4px;text-align:center;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName, 'en_us') . '</th></tr>
							</thead>
							<tbody>
								<tr>
									<td style="text-align:right;padding:0px 4px;border:1px solid #ddd;">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencyData['currency_symbol'] . '</td>
								</tr>
							</tbody>
						</table>';
			}
		}
		return $html;
	}
}
