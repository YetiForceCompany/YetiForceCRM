<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Import_View extends Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

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

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$relationId = $request->getInteger('relationId');
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'Import') || !$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView') || (
			$relationId && (
				!($relationModel = Vtiger_Relation_Model::getInstanceById($relationId))
				|| !$relationModel->isAddActionSupported()
				|| $relationModel->getRelationModuleName() !== $request->getModule()
				|| !($recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('src_record')))
				|| !$recordModel->isViewable()
				|| $relationModel->getParentModuleModel()->getName() !== $recordModel->getModuleName()
			))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if ($request->isEmpty('mode')) {
			if (!$this->checkImportStatus($request)) {
				$this->importBasicStep($request);
			}
		} else {
			$mode = $request->getMode();
			// Added to check the status of import
			if ('continueImport' === $mode || 'uploadAndParse' === $mode || 'importBasicStep' === $mode) {
				$this->checkImportStatus($request);
			}
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		$jsFileNames = [
			'modules.Import.resources.Import',
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * First step to import records.
	 *
	 * @param \App\Request $request
	 */
	public function importBasicStep(App\Request $request)
	{
		$moduleName = $request->getModule();
		$importModule = Vtiger_Module_Model::getInstance('Import')->setImportModule($moduleName);
		$importModule->set('src_record', $request->getInteger('src_record'));
		$importModule->set('relationId', $request->getInteger('relationId'));
		$viewer = $this->getViewer($request);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('XML_IMPORT_TPL', Import_Module_Model::getListTplForXmlType($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES', Import_Module_Model::getSupportedFileExtensions($moduleName));
		$viewer->assign('SUPPORTED_FILE_TYPES_TEXT', Import_Module_Model::getSupportedFileExtensionsDescription($moduleName));
		$viewer->assign('SUPPORTED_FILE_ENCODING', Import_Module_Model::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', Import_Module_Model::getSupportedDelimiters());
		$viewer->assign('AUTO_MERGE_TYPES', Import_Module_Model::getAutoMergeTypes());
		$viewer->assign('AVAILABLE_BLOCKS', $importModule->getFieldsByBlocks());
		$viewer->assign('FOR_MODULE_MODEL', $importModule->getImportModuleModel());
		$viewer->assign('ERROR_MESSAGE', $request->getByType('error_message', 'Text'));
		$viewer->assign('IMPORT_UPLOAD_SIZE', \App\Config::getMaxUploadSize());
		$viewer->assign('IMPORT_UPLOAD_SIZE_MB', \App\Config::getMaxUploadSize(true, true));
		$viewer->assign('MODULE_MODEL', $importModule);
		return $viewer->view('ImportBasicStep.tpl', 'Import');
	}

	/**
	 * Function verifies, validates and uploads data for import.
	 *
	 * @param \App\Request $request
	 */
	public function uploadAndParse(App\Request $request)
	{
		if (Import_Utils_Helper::validateFileUpload($request)) {
			$user = App\User::getCurrentUserModel();
			$fileReader = Import_Module_Model::getFileReader($request, $user);
			if (null === $fileReader) {
				$this->importBasicStep($request);
				return;
			}
			$hasHeader = $fileReader->hasHeader();
			$rowData = $fileReader->getFirstRowData($hasHeader);
			$viewer = $this->getViewer($request);
			$autoMerge = $request->getBoolean('auto_merge');
			if (!$autoMerge) {
				$request->set('merge_type', 0);
				$request->set('merge_fields', '');
				$viewer->assign('MERGE_FIELDS', '');
			} else {
				$viewer->assign('MERGE_FIELDS', \App\Json::encode($request->getArray('merge_fields', 'Alnum')));
			}
			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$viewer->assign('DATE_FORMAT', $user->getDetail('date_format'));
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('MODULE', 'Import');
			$viewer->assign('HAS_HEADER', $hasHeader);
			$viewer->assign('ROW_1_DATA', ($rowData && $rowData['LBL_STANDARD_FIELDS']) ? $rowData : ['LBL_STANDARD_FIELDS' => $rowData]);
			$viewer->assign('USER_INPUT', $request);
			$mandatoryFields = [];
			foreach ($moduleModel->getMandatoryFieldModels() as $fieldName => $fieldModel) {
				if ($fieldModel->isEditable()) {
					$mandatoryFields[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
				}
			}
			if ($moduleModel->isInventory()) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($moduleName);
				$inventoryFields = $inventoryModel->getFieldsByBlocks();
				$inventoryFieldsBlock = [];
				$blocksName = ['LBL_HEADLINE', 'LBL_BASIC_VERSE', 'LBL_ADDITIONAL_VERSE'];
				foreach ($inventoryFields as $key => $data) {
					$inventoryFieldsBlock[$blocksName[$key]] = $data;
				}
				$viewer->assign('INVENTORY_BLOCKS', $inventoryFieldsBlock);
				$viewer->assign('INVENTORY', true);
			}
			$importModule = Vtiger_Module_Model::getInstance('Import')
				->setImportModule($moduleName)
				->set('src_record', $request->getInteger('src_record'))
				->set('relationId', $request->getInteger('relationId'));
			$viewer->assign('AVAILABLE_BLOCKS', $importModule->getFieldsByBlocks());
			$viewer->assign('ENCODED_MANDATORY_FIELDS', \App\Json::encode($mandatoryFields));
			$viewer->assign('SAVED_MAPS', Import_Map_Model::getAllByModule($moduleName));
			$viewer->assign('USERS_LIST', Import_Utils_Helper::getAssignedToUserList($moduleName));
			$viewer->assign('GROUPS_LIST', Import_Utils_Helper::getAssignedToGroupList($moduleName));
			$viewer->assign('CREATE_RECORDS_BY_MODEL', \in_array($request->getByType('type', 1), ['xml', 'zip']));
			$viewer->assign('MODULE_MODEL', $importModule);
			return $viewer->view('ImportAdvanced.tpl', 'Import');
		}
		$this->importBasicStep($request);
	}

	/**
	 * @param \App\Request $request
	 */
	public function import(App\Request $request)
	{
		Import_Main_View::import($request, App\User::getCurrentUserModel());
	}

	/**
	 * Continue import.
	 *
	 * @param \App\Request $request
	 */
	public function continueImport(App\Request $request)
	{
		$this->checkImportStatus($request);
	}

	public function undoImport(App\Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$moduleName = $request->getModule();
		$ownerId = $request->getInteger('foruser');
		$user = App\User::getCurrentUserModel();
		if (!$user->isAdmin() && $user->getId() !== $ownerId) {
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('Exceptions/ExceptionError.tpl', 'Vtiger');
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		[$noOfRecords, $noOfRecordsDeleted] = $this->undoRecords($moduleName);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', 'Import');
		$viewer->assign('TOTAL_RECORDS', $noOfRecords);
		$viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
		$viewer->view('ImportUndoResult.tpl', 'Import');
	}

	public function undoRecords(string $moduleName)
	{
		$dbTableName = Import_Module_Model::getDbTableName(App\User::getCurrentUserModel());
		$dataReader = (new \App\Db\Query())->select(['recordid'])
			->from($dbTableName)
			->where(['and', ['temp_status' => Import_Data_Action::IMPORT_RECORD_CREATED], ['not', ['recordid' => null]]])
			->createCommand()->query();
		$noOfRecords = $noOfRecordsDeleted = 0;
		while ($recordId = $dataReader->readColumn(0)) {
			if (App\Record::isExists($recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				if ($recordModel->privilegeToMoveToTrash()) {
					$recordModel->changeState('Trash');
					++$noOfRecordsDeleted;
				} elseif ($recordModel->privilegeToDelete()) {
					$recordModel->delete();
					++$noOfRecordsDeleted;
				}
			}
			++$noOfRecords;
		}
		return [$noOfRecords, $noOfRecordsDeleted];
	}

	public function lastImportedRecords(App\Request $request)
	{
		$importList = new Import_List_View();
		$importList->process($request);
	}

	public function deleteMap(App\Request $request)
	{
		Import_Main_View::deleteMap($request);
	}

	public function clearCorruptedData(App\Request $request)
	{
		Import_Module_Model::clearUserImportInfo(\App\User::getCurrentUserModel());
		$this->importBasicStep($request);
	}

	public function cancelImport(App\Request $request)
	{
		$importId = $request->getInteger('import_id');
		$user = App\User::getCurrentUserModel();
		$importInfo = Import_Queue_Action::getImportInfoById($importId);
		if (null !== $importInfo && ($importInfo['user_id'] === $user->getId() || $user->isAdmin())) {
			$importDataController = new Import_Data_Action($importInfo, \App\User::getUserModel($importInfo['user_id']));
			$importStatusCount = $importDataController->getImportStatusCount();
			$importDataController->finishImport();
			Import_Main_View::showResult($importInfo, $importStatusCount);
		}
	}

	public function checkImportStatus(App\Request $request)
	{
		$moduleName = $request->getModule();
		$user = \App\User::getCurrentUserModel();
		// Check if import on the module is locked
		$lockInfo = Import_Lock_Action::isLockedForModule($moduleName);
		if ($lockInfo) {
			$lockedBy = $lockInfo['userid'];
			if ($user->getId() !== $lockedBy && !$user->isAdmin()) {
				Import_Utils_Helper::showImportLockedError($lockInfo);
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
			$mode = $request->isEmpty('mode') ? '' : $request->getMode();
			if ('continueImport' === $mode && $user->getId() === $lockedBy) {
				$importController = new Import_Main_View($request, $user);
				$importController->triggerImport(true);
			} else {
				$importInfo = Import_Queue_Action::getImportInfoById($lockInfo['importid']);
				$lockOwner = $user;
				if ($user->getId() !== $lockedBy) {
					$lockOwner = \App\User::getUserModel($lockInfo['userid']);
				}
				Import_Main_View::showImportStatus($importInfo, $lockOwner);
			}
			return true;
		}
		if (Import_Module_Model::isUserImportBlocked($user)) {
			$importInfo = Import_Queue_Action::getUserCurrentImportInfo($user);
			if (null !== $importInfo) {
				Import_Main_View::showImportStatus($importInfo, $user);
				return true;
			}
			Import_Utils_Helper::showImportTableBlockedError($moduleName);
			return true;
		}
		if ('continueImport' !== $request->getMode()) {
			Import_Module_Model::clearUserImportInfo($user);
		}
		return false;
	}
}
