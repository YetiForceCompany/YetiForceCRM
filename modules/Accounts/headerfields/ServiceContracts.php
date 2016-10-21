<?php

/**
 * ServiceContracts Header Field Class
 * @package YetiForce.HeaderField
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_ServiceContracts_HeaderField
{

	public function process(Vtiger_DetailView_Model $viewModel)
	{
		$recordId = $viewModel->getRecord()->getId();

		$query = (new \App\Db\Query())->select('MAX(due_date) AS date,count(*) AS total')->from('vtiger_servicecontracts')
				->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')
				->where(['deleted' => 0, 'sc_related_to' => $recordId, 'contract_status' => 'In Progress']);
		$row = $query->createCommand()->queryOne();

		if (!empty($row['date']) || !empty($row['total'])) {
			$title = vtranslate('LBL_NUMBER_OF_ACTIVE_CONTRACTS', 'Accounts') . ': ' . $row['total'];
			return [
				'class' => 'btn-success',
				'title' => $title,
				'badge' => DateTimeField::convertToUserFormat($row['date'])
			];
		}
		return false;
	}
}
