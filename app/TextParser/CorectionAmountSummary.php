<?php

namespace App\TextParser;

/**
 * Table for amount to return.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class CorectionAmountSummary extends Base
{
	/** @var string */
	public $name = 'LBL_CORECTION_AMOUNT_SUMMARY';
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
		$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($this->textParser->recordModel->get($this->relatedModulesFields[$this->textParser->recordModel->getModuleName()]));
		$inventoryData = $this->textParser->recordModel->getInventoryData();
		$rows = reset($inventoryData);
		$currency = \App\Fields\Currency::getById($rows['currency']);
		$differenceOfAmounts = \App\Fields\Double::formatToDisplay($this->textParser->recordModel->get('sum_gross') -$relatedRecordModel->get('sum_gross') ) . ' ' . $currency['currency_symbol'];
		$differenceOfAmountsDesciption = $differenceOfAmounts > 0 ? \App\Language::translate('LBL_SURCHARGE_AMOUNT', 'Other.PDF') : \App\Language::translate('LBL_SURCHARGE_AMOUNT', 'Other.PDF');
		
		return '<table cellspacing="0" style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>
						<th style="font-size:10px;">'.$differenceOfAmountsDesciption.'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="border-color:#dddddd;border-style:solid;border-width:1px;font-size:12px;font-weight:bold;text-align:center;">'.$differenceOfAmounts .'</td>
					</tr>
				</tbody>
			</table>';

	}

	
}
