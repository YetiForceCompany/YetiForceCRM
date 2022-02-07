<?php

/**
 * ServiceContracts Header Field Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_ServiceContracts_HeaderField
{
	public function process(Vtiger_DetailView_Model $viewModel)
	{
		$row = (new \App\Db\Query())->select(['date' => new \yii\db\Expression('MAX(due_date)'), 'total' => new \yii\db\Expression('count(*)')])->from('vtiger_servicecontracts')
			->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'sc_related_to' => $viewModel->getRecord()->getId(), 'contract_status' => 'In Progress'])
			->one();
		if (!empty($row['date']) || !empty($row['total'])) {
			return [
				'class' => 'btn-success',
				'title' => \App\Language::translate('LBL_NUMBER_OF_ACTIVE_CONTRACTS', 'Accounts') . ': ' . $row['total'],
				'badge' => DateTimeField::convertToUserFormat($row['date']),
				'action' => 'Vtiger_Detail_Js.getInstance().getTabContainer().find(\'[data-reference="ServiceContracts"]:not(.hide)\').trigger("click");',
			];
		}
		return false;
	}
}
