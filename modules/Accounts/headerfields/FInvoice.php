<?php

/**
 * Invoice Header Field Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_FInvoice_HeaderField
{
	public function process(Vtiger_DetailView_Model $viewModel)
	{
		$row = (new \App\Db\Query())->select(['date' => new \yii\db\Expression('MAX(saledate)'), 'total' => new \yii\db\Expression('SUM(sum_total)')])->from('u_#__finvoice')
			->innerJoin('vtiger_crmentity', 'u_#__finvoice.finvoiceid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'accountid' => $viewModel->getRecord()->getId()])->one();
		if (!empty($row['date']) && !empty($row['total'])) {
			return [
				'class' => 'btn-success',
				'title' => \App\Language::translate('Sum invoices') . ': ' . CurrencyField::convertToUserFormat($row['total'], null, true),
				'badge' => DateTimeField::convertToUserFormat($row['date']),
			];
		}
		return false;
	}
}
