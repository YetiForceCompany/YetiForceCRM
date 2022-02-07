<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_ServiceContractsHandler_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$moduleName = $eventHandler->getModuleName();
		// Update Used Units for the Service Contract, everytime the status of a ticket related to the Service Contract changes
		if ('HelpDesk' === $moduleName && 'ServiceContracts' !== \App\Request::_get('return_module')) {
			$recordModel = $eventHandler->getRecordModel();
			$ticketId = $recordModel->getId();
			$status = $recordModel->get('ticketstatus');
			$oldStatus = $recordModel->getPreviousValue('ticketstatus');
			if ($status != $oldStatus && ('Closed' === $status || 'Closed' === $oldStatus)) {
				if ('Closed' === $oldStatus) {
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
					$scFocus->retrieveEntityInfo($contractId, 'ServiceContracts');

					$prevUsedUnits = $scFocus->column_fields['used_units'];
					if (empty($prevUsedUnits)) {
						$prevUsedUnits = 0;
					}

					$usedUnits = $scFocus->computeUsedUnits($recordModel->getData());
					if ('-' === $op) {
						$totalUnits = $prevUsedUnits - $usedUnits;
					} else {
						$totalUnits = $prevUsedUnits + $usedUnits;
					}
					$scFocus->updateUsedUnits($totalUnits);
					$scFocus->calculateProgress();
				}
				$dataReader->close();
			}
		}
		// Update the Planned Duration, Actual Duration, End Date and Progress based on other field values.
		if ('ServiceContracts' === $moduleName) {
			$recordModel = $eventHandler->getRecordModel();
			$contractId = $recordModel->getId();
			$scFocus = CRMEntity::getInstance('ServiceContracts');
			if ($recordModel->get('tracking_unit') !== $recordModel->getPreviousValue('tracking_unit')) { // Need to recompute used_units based when tracking_unit changes.
				$scFocus->updateServiceContractState($contractId);
			} else {
				$scFocus->id = $contractId;
				$scFocus->retrieveEntityInfo($contractId, 'ServiceContracts');
				$scFocus->calculateProgress();
			}
		}
	}
}
