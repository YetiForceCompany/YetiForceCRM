<?php

/**
 * Invoice Header Field Class
 * @package YetiForce.HeaderField
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_FInvoice_HeaderField
{

	public function process(Vtiger_DetailView_Model $viewModel)
	{
		$recordId = $viewModel->getRecord()->getId();

		$db = PearDatabase::getInstance();
		$sql = 'SELECT MAX(saledate) AS date,SUM(sum_total) AS total FROM u_yf_finvoice INNER JOIN vtiger_crmentity ON u_yf_finvoice.finvoiceid = vtiger_crmentity.crmid WHERE deleted = ? && accountid = ?';

		$result = $db->pquery($sql, [0, $recordId]);
		$row = $db->getRow($result);

		if (!empty($row['date']) && !empty($row['total'])) {
			$title = vtranslate('Sum invoices') . ': ' . CurrencyField::convertToUserFormat($row['total'], null, true);
			return [
				'class' => 'btn-success',
				'title' => $title,
				'badge' => DateTimeField::convertToUserFormat($row['date'])
			];
		}
		return false;
	}
}
