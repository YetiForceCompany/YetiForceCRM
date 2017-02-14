<?php

/**
 * FInvoice Record Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class FInvoice_Record_Model extends Vtiger_Record_Model
{

	public function saveToDb()
	{
		parent::saveToDb();

		if (AppConfig::module('FInvoice', 'UPDATE_LAST_INVOICE_DATE') && !$this->isEmpty('accountid')) {
			$date = (new \App\Db\Query())->from('u_#__finvoice')
				->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__finvoice.finvoiceid')
				->where(['vtiger_crmentity.deleted' => 0, 'accountid' => $this->get('accountid')])
				->max('saledate');
			if (!empty($date)) {
				App\Db::getInstance()->createCommand()->update('vtiger_account', [
					'last_invoice_date' => $date
					], ['accountid' => $this->get('accountid')]
				)->execute();
			}
		}
	}
}
