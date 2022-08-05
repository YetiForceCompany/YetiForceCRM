<?php

namespace App\TextParser;

/**
 * Table discount summary class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbol = \App\Fields\Currency::getById($currency)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		if (!empty($fields[0])) {
			$discount = 0;
			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				foreach ($inventoryRows as $inventoryRow) {
					$discount += $inventoryRow['discount'];
				}
				$html .= '<table class="table-discount-summary" style="width:100%;vertical-align:top;border-collapse:collapse;border:1px solid #ddd;">
				<thead>
					<tr>
						<th style="padding:0px 4px;font-weight:bold;background-color:#ddd;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . '</th>
					</tr>
				</thead>
					<tbody>
						<tr>
							<td style="padding:0px 4px;text-align:right;font-weight:bold;border:1px solid #ddd;">' . \CurrencyField::convertToUserFormatSymbol($discount, true, $currencySymbol) . '</td>
						</tr>
					</tbody>
				</table>';
			}
		}
		return $html;
	}
}
