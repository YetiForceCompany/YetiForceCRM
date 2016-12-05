<?php

/**
 * 
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_ServiceContractsHandler_Handler
{

	/**
	 * EntityAfterSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$moduleName = $eventHandler->getModuleName();
		// Update Used Units for the Service Contract, everytime the status of a ticket related to the Service Contract changes
		if ($moduleName === 'HelpDesk' && AppRequest::get('return_module') !== 'ServiceContracts') {
			$recordModel = $eventHandler->getRecordModel();
			$ticketId = $recordModel->getId();
			$status = $recordModel->get('ticketstatus');
			$oldStatus = $recordModel->getPreviousValue('ticketstatus');
			if ($status != $oldStatus) {
				if ($status === 'Closed' || $oldStatus === 'Closed') {
					if ($oldStatus === 'Closed') {
						$op = '-';
					} else {
						$op = '+';
					}
					$dataReader = (new App\Db\Query())
							->select(['crmid'])
							->from('vtiger_crmentityrel')
							->where(['module' => 'ServiceContracts', 'relmodule' => 'HelpDesk', 'relcrmid' => $ticketId])
							->union(
								(new App\Db\Query())
								->select(['relcrmid'])
								->from('vtiger_crmentityrel')
								->where(['relmodule' => 'ServiceContracts', 'module' => 'HelpDesk', 'crmid' => $ticketId])
							)
							->createCommand()->query();
					while ($contractId = $dataReader->readColumn(0)) {
						$scFocus = CRMEntity::getInstance('ServiceContracts');
						$scFocus->id = $contractId;
						$scFocus->retrieve_entity_info($contractId, 'ServiceContracts');

						$prevUsedUnits = $scFocus->column_fields['used_units'];
						if (empty($prevUsedUnits))
							$prevUsedUnits = 0;

						$usedUnits = $scFocus->computeUsedUnits($recordModel->getData());
						if ($op === '-') {
							$totalUnits = $prevUsedUnits - $usedUnits;
						} else {
							$totalUnits = $prevUsedUnits + $usedUnits;
						}
						$scFocus->updateUsedUnits($totalUnits);
						$scFocus->calculateProgress();
					}
				}
			}
		}
		// Update the Planned Duration, Actual Duration, End Date and Progress based on other field values.			
		if ($moduleName === 'ServiceContracts') {
			$recordModel = $eventHandler->getRecordModel();
			$contractId = $recordModel->getId();
			$scFocus = CRMEntity::getInstance('ServiceContracts');
			if ($recordModel->get('tracking_unit') !== $recordModel->getPreviousValue('tracking_unit')) { // Need to recompute used_units based when tracking_unit changes.
				$scFocus->updateServiceContractState($contractId);
			} else {
				$scFocus->id = $contractId;
				$scFocus->retrieve_entity_info($contractId, 'ServiceContracts');
				$scFocus->calculateProgress();
			}
		}
	}
}
