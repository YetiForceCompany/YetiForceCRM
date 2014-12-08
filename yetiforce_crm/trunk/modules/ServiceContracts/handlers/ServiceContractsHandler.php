<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
 
class ServiceContractsHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		if($eventName == 'vtiger.entity.beforesave') {			
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'HelpDesk') {
				$ticketId = $entityData->getId();
				$oldStatus = '';
				if(!empty($ticketId)) {
					$tktResult = $adb->pquery('SELECT status FROM vtiger_troubletickets WHERE ticketid = ?', array($ticketId));
					if($adb->num_rows($tktResult) > 0) {
						$oldStatus = $adb->query_result($tktResult,0,'status');
					}
				}
				$entityData->oldStatus = $oldStatus;
			}
			if ($moduleName == 'ServiceContracts') {				
				$contractId = $entityData->getId();
				$oldTrackingUnit = '';
				if(!empty($contractId)) {
					$contractResult = $adb->pquery('SELECT tracking_unit FROM vtiger_servicecontracts WHERE servicecontractsid = ?', array($contractId));
					if($adb->num_rows($contractResult) > 0) {
						$oldTrackingUnit = $adb->query_result($contractResult,0,'tracking_unit');
					}
				}
				$entityData->oldTrackingUnit = $oldTrackingUnit;
			}
		}

		if($eventName == 'vtiger.entity.aftersave') {
			
			$moduleName = $entityData->getModuleName();
			
			// Update Used Units for the Service Contract, everytime the status of a ticket related to the Service Contract changes
			if ($moduleName == 'HelpDesk' && $_REQUEST['return_module'] != 'ServiceContracts') {
				$ticketId = $entityData->getId();
				$data = $entityData->getData();
				if($data['ticketstatus'] != $entityData->oldStatus) {
					if(strtolower($data['ticketstatus']) == 'closed' || strtolower($entityData->oldStatus) == 'closed') {
						if (strtolower($entityData->oldStatus) == 'closed') {
							$op = '-';
						} else {
							$op = '+';
						}
						$contract_tktresult = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel
																WHERE module = 'ServiceContracts'
																AND relmodule = 'HelpDesk' AND relcrmid = ?
															UNION
																SELECT relcrmid FROM vtiger_crmentityrel
																WHERE relmodule = 'ServiceContracts'
																AND module = 'HelpDesk' AND crmid = ?", array($ticketId,$ticketId));
						$noOfContracts = $adb->num_rows($contract_tktresult);
						if($noOfContracts > 0) {
							for($i=0;$i<$noOfContracts;$i++) {
								$contract_id = $adb->query_result($contract_tktresult,$i,'crmid');								
								$scFocus = CRMEntity::getInstance('ServiceContracts');
								$scFocus->id = $contract_id;
								$scFocus->retrieve_entity_info($contract_id,'ServiceContracts');
								
								$prevUsedUnits = $scFocus->column_fields['used_units'];
								if(empty($prevUsedUnits)) $prevUsedUnits = 0;
								
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
			}
			
			// Update the Planned Duration, Actual Duration, End Date and Progress based on other field values.			
			if ($moduleName == 'ServiceContracts') {				
				$contractId = $entityData->getId();	
				$data = $entityData->getData();							
				$scFocus = CRMEntity::getInstance('ServiceContracts');
				if($data['tracking_unit'] != $entityData->oldTrackingUnit) { // Need to recompute used_units based when tracking_unit changes.
					$scFocus->updateServiceContractState($contractId);
				} else {
					$scFocus->id = $contractId;
					$scFocus->retrieve_entity_info($contractId,'ServiceContracts');
					$scFocus->calculateProgress();
				}
			}
		}
	}
}

?>
