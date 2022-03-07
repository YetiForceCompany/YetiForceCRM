<?php

namespace App\TextParser;

/**
 * Table tax summary class fo correct.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class TableCorrectTaxSummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_TAX_SUMMARY_FOR_CORRECT';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var array Allowed modules */
	public $allowedModules = ['FCorectingInvoice'];
	/** @var array Related modules fields */
	protected $relatedModulesFields = ['FCorectingInvoice' => 'finvoiceid'];

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
		$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($this->textParser->recordModel->get($this->relatedModulesFields[$this->textParser->recordModel->getModuleName()]));
		$relatedInventoryRows = $relatedRecordModel->getInventoryData();
		$relatedInventory = \Vtiger_Inventory_Model::getInstance($relatedRecordModel->getModuleName());
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
			$taxes = $relatedTaxes = [];
			if ($inventory->isField('tax') && $inventory->isField('net')) {
				$taxField = $inventory->getField('tax');
				foreach ($inventoryRows as $key => $inventoryRow) {
					$taxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $taxes);
				}
			}
			if ($relatedInventory->isField('tax') && $relatedInventory->isField('net')) {
				$taxField = $relatedInventory->getField('tax');
				foreach ($relatedInventoryRows as $key => $inventoryRow) {
					$relatedTaxes = $taxField->getTaxParam($inventoryRow['taxparam'], $inventoryRow['net'], $relatedTaxes);
				}
			}
			if ($inventory->isField('tax') && $inventory->isField('taxmode')) {
				$taxAmount = $relatedTaxAmount = 0;
				$html .= '
						<table class="table-correct-tax-summary" style="width:100%;vertical-align:top;border-collapse:collapse;border:1px solid #ddd;">
						<thead>
								<tr>
									<th colspan="2" style="font-weight:bold;padding:0px 4px;">' . \App\Language::translate('LBL_TAX_CORRECT_SUMMARY', $this->textParser->moduleName) . '</th>
								</tr>
								</thead><tbody>';

				foreach ($taxes as $tax) {
					$taxAmount += $tax;
				}
				foreach ($relatedTaxes as $tax) {
					$relatedTaxAmount += $tax;
				}
				$html .= '<tr>
									<td class="name" style="text-align:left;font-weight:bold;padding:0px 4px;">' . \App\Language::translate('LBL_AMOUNT', $this->textParser->moduleName) . '</td>
									<td class="value" style="text-align:right;font-weight:bold;padding:0px 4px;">' . \CurrencyField::convertToUserFormat($relatedTaxAmount - $taxAmount, null, true) . ' ' . $currencySymbol . '</td>
								</tr>
								</tbody>
						</table>';
			}
		}
		return $html;
	}
}
