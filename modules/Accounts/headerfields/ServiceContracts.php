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

		$db = PearDatabase::getInstance();
		$sql = 'SELECT MAX(end_date) AS date,count(*) AS total FROM vtiger_servicecontracts INNER JOIN vtiger_crmentity ON vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid WHERE deleted = ? AND sc_related_to = ?';

		$instance = CRMEntity::getInstance('ServiceContracts');
		$securityParameter = $instance->getUserAccessConditionsQuerySR('ServiceContracts', Users_Record_Model::getCurrentUserModel());
		if ($securityParameter != '')
			$sql.= $securityParameter;

		$result = $db->pquery($sql, [0, $recordId]);
		$row = $db->getRow($result);

		if (!empty($row['date']) || !empty($row['total'])) {
			$title = vtranslate('LBL_NUMBER_OF_ACTIVE_CONTRACTS', 'Accounts') . ': ' . $row['total'];
			return [
				'title' => $title,
				'badge' => DateTimeField::convertToUserFormat($row['date'])
			];
		}
		return false;
	}
}
