<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Import_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('continueImport');
		$this->exposeMethod('uploadAndParse');
		$this->exposeMethod('importBasicStep');
		$this->exposeMethod('import');
		$this->exposeMethod('undoImport');
		$this->exposeMethod('lastImportedRecords');
		$this->exposeMethod('deleteMap');
		$this->exposeMethod('clearCorruptedData');
		$this->exposeMethod('cancelImport');
		$this->exposeMethod('checkImportStatus');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Import')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function process(Vtiger_Request $request) {
		global $VTIGER_BULK_SAVE_MODE;
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;

		$mode = $request->getMode();
		if(!empty($mode)) {
			// Added to check the status of import
			if($mode == 'continueImport' || $mode == 'uploadAndParse' || $mode == 'importBasicStep') {
				$this->checkImportStatus($request);
			}
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->checkImportStatus($request);
			$this->importBasicStep($request);
		}
		
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getFooterScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = array(
			'modules.Import.resources.Import'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function importBasicStep(Vtiger_Request $request) {
		global $upload_maxsize;
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$moduleMeta = $moduleModel->getModuleMeta();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('SUPPORTED_FILE_TYPES', Import_Utils_Helper::getSupportedFileExtensions($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES_TEXT', Import_Utils_Helper::getSupportedFileExtensionsDescription($moduleName));
		$viewer->assign('SUPPORTED_FILE_ENCODING', Import_Utils_Helper::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', Import_Utils_Helper::getSupportedDelimiters());
		$viewer->assign('AUTO_MERGE_TYPES', Import_Utils_Helper::getAutoMergeTypes());
		
		//Duplicate records handling not supported for inventory moduels
		$duplicateHandlingNotSupportedModules = getInventoryModules();
		if(in_array($moduleName, $duplicateHandlingNotSupportedModules)){
			$viewer->assign('DUPLICATE_HANDLING_NOT_SUPPORTED', true);
		}
		//End
		
		$viewer->assign('AVAILABLE_FIELDS', $moduleMeta->getMergableFields());
		$viewer->assign('ENTITY_FIELDS', $moduleMeta->getEntityFields());
		$viewer->assign('ERROR_MESSAGE', $request->get('error_message'));
		$viewer->assign('IMPORT_UPLOAD_SIZE', $upload_maxsize);
		$viewer->assign('IMPORT_UPLOAD_SIZE_MB', round($upload_maxsize/1024/1024,2));
		return $viewer->view('ImportBasicStep.tpl', 'Import');
	}

	function uploadAndParse(Vtiger_Request $request) {

		if(Import_Utils_Helper::validateFileUpload($request)) {
			$moduleName = $request->getModule();
			$user = Users_Record_Model::getCurrentUserModel();

			$fileReader = Import_Utils_Helper::getFileReader($request, $user);
			if($fileReader == null) {
				$request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
				$this->importBasicStep($request);
				exit;
			}

			$hasHeader = $fileReader->hasHeader();
			$rowData = $fileReader->getFirstRowData($hasHeader);

			$viewer = $this->getViewer($request);
			$autoMerge = $request->get('auto_merge');
			if(!$autoMerge) {
				$request->set('merge_type', 0);
				$request->set('merge_fields', '');
			} else {
				$viewer->assign('MERGE_FIELDS', Zend_Json::encode($request->get('merge_fields')));
			}

			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$moduleMeta = $moduleModel->getModuleMeta();


			$viewer->assign('DATE_FORMAT', $user->date_format);
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('MODULE', 'Import');

			$viewer->assign('HAS_HEADER', $hasHeader);
			$viewer->assign('ROW_1_DATA', $rowData);
			$viewer->assign('USER_INPUT', $request);

			$viewer->assign('AVAILABLE_FIELDS', $moduleMeta->getImportableFields($moduleName));
			$viewer->assign('ENCODED_MANDATORY_FIELDS', Zend_Json::encode($moduleMeta->getMandatoryFields($moduleName)));
			$viewer->assign('SAVED_MAPS', Import_Map_Model::getAllByModule($moduleName));
			$viewer->assign('USERS_LIST', Import_Utils_Helper::getAssignedToUserList($moduleName));
			$viewer->assign('GROUPS_LIST', Import_Utils_Helper::getAssignedToGroupList($moduleName));

			return $viewer->view('ImportAdvanced.tpl', 'Import');
		} else {
			$this->importBasicStep($request);
		}
	}

	function import(Vtiger_Request $request) {
		$user = Users_Record_Model::getCurrentUserModel();
		Import_Main_View::import($request, $user);
	}

	function undoImport(Vtiger_Request $request) {
		$viewer = new Vtiger_Viewer();
		$db = PearDatabase::getInstance();

		$moduleName = $request->getModule();
		$ownerId = $request->get('foruser');

		$user = Users_Record_Model::getCurrentUserModel();
		$dbTableName = Import_Utils_Helper::getDbTableName($user);

		if(!$user->isAdminUser() && $user->id != $ownerId) {
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit;
		}
        $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
        $VTIGER_BULK_SAVE_MODE = true;
		$query = "SELECT recordid FROM $dbTableName WHERE temp_status = ? AND recordid IS NOT NULL";
		//For inventory modules
		$inventoryModules = getInventoryModules();
		if(in_array($moduleName, $inventoryModules)){
			$query .=' GROUP BY subject';
		}
		//End
		$result = $db->pquery($query, array(Import_Data_Action::$IMPORT_RECORD_CREATED));
		$noOfRecords = $db->num_rows($result);
		$noOfRecordsDeleted = 0;
        $entityData = array();
		for($i=0; $i<$noOfRecords; $i++) {
			$recordId = $db->query_result($result, $i, 'recordid');
			if(isRecordExists($recordId) && isPermitted($moduleName, 'Delete', $recordId) == 'yes') {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                $recordModel->setId($recordId);
                $recordModel->delete();
                $focus = $recordModel->getEntity();
                $focus->id = $recordId;
                $entityData[] = VTEntityData::fromCRMEntity($focus);
				$noOfRecordsDeleted++;
			}
		}
        $entity = new VTEventsManager($db);        
        $entity->triggerEvent('vtiger.batchevent.delete',$entityData);
        $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('TOTAL_RECORDS', $noOfRecords);
		$viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
		$viewer->view('ImportUndoResult.tpl', 'Import');
	}

	function lastImportedRecords(Vtiger_Request $request) {
		$importList = new Import_List_View();
		$importList->process($request);
	}

	function deleteMap(Vtiger_Request $request) {
		Import_Main_View::deleteMap($request);
	}

	//TODO need to move it to an action
	function clearCorruptedData(Vtiger_Request $request) {
		$user = Users_Record_Model::getCurrentUserModel();
		Import_Utils_Helper::clearUserImportInfo($user);
		$this->importBasicStep($request);
	}

	function cancelImport(Vtiger_Request $request) {
		$importId = $request->get('import_id');
		$user = Users_Record_Model::getCurrentUserModel();

		$importInfo = Import_Queue_Action::getImportInfoById($importId);
		if($importInfo != null) {
			if($importInfo['user_id'] == $user->id || $user->isAdminUser()) {
				$importUser = Users_Record_Model::getInstanceById($importInfo['user_id'], 'Users');
				$importDataController = new Import_Data_Action($importInfo, $importUser);
				$importStatusCount = $importDataController->getImportStatusCount();
				$importDataController->finishImport();
				Import_Main_View::showResult($importInfo, $importStatusCount);
			}
		}
	}

	function checkImportStatus(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$user = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();

		// Check if import on the module is locked
		$lockInfo = Import_Lock_Action::isLockedForModule($moduleName);
		if($lockInfo != null) {
			$lockedBy = $lockInfo['userid'];
			if($user->id != $lockedBy && !$user->isAdminUser()) {
				Import_Utils_Helper::showImportLockedError($lockInfo);
				exit;
			} else {
				if($mode == 'continueImport' && $user->id == $lockedBy) {
					$importController = new Import_Main_View($request, $user);
					$importController->triggerImport(true);
				} else {
					$importInfo = Import_Queue_Action::getImportInfoById($lockInfo['importid']);
					$lockOwner = $user;
					if($user->id != $lockedBy) {
						$lockOwner = Users_Record_Model::getInstanceById($lockInfo['userid'], 'Users');
					}
					Import_Main_View::showImportStatus($importInfo, $lockOwner);
				}
				exit;
			}
		}

		if(Import_Utils_Helper::isUserImportBlocked($user)) {
			$importInfo = Import_Queue_Action::getUserCurrentImportInfo($user);
			if($importInfo != null) {
				Import_Main_View::showImportStatus($importInfo, $user);
				exit;
			} else {
				Import_Utils_Helper::showImportTableBlockedError($moduleName, $user);
				exit;
			}
		}
		Import_Utils_Helper::clearUserImportInfo($user);
	}
}
