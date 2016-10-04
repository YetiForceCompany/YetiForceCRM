<?php

/**
 * 
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContractsHandler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		$adb = PearDatabase::getInstance();

		if ($eventName == 'vtiger.entity.beforesave') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'HelpDesk') {
				$ticketId = $entityData->getId();
				$oldStatus = '';
				if (!$entityData->isNew()) {
					$tktResult = $adb->pquery('SELECT status FROM vtiger_troubletickets WHERE ticketid = ?', array($ticketId));
					if ($adb->num_rows($tktResult) > 0) {
						$oldStatus = $adb->query_result($tktResult, 0, 'status');
					}
				}
				$entityData->oldStatus = $oldStatus;
			}
			if ($moduleName == 'ServiceContracts') {
				$contractId = $entityData->getId();
				$oldTrackingUnit = '';
				if (!$entityData->isNew()) {
					$contractResult = $adb->pquery('SELECT tracking_unit FROM vtiger_servicecontracts WHERE servicecontractsid = ?', array($contractId));
					if ($adb->num_rows($contractResult) > 0) {
						$oldTrackingUnit = $adb->query_result($contractResult, 0, 'tracking_unit');
					}
				}
				$entityData->oldTrackingUnit = $oldTrackingUnit;
			}
		}

		if ($eventName == 'vtiger.entity.aftersave') {
			$moduleName = $entityData->getModuleName();

			// Update Used Units for the Service Contract, everytime the status of a ticket related to the Service Contract changes
			if ($moduleName == 'HelpDesk' && AppRequest::get('return_module') != 'ServiceContracts') {
				$ticketId = $entityData->getId();
				$data = $entityData->getData();
				if ($data['ticketstatus'] != $entityData->oldStatus) {
					if (strtolower($data['ticketstatus']) == 'closed' || strtolower($entityData->oldStatus) == 'closed') {
						if (strtolower($entityData->oldStatus) == 'closed') {
							$op = '-';
						} else {
							$op = '+';
						}
						$contract_tktresult = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel
																WHERE module = 'ServiceContracts'
																AND relmodule = 'HelpDesk' && relcrmid = ?
															UNION
																SELECT relcrmid FROM vtiger_crmentityrel
																WHERE relmodule = 'ServiceContracts'
																AND module = 'HelpDesk' && crmid = ?", array($ticketId, $ticketId));
						while (($contract_id = $adb->getSingleValue($contract_tktresult)) !== false) {
							$scFocus = CRMEntity::getInstance('ServiceContracts');
							$scFocus->id = $contract_id;
							$scFocus->retrieve_entity_info($contract_id, 'ServiceContracts');

							$prevUsedUnits = $scFocus->column_fields['used_units'];
							if (empty($prevUsedUnits))
								$prevUsedUnits = 0;

							$usedUnits = $scFocus->computeUsedUnits($data);
							if ($op == '-') {
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
			if ($moduleName == 'ServiceContracts') {
				$contractId = $entityData->getId();
				$data = $entityData->getData();
				$scFocus = CRMEntity::getInstance('ServiceContracts');
				if ($data['tracking_unit'] != $entityData->oldTrackingUnit) { // Need to recompute used_units based when tracking_unit changes.
					$scFocus->updateServiceContractState($contractId);
				} else {
					$scFocus->id = $contractId;
					$scFocus->retrieve_entity_info($contractId, 'ServiceContracts');
					$scFocus->calculateProgress();
				}
			}
		}
	}
}
