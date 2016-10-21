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

		$query = (new \App\Db\Query())->select('MAX(saledate) AS date, SUM(sum_total) as total')->from('u_yf_finvoice')
			->innerJoin('vtiger_crmentity', 'u_yf_finvoice.finvoiceid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'accountid' => $recordId]);
		$row = $query->one();

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
