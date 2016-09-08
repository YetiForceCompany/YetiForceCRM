<?php

/**
 * FInvoice Record Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class FInvoice_Record_Model extends Vtiger_Record_Model
{

	public function save()
	{
		parent::save();

		if (AppConfig::module('FInvoice', 'UPDATE_LAST_INVOICE_DATE') && !$this->isEmpty('accountid')) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT MAX(saledate) FROM u_yf_finvoice
				LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = u_yf_finvoice.finvoiceid
				WHERE vtiger_crmentity.deleted = 0 && accountid = ?';
			$result = $db->pquery($query, [$this->get('accountid')]);
			$date = $db->getSingleValue($result);
			if (!empty($date)) {
				$db->update('vtiger_account', [
					'last_invoice_date' => $date
					], 'accountid = ?', [$this->get('accountid')]
				);
			}
		}
	}
}
