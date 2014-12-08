<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
************************************************************************************ */
require_once 'modules/WSAPP/synclib/connectors/BaseConnector.php';
require_once 'modules/WSAPP/api/ws/Get.php';
require_once 'modules/WSAPP/synclib/models/VtigerModel.php';
require_once 'modules/WSAPP/synclib/models/PullResultModel.php';
require_once 'modules/WSAPP/api/ws/Map.php';
require_once 'modules/WSAPP/api/ws/Put.php';
require_once 'include/Zend/Json.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/Webservices/Utils.php';


class WSAPP_VtigerConnector extends WSAPP_BaseConnector {

	protected $name;
	protected $db;
	protected $nextSyncSate;

	function __construct() {
		$this->db = PearDatabase::getInstance();
	}

	public function getDbInstance() {
		return $this->db;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function getSyncTrackerHandlerName() {
		return 'vtigerSyncLib';
	}

	public function getUser() {
		return $this->getSynchronizeController()->user;
	}

	public function getRecordModelFromData($data) {
		return $this->getSynchronizeController()->getSourceRecordModel($data);
	}

	public function getSyncState() {
		$result = null;
		if($this->getSynchronizeController()->getSyncType() == "app"){
			$result = $this->db->pquery("SELECT * FROM vtiger_wsapp_sync_state WHERE name=?", array($this->getName()));
		} else {
			$result = $this->db->pquery("SELECT * FROM vtiger_wsapp_sync_state WHERE name=? and userid=?", array($this->getName(), $this->getSynchronizeController()->user->id));//$this->getSYnchronizeController()->getSyncType();
		}
		if ($this->db->num_rows($result) <= 0) {
			return $this->intialSync();
		}
		$rowData = $this->db->raw_query_result_rowdata($result);
		$stateValues = Zend_Json::decode($rowData['stateencodedvalues']);
		$model = WSAPP_SyncStateModel::getInstanceFromQueryResult($stateValues);
		return $model;
	}

	public function moreRecordsExits() {
		$syncState = $this->getSyncState();
		return $syncState->get('more');
	}

	public function intialSync() {
		$registrationDetails = $this->registerWithTracker();
		return $this->getSyncStateModelFromTrackerRegisterDetails($registrationDetails);
	}

	private function getSyncStateModelFromTrackerRegisterDetails($registerDetails) {
		$syncStateModel = new WSAPP_SyncStateModel();
		$syncStateModel->setSyncTrackerId($registerDetails['key'])->setSyncToken(strtotime('10 years ago'))->setType($this->getSynchronizeController()->getSourceType());
		return $syncStateModel;
	}

	function registerWithTracker() {
		return wsapp_register($this->getSyncTrackerHandlerName(), $this->getSynchronizeController()->getSyncType(), $this->user);
	}

	function updateSyncState(WSAPP_SyncStateModel $syncStateModel) {
		$encodedValues = Zend_Json::encode(array('synctrackerid' => $syncStateModel->getSyncTrackerId(), 'synctoken' => $syncStateModel->getSyncToken(), 'more' => $syncStateModel->get('more')));
		$query = 'INSERT INTO vtiger_wsapp_sync_state(stateencodedvalues,name,userid) VALUES (?,?,?)';
		$parameters = array($encodedValues, $this->getName(), $this->getSynchronizeController()->user->id);
		if ($this->isSyncStateExists()) {
			$query		= '';
			$parameters = array();
			if($this->getSynchronizeController()->getSyncType() == "app"){
				$query = 'UPDATE vtiger_wsapp_sync_state SET stateencodedvalues=? where name=?';
				$parameters = array($encodedValues, $this->getName());
			}else {
				$query = 'UPDATE vtiger_wsapp_sync_state SET stateencodedvalues=? where name=? and userid=?';
				$parameters = array($encodedValues, $this->getName(), $this->getSynchronizeController()->user->id);
			}
		}
		$result = $this->db->pquery($query, $parameters);
		if ($result) {
			return true;
		}
		return false;
	}

	function isSyncStateExists() {
		$result = null;
		if($this->getSynchronizeController()->getSyncType() == "app"){
			$result = $this->db->pquery('SELECT 1 FROM vtiger_wsapp_sync_state where name=?', array($this->getName()));
		} else {
			$result = $this->db->pquery('SELECT 1 FROM vtiger_wsapp_sync_state where name=? and userid=?', array($this->getName(), $this->getSynchronizeController()->user->id));
		}
		return ($this->db->num_rows($result) > 0) ? true : false;
	}

	public function pull(WSAPP_SyncStateModel $syncStateModel) {
		$syncTrackerId = $syncStateModel->getSyncTrackerId();
		$prevSyncToken = $syncStateModel->getSyncToken();

		$recordModels = array();
		$records = wsapp_get($syncTrackerId, $syncStateModel->getType(), $prevSyncToken, $this->getSynchronizeController()->user);

		$createdRecords = $records['created'];
		$updatedRecords = $records['updated'];
		$deletedRecords = $records['deleted'];

		foreach ($createdRecords as $record) {
			$model = $this->getRecordModelFromData($record);
			$recordModels[] = $model->setMode(WSAPP_SyncRecordModel::WSAPP_CREATE_MODE);
		}

		foreach ($updatedRecords as $record) {
			$model = $this->getRecordModelFromData($record);
			$recordModels[] = $model->setMode(WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE);
		}

		foreach ($deletedRecords as $record) {
			$model = $this->getRecordModelFromData($record);
			$recordModels[] = $model->setMode(WSAPP_SyncRecordModel::WSAPP_DELETE_MODE);
		}
		$nextSyncState = clone $syncStateModel;
		$nextSyncState->setSyncToken($records['lastModifiedTime'])->set('more', $records['more']);
		$pullResultModel = new WSAPP_PullResultModel();
		$pullResultModel->setPulledRecords($recordModels)->setNextSyncState($nextSyncState)->setPrevSyncState($syncStateModel);
		$this->nextSyncSate = $nextSyncState;
		return $recordModels;
	}

	public function push($recordList, $syncStateModel) {
		$pushResult = wsapp_put($syncStateModel->getSyncTrackerId(), $this->convertToPushSyncTrackerFormat($recordList), $this->getSynchronizeController()->user);
		$pushResponseRecordList = array();
		foreach ($pushResult as $mode => $records) {
			if ($mode == 'created') {
				$recordMode = WSAPP_SyncRecordModel::WSAPP_CREATE_MODE;
			} else if ($mode == 'updated') {
				$recordMode = WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE;
			} else {
				$recordMode = WSAPP_SyncRecordModel::WSAPP_DELETE_MODE;
			}
			foreach ($records as $record) {
				$pushResponseRecordList[] = $this->getRecordModelFromData($record)->setMode($recordMode)->setType($this->getSynchronizeController()->getSourceType());
			}
		}
		return $pushResponseRecordList;
	}

	public function postEvent($type, $synchronizedRecords, $syncStateModel) {
		if ($type == WSAPP_SynchronizeController::WSAPP_SYNCHRONIZECONTROLLER_PULL_EVENT) {
			$this->map($synchronizedRecords, $syncStateModel);
			$this->updateSyncState($this->nextSyncSate);
		}
	}

	public function map($synchronizedRecords, $syncStateModel) {
		$mapFormatedRecords = array();
		$mapFormatedRecords['create'] = array();
		$mapFormatedRecords['update'] = array();
		$mapFormatedRecords['delete'] = array();

		foreach ($synchronizedRecords as $sourceAndTargetRecord) {
			$sourceRecord = $sourceAndTargetRecord['source'];
			$destinationRecord = $sourceAndTargetRecord['target'];
			if ($destinationRecord->isCreateMode()) {
				$mapFormatedRecords['create'][$destinationRecord->getId()] = array('serverid' => $sourceRecord->getId(),
					'modifiedtime' => $destinationRecord->getModifiedTime(),
					'_modifiedtime' => $sourceRecord->getModifiedTime());
			} else if ($destinationRecord->isDeleteMode()) {
				$mapFormatedRecords['delete'][] = $destinationRecord->getId();
			} else {
				$mapFormatedRecords['update'][$destinationRecord->getId()] = array('serverid' => $sourceRecord->getId(),
					'modifiedtime' => $destinationRecord->getModifiedTime(),
					'_modifiedtime' => $sourceRecord->getModifiedTime());
			}
		}
		wsapp_map($syncStateModel->getSyncTrackerId(), $mapFormatedRecords, $this->getSynchronizeController()->user);
	}

	public function convertToPushSyncTrackerFormat($recordList) {
		$syncTrackerRecordList = array();
		foreach ($recordList as $record) {
			$syncTrackerRecord = array();
			$syncTrackerRecord['module'] = $record->getType();
			;
			$syncTrackerRecord['mode'] = $record->getMode();
			$syncTrackerRecord['id'] = $record->getId();
			if (!$record->isDeleteMode()) {
				$syncTrackerRecord['values'] = $record->getData();
				$syncTrackerRecord['values']['modifiedtime'] = $record->getModifiedTime();
				$syncTrackerRecord['values']['id'] = $record->getId();
			} else {
               $syncTrackerRecord['_syncidentificationkey'] = $record->get('_syncidentificationkey');
            }
			$syncTrackerRecordList[] = $syncTrackerRecord;
		}
		return $syncTrackerRecordList;
	}

	public function fillMandatoryEmptyFields($moduleName, $recordLists, $user) {
		$handler = vtws_getModuleHandlerFromName($moduleName, $user);
		$meta = $handler->getMeta();
		$fields = $meta->getModuleFields();
		$mandatoryFields = $meta->getMandatoryFields();
		$ownerFields = $meta->getOwnerFields();
		$transformedRecords = array();
		foreach ($recordLists as $record) {
			foreach ($mandatoryFields as $fieldName) {
				// ignore owner fields 
				if (in_array($fieldName, $ownerFields)) {
					continue;
				}

				$fieldInstance = $fields[$fieldName];
				$currentFieldValue = $record->get($fieldName);
				if (!empty($currentFieldValue)) {
					continue;
				}
				//Dont fill mandatory fields if empty is passed and if the record is in update mode
				//Since sync app is using revise to update
				if($record->getMode() == WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE) {
					continue;
				}
				$fieldDataType = $fieldInstance->getFieldDataType();
				$defaultValue = $fieldInstance->getDefault();
				$value = '';
				switch ($fieldDataType) {
					case 'date':
						$value = $defaultValue;
						if (empty($defaultValue)) {
							$dateObject = new DateTime();
							$value = $dateObject->format('Y-m-d');
						}
						break;

					case 'text':
						$value = '?????';
						if (!empty($defaultValue)) {
							$value = $defaultValue;
						}
						break;
					case 'phone':
						$value = '?????';
						if (!empty($defaultValue)) {
							$value = $defaultValue;
						}
						break;
				}
				$record->set($fieldName, $value);
			}
			$transformedRecords[] = $record;
		}
		return $transformedRecords;
	}

}

?>
