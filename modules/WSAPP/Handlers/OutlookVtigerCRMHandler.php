<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'modules/WSAPP/Handlers/vtigerCRMHandler.php';
require_once 'include/utils/GetUserGroups.php';


class OutlookVtigerCRMHandler extends vtigerCRMHandler{
    
    public function translateReferenceFieldNamesToIds($entityRecords,$user){
        $entityRecordList = array();
        foreach($entityRecords as $index=>$record){
            $entityRecordList[$record['module']][$index] = $record;
        }
        foreach($entityRecordList as $module=>$records){
            $handler = vtws_getModuleHandlerFromName($module, $user);
            $meta = $handler->getMeta();
            $referenceFieldDetails = $meta->getReferenceFieldDetails();

            foreach($referenceFieldDetails as $referenceFieldName=>$referenceModuleDetails){
                $recordReferenceFieldNames = array();
                foreach($records as $index=>$recordDetails){
                    if(!empty($recordDetails[$referenceFieldName])) {
                    $recordReferenceFieldNames[] = $recordDetails[$referenceFieldName];
                }
                }
                $entityNameIds = wsapp_getRecordEntityNameIds(array_values($recordReferenceFieldNames), $referenceModuleDetails, $user);
                if(is_array($entityNameIds))
                    $entityNameIds = array_change_key_case($entityNameIds, CASE_LOWER);
                foreach($records as $index=>$recordInfo){
                    if(!empty($entityNameIds[strtolower($recordInfo[$referenceFieldName])])){
                        $recordInfo[$referenceFieldName] = $entityNameIds[strtolower($recordInfo[$referenceFieldName])];
                    } else {
                        if($referenceFieldName == 'account_id'){
                            if($recordInfo[$referenceFieldName]!=NULL){
                                $element['accountname'] = $recordInfo[$referenceFieldName];
                                $element['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $user->id);
                                $element['module'] = "Accounts";
                                $createRecord= array($element);
                                $createRecord = $this->fillNonExistingMandatoryPicklistValues($createRecord);
                                $createRecord = $this->fillMandatoryFields($createRecord, $user);
                                foreach ($createRecord as $key => $record) {
                                	vtws_create($record['module'], $record, $user);
                                }
                                $entityNameIds = wsapp_getRecordEntityNameIds(array_values($recordReferenceFieldNames), $referenceModuleDetails, $user);
                                $recordInfo[$referenceFieldName] = $entityNameIds[$recordInfo[$referenceFieldName]];;
                            }
                        }
                        else{
                            $recordInfo[$referenceFieldName] = "";
                        }
                    }
                    $records[$index] = $recordInfo;
                }
            }
            $entityRecordList[$module] = $records;
        }

        $crmRecords = array();
        foreach($entityRecordList as $module=>$entityRecords){
            foreach($entityRecords as $index=>$record){
                $crmRecords[$index] = $record;
            }
        }
        return $crmRecords;
    }
    
    /*
     * Function overriden to handle duplication
     */
      public function put($recordDetails, $user) {
        global $log;
		$this->user = $user;
		$recordDetails = $this->syncToNativeFormat($recordDetails);
		$createdRecords = $recordDetails['created'];
		$updatedRecords = $recordDetails['updated'];
		$deletedRecords = $recordDetails['deleted'];


		if (count($createdRecords) > 0) {
			$createdRecords = $this->translateReferenceFieldNamesToIds($createdRecords, $user);
			$createdRecords = $this->fillNonExistingMandatoryPicklistValues($createdRecords);
			$createdRecords = $this->fillMandatoryFields($createdRecords, $user);
		}
		foreach ($createdRecords as $index => $record) {
			$createdRecords[$index] = vtws_create($record['module'], $record, $this->user);
		}

		if (count($updatedRecords) > 0) {
			$updatedRecords = $this->translateReferenceFieldNamesToIds($updatedRecords, $user);
		}

		$crmIds = array();

		foreach ($updatedRecords as $index => $record) {
			$webserviceRecordId = $record["id"];
			$recordIdComp = vtws_getIdComponents($webserviceRecordId);
			$crmIds[] = $recordIdComp[1];
		}
		$assignedRecordIds = array();
		if ($this->isClientUserSyncType()) {
			$assignedRecordIds = wsapp_checkIfRecordsAssignToUser($crmIds, $this->user->id);
            // To check if the record assigned to group
            if ($this->isClientUserAndGroupSyncType()) {
                $getUserGroups = new GetUserGroups();
                $getUserGroups->getAllUserGroups($this->user->id);
                $groupIds = $getUserGroups->user_groups;
                if(!empty($groupIds)){
                    $groupRecordId = wsapp_checkIfRecordsAssignToUser($crmIds, $groupIds);
                    $assignedRecordIds = array_merge($assignedRecordIds, $groupRecordId);
                }
            }
            // End
        }
		foreach ($updatedRecords as $index => $record) {
			$webserviceRecordId = $record["id"];
			$recordIdComp = vtws_getIdComponents($webserviceRecordId);
			try {
				if (in_array($recordIdComp[1], $assignedRecordIds)) {
					$updatedRecords[$index] = vtws_revise($record, $this->user);
				} else if (!$this->isClientUserSyncType()) {
					$updatedRecords[$index] = vtws_revise($record, $this->user);
				} else {
					$this->assignToChangedRecords[$index] = $record;
				}
			} catch (Exception $e) {
				continue;
			}
            // Added to handle duplication
            if($record['duplicate']){
                $updatedRecords[$index]['duplicate'] = true;
            }
            // End
		}
		$hasDeleteAccess = null;
		$deletedCrmIds = array();
		foreach ($deletedRecords as $index => $record) {
			$webserviceRecordId = $record;
			$recordIdComp = vtws_getIdComponents($webserviceRecordId);
			$deletedCrmIds[] = $recordIdComp[1];
		}
		$assignedDeletedRecordIds = wsapp_checkIfRecordsAssignToUser($deletedCrmIds, $this->user->id);
        
        // To get record id's assigned to group of the current user
        if ($this->isClientUserAndGroupSyncType()) {
            if(!empty($groupIds)){
                foreach ($groupIds as $group) {
                    $groupRecordId = wsapp_checkIfRecordsAssignToUser($deletedCrmIds, $group);
                    $assignedDeletedRecordIds = array_merge($assignedDeletedRecordIds, $groupRecordId);
                }
            }
        }
        // End
        
		foreach ($deletedRecords as $index => $record) {
			$idComp = vtws_getIdComponents($record);
			if (empty($hasDeleteAccess)) {
				$handler = vtws_getModuleHandlerFromId($idComp[0], $this->user);
				$meta = $handler->getMeta();
				$hasDeleteAccess = $meta->hasDeleteAccess();
			}
			if ($hasDeleteAccess) {
				if (in_array($idComp[1], $assignedDeletedRecordIds)) {
					try {
						vtws_delete($record, $this->user);
					} catch (Exception $e) {
						continue;
					}
				}
			}
		}

		$recordDetails['created'] = $createdRecords;
		$recordDetails['updated'] = $updatedRecords;
		$recordDetails['deleted'] = $deletedRecords;
		return $this->nativeToSyncFormat($recordDetails);
	}
}

?>