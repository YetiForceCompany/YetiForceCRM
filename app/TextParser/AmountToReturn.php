<?php

namespace App\TextParser;

/**
 * Amount to return.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class AmountToReturn extends Base
{
	/** @var string */
	public $name = 'LBL_AMOUNT_TO_RETURN';

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
		$temp = $this->textParser->recordModel->getInventoryData();
		$rows = reset($temp);
		$currency = \App\Fields\Currency::getById($rows['currency']);
		return \App\Fields\Double::formatToDisplay($relatedRecordModel->get('sum_gross') - $this->textParser->recordModel->get('sum_gross')) . ' ' . $currency['currency_symbol'];
	}
}
