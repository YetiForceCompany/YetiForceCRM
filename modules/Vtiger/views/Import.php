<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Import_View extends Vtiger_Index_View
{

	public function __construct()
	{
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

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Import')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		global $VTIGER_BULK_SAVE_MODE;
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;

		$mode = $request->getMode();
		if (!empty($mode)) {
			// Added to check the status of import
			if ($mode == 'continueImport' || $mode == 'uploadAndParse' || $mode == 'importBasicStep') {
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
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = array(
			'modules.Import.resources.Import'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function importBasicStep(Vtiger_Request $request)
	{
		global $upload_maxsize;
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$moduleMeta = $moduleModel->getModuleMeta();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('XML_IMPORT_TPL', Import_Utils_Helper::getListTplForXmlType($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES', Import_Utils_Helper::getSupportedFileExtensions($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES_TEXT', Import_Utils_Helper::getSupportedFileExtensionsDescription($moduleName));
		$viewer->assign('SUPPORTED_FILE_ENCODING', Import_Utils_Helper::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', Import_Utils_Helper::getSupportedDelimiters());
		$viewer->assign('AUTO_MERGE_TYPES', Import_Utils_Helper::getAutoMergeTypes());

		//Duplicate records handling not supported for inventory moduels
		$duplicateHandlingNotSupportedModules = getInventoryModules();
		if (in_array($moduleName, $duplicateHandlingNotSupportedModules)) {
			$viewer->assign('DUPLICATE_HANDLING_NOT_SUPPORTED', true);
		}
		//End

		$viewer->assign('AVAILABLE_BLOCKS', $moduleMeta->getMergableFields(true));
		$viewer->assign('ENTITY_FIELDS', $moduleMeta->getEntityFields());
		$viewer->assign('ERROR_MESSAGE', $request->get('error_message'));
		$viewer->assign('IMPORT_UPLOAD_SIZE', $upload_maxsize);
		$viewer->assign('IMPORT_UPLOAD_SIZE_MB', round($upload_maxsize / 1024 / 1024, 2));
		return $viewer->view('ImportBasicStep.tpl', 'Import');
	}

	public function uploadAndParse(Vtiger_Request $request)
	{
		if (Import_Utils_Helper::validateFileUpload($request)) {
			$moduleName = $request->getModule();
			$user = Users_Record_Model::getCurrentUserModel();
			$fileReader = Import_Utils_Helper::getFileReader($request, $user);
			if ($fileReader == null) {
				$this->importBasicStep($request);
				exit;
			}
			$hasHeader = $fileReader->hasHeader();
			$rowData = $fileReader->getFirstRowData($hasHeader);
			$viewer = $this->getViewer($request);
			$autoMerge = $request->get('auto_merge');
			if (!$autoMerge) {
				$request->set('merge_type', 0);
				$request->set('merge_fields', '');
			} else {
				$viewer->assign('MERGE_FIELDS', \includes\utils\Json::encode($request->get('merge_fields')));
			}

			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$moduleMeta = $moduleModel->getModuleMeta();

			$viewer->assign('DATE_FORMAT', $user->date_format);
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('MODULE', 'Import');

			$viewer->assign('HAS_HEADER', $hasHeader);
			$viewer->assign('ROW_1_DATA', ($rowData && $rowData['LBL_STANDARD_FIELDS']) ? $rowData : ['LBL_STANDARD_FIELDS' => $rowData]);
			$viewer->assign('USER_INPUT', $request);

			if ($moduleModel->isInventory()) {
				$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($moduleName);
				$inventoryFields = $inventoryFieldModel->getFields(true);
				$inventoryFieldsBlock = [];
				$blocksName = ['LBL_HEADLINE', 'LBL_BASIC_VERSE', 'LBL_ADDITIONAL_VERSE'];
				foreach ($inventoryFields as $key => $data) {
					$inventoryFieldsBlock[$blocksName[$key]] = $data;
				}
				$viewer->assign('INVENTORY_BLOCKS', $inventoryFieldsBlock);
				$viewer->assign('INVENTORY', true);
			}

			$viewer->assign('AVAILABLE_BLOCKS', $moduleMeta->getImportableFields(true));
			$viewer->assign('ENCODED_MANDATORY_FIELDS', \includes\utils\Json::encode($moduleMeta->getMandatoryFields()));
			$viewer->assign('SAVED_MAPS', Import_Map_Model::getAllByModule($moduleName));
			$viewer->assign('USERS_LIST', Import_Utils_Helper::getAssignedToUserList($moduleName));
			$viewer->assign('GROUPS_LIST', Import_Utils_Helper::getAssignedToGroupList($moduleName));
			$viewer->assign('CREATE_RECORDS_BY_MODEL', in_array($request->get('type'), ['xml', 'zip']));

			return $viewer->view('ImportAdvanced.tpl', 'Import');
		} else {
			$this->importBasicStep($request);
		}
	}

	public function import(Vtiger_Request $request)
	{
		$user = Users_Record_Model::getCurrentUserModel();
		Import_Main_View::import($request, $user);
	}

	public function undoImport(Vtiger_Request $request)
	{
		$previousBulkSaveMode = vglobal('VTIGER_BULK_SAVE_MODE');
		$db = PearDatabase::getInstance();
		$viewer = new Vtiger_Viewer();

		$moduleName = $request->getModule();
		$ownerId = $request->get('foruser');
		$type = $request->get('type');
		$user = Users_Record_Model::getCurrentUserModel();

		if (!$user->isAdminUser() && $user->id != $ownerId) {
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit;
		}
		if (empty($type)) {
			vglobal('VTIGER_BULK_SAVE_MODE', true);
		} else {
			vglobal('VTIGER_BULK_SAVE_MODE', false);
		}
		list($noOfRecords, $noOfRecordsDeleted, $entityData) = $this->undoRecords($type, $moduleName);
		if (empty($type)) {
			$entity = new VTEventsManager($db);
			$entity->triggerEvent('vtiger.batchevent.delete', $entityData);
		}
		vglobal('VTIGER_BULK_SAVE_MODE', $previousBulkSaveMode);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('TOTAL_RECORDS', $noOfRecords);
		$viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
		$viewer->view('ImportUndoResult.tpl', 'Import');
	}

	public function undoRecords($type, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$dbTableName = Import_Utils_Helper::getDbTableName($user);
		$query = "SELECT recordid FROM $dbTableName WHERE temp_status = ? && recordid IS NOT NULL";
		$result = $db->pquery($query, array(Import_Data_Action::$IMPORT_RECORD_CREATED));
		$noOfRecords = $db->getRowCount($result);
		$noOfRecordsDeleted = 0;
		$entityData = [];
		for ($i = 0; $i < $noOfRecords; $i++) {
			$recordId = $db->query_result($result, $i, 'recordid');
			if (isRecordExists($recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				if ($recordModel->isDeletable()) {
					$recordModel->delete();
					if (empty($type)) {
						$focus = $recordModel->getEntity();
						$focus->id = $recordId;
						$entityData[] = VTEntityData::fromCRMEntity($focus);
					}
					$noOfRecordsDeleted++;
				}
			}
		}
		return [$noOfRecords, $noOfRecordsDeleted, $entityData];
	}

	public function lastImportedRecords(Vtiger_Request $request)
	{
		$importList = new Import_List_View();
		$importList->process($request);
	}

	public function deleteMap(Vtiger_Request $request)
	{
		Import_Main_View::deleteMap($request);
	}

	public function clearCorruptedData(Vtiger_Request $request)
	{
		$user = Users_Record_Model::getCurrentUserModel();
		Import_Utils_Helper::clearUserImportInfo($user);
		$this->importBasicStep($request);
	}

	public function cancelImport(Vtiger_Request $request)
	{
		$importId = $request->get('import_id');
		$user = Users_Record_Model::getCurrentUserModel();

		$importInfo = Import_Queue_Action::getImportInfoById($importId);
		if ($importInfo != null) {
			if ($importInfo['user_id'] == $user->id || $user->isAdminUser()) {
				$importUser = Users_Record_Model::getInstanceById($importInfo['user_id'], 'Users');
				$importDataController = new Import_Data_Action($importInfo, $importUser);
				$importStatusCount = $importDataController->getImportStatusCount();
				$importDataController->finishImport();
				Import_Main_View::showResult($importInfo, $importStatusCount);
			}
		}
	}

	public function checkImportStatus(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$user = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();

		// Check if import on the module is locked
		$lockInfo = Import_Lock_Action::isLockedForModule($moduleName);
		if ($lockInfo != null) {
			$lockedBy = $lockInfo['userid'];
			if ($user->id != $lockedBy && !$user->isAdminUser()) {
				Import_Utils_Helper::showImportLockedError($lockInfo);
				exit;
			} else {
				if ($mode == 'continueImport' && $user->id == $lockedBy) {
					$importController = new Import_Main_View($request, $user);
					$importController->triggerImport(true);
				} else {
					$importInfo = Import_Queue_Action::getImportInfoById($lockInfo['importid']);
					$lockOwner = $user;
					if ($user->id != $lockedBy) {
						$lockOwner = Users_Record_Model::getInstanceById($lockInfo['userid'], 'Users');
					}
					Import_Main_View::showImportStatus($importInfo, $lockOwner);
				}
				exit;
			}
		}

		if (Import_Utils_Helper::isUserImportBlocked($user)) {
			$importInfo = Import_Queue_Action::getUserCurrentImportInfo($user);
			if ($importInfo != null) {
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
