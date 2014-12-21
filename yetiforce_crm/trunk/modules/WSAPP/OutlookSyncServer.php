<?php

/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'modules/WSAPP/Utils.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/Zend/Json.php';
require_once 'include/utils/utils.php';

class OutlookSyncServer extends SyncServer{
    
    private $destHandler;
    private $create = "create";
    private $update = "update";
    private $delete = "delete"; 
    
    function getDestinationHandleDetails(){
        return array('handlerclass' => 'OutlookVtigerCRMHandler',
            'handlerpath' => 'modules/WSAPP/Handlers/OutlookVtigerCRMHandler.php');
	}
    
    /*
     * Function overrided to deal duplication handling 
     */
     function put($key, $element, $user) {
        global $log;
        $db = PearDatabase::getInstance();
        $appid = parent::appid_with_key($key);
        if (empty($appid)) {
            throw new WebServiceException('WSAPP04', "Access restricted to app");
        }

        if (!is_array($element))
            $records = array($element);
        else
            $records = $element;

        //hardcoded since the destination handler will be vtigerCRM
        $serverKey = wsapp_getAppKey("vtigerCRM");
        $serverAppId = parent::appid_with_key($serverKey);
        $handlerDetails = $this->getDestinationHandleDetails();
        $clientApplicationSyncType = wsapp_getAppSyncType($key);
        require_once $handlerDetails['handlerpath'];
        $this->destHandler = new $handlerDetails['handlerclass']($serverKey);
        $this->destHandler->setClientSyncType($clientApplicationSyncType);

        $recordDetails = array();

        $createRecords = array();
        $updateRecords = array();
        $deleteRecords = array();

        $clientModifiedTimeList = array();
        foreach ($records as $record) {
            $recordDetails = array();
            $clientRecordId = $record['id'];

            // Missing client record id?
            if (empty($clientRecordId))
                continue;

            $lookupRecordId = false;
            // Added for Duplication handling
            if (!empty($record['crmid'])) {
                $crmid = vtws_getIdComponents($record['crmid']);
                $lookupResult = $db->pquery("SELECT crmid,modifiedtime FROM vtiger_crmentity WHERE crmid=?", array($crmid[1]));
                if ($db->num_rows($lookupResult))
                    $lookupRecordId = $record['crmid'];
                if (!(empty($lookupRecordId))) {
                    $clientLastModifiedTime = $db->query_result($lookupResult, 0, 'modifiedtime');
                    $record['values']['id'] = $lookupRecordId;
                    $record['values']['duplicate'] = true;
                    $updateRecords[$clientRecordId] = $record['values'];
                    $updateRecords[$clientRecordId]['module'] = $record['module'];
                    $clientModifiedTimeList[$clientRecordId] = $record['values']['modifiedtime'];
                }
            }
            // End
            else {
                $lookupResult = $db->pquery("SELECT serverid,clientmodifiedtime FROM vtiger_wsapp_recordmapping WHERE appid=? AND clientid=?", array($appid, $clientRecordId));
                if ($db->num_rows($lookupResult))
                    $lookupRecordId = $db->query_result($lookupResult, 0, 'serverid');
                if (empty($lookupRecordId) && $record['mode'] != "delete") {
                    $createRecords[$clientRecordId] = $record['values'];
                    $createRecords[$clientRecordId]['module'] = $record['module'];
                    $clientModifiedTimeList[$clientRecordId] = $record['values']['modifiedtime'];
                } else {
                    if (empty($record['values']) && !(empty($lookupRecordId))) {
                        $deleteRecords[$clientRecordId] = $lookupRecordId;
                    } else if (!(empty($lookupRecordId))) {
                        $clientLastModifiedTime = $db->query_result($lookupResult, 0, 'clientmodifiedtime');
                        if ($clientLastModifiedTime >= $record['values']['modifiedtime'])
                            continue;
                        $record['values']['id'] = $lookupRecordId;
                        $updateRecords[$clientRecordId] = $record['values'];
                        $updateRecords[$clientRecordId]['module'] = $record['module'];
                        $clientModifiedTimeList[$clientRecordId] = $record['values']['modifiedtime'];
                    }
                }
            }
        }

        $recordDetails['created'] = $createRecords;
        $recordDetails['updated'] = $updateRecords;
        $recordDetails['deleted'] = $deleteRecords;
        $result = $this->destHandler->put($recordDetails, $user);

        $response = array();
        $response['created'] = array();
        $response['updated'] = array();
        $response['deleted'] = array();

        $log->fatal($result['updated']);

        $nextSyncDeleteRecords = $this->destHandler->getAssignToChangedRecords();
        foreach ($result['created'] as $clientRecordId => $record) {
            parent::idmap_put($appid, $record['id'], $clientRecordId, $clientModifiedTimeList[$clientRecordId], $record['modifiedtime'], $serverAppId, $this->create);
            $responseRecord = $record;
            $responseRecord['_id'] = $record['id'];
            $responseRecord['id'] = $clientRecordId;
            $responseRecord['_modifiedtime'] = $record['modifiedtime'];
            $responseRecord['modifiedtime'] = $clientModifiedTimeList[$clientRecordId];
            $response['created'][] = $responseRecord;
        }
        foreach ($result['updated'] as $clientRecordId => $record) {
            /*
             * If record is duplicate then it'll be in updated records. But, we should create a mapping instead of
             * updating
             */
            if($record['duplicate']){
                parent::idmap_put($appid, $record['id'], $clientRecordId, $clientModifiedTimeList[$clientRecordId], $record['modifiedtime'], $serverAppId, $this->create);
            }else{
                parent::idmap_put($appid, $record['id'], $clientRecordId, $clientModifiedTimeList[$clientRecordId], $record['modifiedtime'], $serverAppId, $this->update);
            }
            $responseRecord = $record;
            $responseRecord['_id'] = $record['id'];
            $responseRecord['id'] = $clientRecordId;
            $responseRecord['_modifiedtime'] = $record['modifiedtime'];
            $responseRecord['modifiedtime'] = $clientModifiedTimeList[$clientRecordId];
            $response['updated'][] = $responseRecord;
        }
        foreach ($result['deleted'] as $clientRecordId => $record) {
            parent::idmap_put($appid, $record, $clientRecordId, "", "", $serverAppId, $this->delete);
            $response['deleted'][] = $clientRecordId;
        }
        $queueRecordIds = array();
        $queueRecordDetails = array();
        foreach ($nextSyncDeleteRecords as $clientRecordId => $record) {
            $queueRecordIds[] = $record['id'];
            $queueRecordDetails[$record['id']] = parent::convertToQueueRecordFormat($record, $this->delete);
        }
        if (count($queueRecordIds > 0)) {
            $syncServerDetails = parent::idmap_get_clientmap($appid, $queueRecordIds);
            foreach ($queueRecordIds as $serverId) {
                $syncServerId = $syncServerDetails[$serverId]['id'];
                $recordValues = $queueRecordDetails[$serverId];
                if (!parent::checkIdExistInQueue($syncServerId)) {
                    parent::idmap_storeRecordsInQueue($syncServerId, $recordValues, $this->delete, $appid);
                }
            }
        }
        return $response;
    }
}
?>
