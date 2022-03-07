<?php

namespace App\TextParser;

/**
 * Table discount two lang class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			foreach ($inventoryRows as $inventoryRow) {
				$discount += $inventoryRow['discount'];
			}
			if ($inventory->isField('discount') && $inventory->isField('discountmode')) {
				$html .= '<table class="table-discount-s-two-lang" style="border-collapse:collapse;width:100%;">
							<thead>
								<tr><th style="padding:0px 4px;text-align:center;">' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName) . ' / ' . \App\Language::translate('LBL_DISCOUNTS_SUMMARY', $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</th></tr>
							</thead>
							<tbody>
								<tr>
									<td style="text-align:right;padding:0px 4px;border:1px solid #ddd;">' . \CurrencyField::convertToUserFormat($discount, null, true) . ' ' . $currencySymbol . '</td>
								</tr>
							</tbody>
						</table>';
			}
		}
		return $html;
	}
}
