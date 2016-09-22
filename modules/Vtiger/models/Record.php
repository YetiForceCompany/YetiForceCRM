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

/**
 * Vtiger Entity Record Model Class
 */
class Vtiger_Record_Model extends Vtiger_Base_Model
{

	protected $module = false;
	protected $inventoryData = false;
	protected $privileges = [];
	public $summaryRowCount = 4;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value)
	{
		return $this->set('id', $value);
	}

	/**
	 * Fuction to get the Name of the record
	 * @return <String> - Entity Name of the record
	 */
	public function getName()
	{
		$displayName = $this->get('label');
		if (empty($displayName)) {
			$displayName = $this->getDisplayName();
		}
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	public function getSearchName()
	{
		$displayName = $this->get('searchlabel');
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	public function isWatchingRecord()
	{
		if (!isset($this->isWatchingRecord)) {
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($this->getId(), $this->getModuleName());
			$this->isWatchingRecord = (bool) $watchdog->isWatchingRecord();
		}
		return $this->isWatchingRecord;
	}

	/**
	 * Function to get the Module to which the record belongs
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param <Vtiger_Module_Model> $module
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module)
	{
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the entity instance of the recrod
	 * @return CRMEntity object
	 */
	public function getEntity()
	{
		if (empty($this->entity)) {
			return false;
		}
		return $this->entity;
	}

	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 */
	public function setEntity($entity)
	{
		$this->entity = $entity;
		return $this;
	}

	/**
	 * Function to get raw data
	 * @return <Array>
	 */
	public function getRawData()
	{

		return isset($this->rawData) ? $this->rawData : false;
	}

	/**
	 * Function to set raw data
	 * @param <Array> $data
	 * @return Vtiger_Record_Model instance
	 */
	public function setRawData($data)
	{
		$this->rawData = $data;
		return $this;
	}

	public function getRecordNumber()
	{
		$fieldModel = $this->getModule()->getFieldsByUiType(4);
		$fieldModel = reset($fieldModel);
		return $this->get($fieldModel->getName());
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getFullDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId() . '&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Update View url for the record
	 * @return <String> - Record Upadte view Url
	 */
	public function getUpdatesUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES';
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&action=' . $module->getDeleteActionName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return <String> - Record Module Name
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName()
	{
		return \includes\Record::getLabel($this->getId());
	}

	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @return <String>
	 */
	public function getDisplayValue($fieldName, $recordId = false, $rawText = false)
	{
		if (empty($recordId)) {
			$recordId = $this->getId();
		}
		$fieldModel = $this->getModule()->getField($fieldName);

		// For showing the "Date Sent" and "Time Sent" in email related list in user time zone
		if ($fieldName == "time_start" && $this->getModule()->getName() == "Emails") {
			$date = new DateTime();
			$dateTime = new DateTimeField($date->format('Y-m-d') . ' ' . $this->get($fieldName));
			$value = $dateTime->getDisplayTime();
			$this->set($fieldName, $value);
			return $value;
		} else if ($fieldName == "date_start" && $this->getModule()->getName() == "Emails") {
			$dateTime = new DateTimeField($this->get($fieldName) . ' ' . $this->get('time_start'));
			$value = $dateTime->getDisplayDate();
			$this->set($fieldName, $value);
			return $value;
		}
		// End

		if ($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $recordId, $this, $rawText);
		}
		return false;
	}

	/**
	 * Function returns the Vtiger_Field_Model
	 * @param <String> $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName)
	{
		return $this->getModule()->getField($fieldName);
	}

	/**
	 * Function returns all the field values in user format
	 * @return <Array>
	 */
	public function getDisplayableValues()
	{
		$displayableValues = [];
		$data = $this->getData();
		foreach ($data as $fieldName => $value) {
			$fieldValue = $this->getDisplayValue($fieldName);
			$displayableValues[$fieldName] = ($fieldValue) ? $fieldValue : $value;
		}
		return $displayableValues;
	}

	/**
	 * Function to save the current Record Model
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();
		//Disabled generating record ID in transaction  in order to maintain data integrity
		if ($this->get('mode') != 'edit') {
			$recordId = $db->getUniqueID('vtiger_crmentity');
			$this->set('newRecord', $recordId);
		}

		$db->startTransaction();
		if ($this->getModule()->isInventory()) {
			$this->initInventoryData();
		}

		$this->getModule()->saveRecord($this);
		$db->completeTransaction();
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		$db = PearDatabase::getInstance();
		$db->startTransaction();

		$this->getModule()->deleteRecord($this);

		$db->completeTransaction();
	}

	/**
	 * Static Function to get the instance of a clean Vtiger Record Model for the given module name
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getCleanInstance($moduleName)
	{
		$focus = CRMEntity::getInstance($moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
	}

	/**
	 * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name
	 * @param <Number> $recordId
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceById($recordId, $module = null)
	{
		if (is_object($module) && is_a($module, 'Vtiger_Module_Model')) {
			$moduleName = $module->get('name');
		} elseif (is_string($module)) {
			$module = Vtiger_Module_Model::getInstance($module);
			$moduleName = $module->get('name');
		} elseif (empty($module)) {
			$moduleName = \includes\Record::getType($recordId);
			$module = Vtiger_Module_Model::getInstance($moduleName);
		}
		$cacheName = $recordId . ':' . $moduleName;
		$instance = Vtiger_Cache::get('Vtiger_Record_Model', $cacheName);
		if ($instance) {
			return $instance;
		}

		$focus = CRMEntity::getInstance($moduleName);
		$focus->id = $recordId;
		$focus->retrieve_entity_info($recordId, $moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		$instance->setData($focus->column_fields)->set('id', $recordId)->setModuleFromInstance($module)->setEntity($focus);
		$instance->set('mode', 'edit');
		Vtiger_Cache::set('Vtiger_Record_Model', $cacheName, $instance);
		return $instance;
	}

	public static function getInstanceByEntity($focus, $recordId)
	{
		$moduleName = $focus->moduleName;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$recordModel = new $modelClassName();
		$recordModel->setData($focus->column_fields)->set('id', $recordId)->setModuleFromInstance($moduleModel)->setEntity($focus);
		return $recordModel;
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module = false, $limit = false)
	{
		if (!$limit) {
			$limit = AppConfig::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
		}
		$rows = \includes\Record::findCrmidByLabel($searchKey, $module, $limit);
		$ids = $matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \includes\Record::getLabel($ids);

		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = $labels[$row['crmid']];
			$row['smownerid'] = $recordMeta['smownerid'];
			$row['createdtime'] = $recordMeta['createdtime'];
			$row['permitted'] = \includes\Privileges::isPermitted($row['setype'], 'DetailView', $row['crmid']);
			$moduleName = $row['setype'];
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
			$recordInstance = new $modelClassName();
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
		}
		return $matchingRecords;
	}

	public function isViewable()
	{
		if (!isset($this->privileges['isViewable'])) {
			$this->privileges['isViewable'] = Users_Privileges_Model::isPermitted($this->getModuleName(), 'DetailView', $this->getId());
		}
		return $this->privileges['isViewable'];
	}

	public function isCreateable()
	{
		if (!isset($this->privileges['isCreateable'])) {
			$this->privileges['isCreateable'] = $this->getModule()->isPermitted('CreateView');
		}
		return $this->privileges['isCreateable'];
	}

	public function isEditable()
	{
		if (!isset($this->privileges['isEditable'])) {
			$moduleName = $this->getModuleName();
			$recordId = $this->getId();

			$isPermitted = Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId);
			$checkLockEdit = Users_Privileges_Model::checkLockEdit($moduleName, $recordId);

			$this->privileges['isEditable'] = $isPermitted && $this->checkLockFields() && $checkLockEdit == false;
		}
		return $this->privileges['isEditable'];
	}

	public function checkLockFields()
	{
		$moduleName = $this->getModuleName();
		$recordId = $this->getId();
		$focus = $this->getEntity();
		if (!$focus) {
			$focus = CRMEntity::getInstance($moduleName);
			$this->setEntity($focus);
		}
		$lockFields = $focus->getLockFields();
		if ($lockFields) {
			$loadData = false;
			foreach ($lockFields as $fieldName => $values) {
				if (!$this->has($fieldName)) {
					$loadData = true;
				}
			}
			if ($loadData && $recordId) {
				$focus->id = $recordId;
				$focus->retrieve_entity_info($recordId, $moduleName);
				$this->setEntity($focus);
			}
			foreach ($lockFields as $fieldName => $values) {
				foreach ($values as $value) {
					if ($this->get($fieldName) == $value) {
						return false;
					}
					if (isset($focus->column_fields[$fieldName]) && $focus->column_fields[$fieldName] == $value) {
						return false;
					}
				}
			}
		}
		return true;
	}

	public function isDeletable()
	{
		if (!isset($this->privileges['isDeletable'])) {
			$this->privileges['isDeletable'] = Users_Privileges_Model::isPermitted($this->getModuleName(), 'Delete', $this->getId()) && $this->checkLockFields();
		}
		return $this->privileges['isDeletable'];
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($fieldName)
	{
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId)
	{
		$db = PearDatabase::getInstance();
		$checkResult = $db->pquery('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$crmId = $db->query_result($checkResult, 0, 'crmid');
		if ($this->getId() == $crmId) {
			$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
			return true;
		}
		return false;
	}

	/**
	 * Function to get Descrption value for this record
	 * @return <String> Descrption
	 */
	public function getDescriptionValue()
	{
		$description = $this->get('description');
		if (empty($description)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT description FROM vtiger_crmentity WHERE crmid = ?", array($this->getId()));
			$description = $db->query_result($result, 0, "description");
		}
		return $description;
	}

	/**
	 * Function to transfer related records of parent records to this record
	 * @param <Array> $recordIds
	 * @return <Boolean> true/false
	 */
	public function transferRelationInfoOfRecords($recordIds = [])
	{
		if ($recordIds) {
			$moduleName = $this->getModuleName();
			$focus = CRMEntity::getInstance($moduleName);
			if (method_exists($focus, 'transferRelatedRecords')) {
				$focus->transferRelatedRecords($moduleName, $recordIds, $this->getId());
			}
		}
		return true;
	}

	public function getSummaryInfo()
	{
		$moduleName = $this->getModuleName();
		$path = "modules/$moduleName/summary_blocks";
		if (!is_dir($path)) {
			return [];
		}
		$summaryBlocks = [];
		$dir = new DirectoryIterator($path);
		$blockCount = 0;

		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$tmp = explode('.', $fileinfo->getFilename());
				$fullPath = $path . DIRECTORY_SEPARATOR . $tmp[0] . '.php';
				if (file_exists($fullPath)) {
					require_once $fullPath;
					$blockObiect = new $tmp[0];
					$summaryBlocks[intval($blockCount / $this->summaryRowCount)][$blockObiect->sequence] = array('name' => $blockObiect->name, 'data' => $blockObiect->process($this), 'reference' => $blockObiect->reference);
					$blockCount++;
				}
			}
		}
		foreach ($summaryBlocks as $key => $block) {
			ksort($summaryBlocks[$key]);
		}
		return $summaryBlocks;
	}

	public function trackView()
	{
		$log = vglobal('log');
		$db = PearDatabase::getInstance();
		$id = $this->getId();
		$log->debug("Track the viewing of a detail record: vtiger_tracker (user_id, module_name, item_id)($id)");
		if ($id != '') {
			$updateQuery = "UPDATE vtiger_crmentity SET viewedtime=? WHERE crmid=?;";
			$updateParams = array(date('Y-m-d H:i:s'), $this->getId());
			$db->pquery($updateQuery, $updateParams);
		}
	}

	/**
	 * Function to set record module field values
	 * @param parent record model
	 */
	public function setRecordFieldValues($parentRecordModel)
	{
		$newInvData = [];
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mfInstance = Vtiger_MappedFields_Model::getInstanceByModules($parentRecordModel->getModule()->getId(), $this->getModule()->getId());
		if ($mfInstance) {
			$moduleFields = $this->getModule()->getFields();
			$fieldsList = array_keys($moduleFields);
			$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());
			$this->set('mode', 'fromMapping');
			$params = $mfInstance->get('params');
			if ($params['autofill']) {
				$commonFields = array_intersect($fieldsList, $parentFieldsList);
				foreach ($commonFields as $fieldName) {
					if (getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), $fieldName) == 0) {
						if ($fieldName == 'shownerid') {
							$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $parentRecordModel->getModule());
							$parentRecordModel->set($fieldName, $fieldInstance->getUITypeModel()->getEditViewDisplayValue('', $parentRecordModel->getId()));
						}
						$this->set($fieldName, $parentRecordModel->get($fieldName));
					}
				}
			}
			if ($parentRecordModel->getModule()->isInventory() && $this->getModule()->isInventory()) {
				$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($parentRecordModel->getModuleName());
				$inventoryFields = $inventoryFieldModel->getFields();
				$sourceInv = $parentRecordModel->getInventoryData();
			}
			foreach ($mfInstance->getMapping() as $mapp) {
				if ($mapp['type'] == 'SELF' && is_object($mapp['target'])) {
					$referenceList = $mapp['target']->getReferenceList();
					if (in_array($parentRecordModel->getModuleName(), $referenceList)) {
						$this->set($mapp['target']->getName(), $parentRecordModel->get($mapp['source']->getName()));
					}
				} elseif ($mapp['type'] == 'INVENTORY' && is_array($sourceInv)) {
					foreach ($sourceInv as $key => $base) {
						$newInvData[$key][$mapp['target']->getName()] = $base[$mapp['source']->getName()];
						$fieldInventoryModel = $inventoryFields ? $inventoryFields[$mapp['source']->getName()] : [];
						if ($fieldInventoryModel && $fieldInventoryModel->getCustomColumn()) {
							foreach (array_keys($fieldInventoryModel->getCustomColumn()) as $customColumn) {
								if (array_key_exists($customColumn, $base)) {
									$newInvData[$key][$customColumn] = $base[$customColumn];
								}
							}
						}
					}
				} elseif ((is_object($mapp['target']) && is_object($mapp['source'])) && getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), $mapp['source']->getName()) == 0 && in_array($mapp['source']->getName(), $parentFieldsList)) {
					$parentMapName = $parentRecordModel->get($mapp['source']->getName());
					if ($mapp['source']->getName() == 'shownerid' && empty($parentMapName)) {
						$fieldInstance = Vtiger_Field_Model::getInstance($mapp['source']->getName(), $parentRecordModel->getModule());
						$parentRecordModel->set($mapp['source']->getName(), $fieldInstance->getUITypeModel()->getEditViewDisplayValue('', $parentRecordModel->getId()));
					}
					$value = $parentRecordModel->get($mapp['source']->getName());
					if (!$value) {
						$value = $mapp['default'];
					}
					$this->set($mapp['target']->getName(), $value);
				}
			}
			if ($newInvData) {
				$this->inventoryData = $newInvData;
			}
		}
	}

	public function getListFieldsToGenerate($parentModuleName, $moduleName)
	{
		$module = CRMEntity::getInstance($parentModuleName);
		return $module->fieldsToGenerate[$moduleName] ? $module->fieldsToGenerate[$moduleName] : [];
	}

	public function getInventoryDefaultDataFields()
	{
		$lastItem = end($this->getInventoryData());
		$defaultData = [];
		if (!empty($lastItem)) {
			$items = ['discountparam', 'currencyparam', 'taxparam', 'taxmode', 'discountmode'];
			foreach ($items as $key) {
				$defaultData[$key] = isset($lastItem[$key]) ? $lastItem[$key] : null;
			}
		}
		return $defaultData;
	}

	/**
	 * Loading the inventory data
	 * @return array inventory data
	 */
	public function getInventoryData()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__);
		if (!$this->inventoryData) {
			$module = $this->getModuleName();
			$record = $this->getId();
			if (empty($record)) {
				$record = $this->get('record_id');
			}
			if (empty($record)) {
				return [];
			}
			$this->inventoryData = self::getInventoryDataById($record, $module);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
		return $this->inventoryData;
	}

	public static function getInventoryDataById($ID, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$inventoryField = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventoryField->getTableName('data');
		$result = $db->pquery(sprintf('SELECT * FROM %s WHERE id = ? ORDER BY seq', $table), [$ID]);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			$fields[] = $row;
		}
		return $fields;
	}

	/**
	 * Save the inventory data
	 */
	public function initInventoryData()
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__);

		$moduleName = $this->getModuleName();
		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$fields = $inventory->getColumns();
		$table = $inventory->getTableName('data');
		$summaryFields = $inventory->getSummaryFields();
		$inventoryData = $summary = [];
		if ($this->has('inventoryData')) {
			$request = $this->get('inventoryData');
		} else {
			$request = AppRequest::init();
		}
		if ($request->has('inventoryItemsNo')) {
			$numRow = $request->get('inventoryItemsNo');
			for ($i = 1; $i <= $numRow; $i++) {
				if (!$request->has(reset($fields)) && !$request->has(reset($fields) . $i)) {
					continue;
				}
				$insertData = ['seq' => $request->get('seq' . $i)];
				foreach ($fields as $field) {
					$insertData[$field] = $inventory->getValueForSave($request, $field, $i);
				}
				$inventoryData[] = $insertData;
			}
			$prefix = 'sum_';
			$inventoryFields = $inventory->getFields();
			foreach ($summaryFields as $fieldName) {
				if ($this->has($prefix . $fieldName)) {
					$value = $inventoryFields[$fieldName]->getSummaryValuesFromData($inventoryData);
					$this->set($prefix . $fieldName, $value);
				}
			}
		}
		$this->inventoryData = $inventoryData;
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__);
	}

	/**
	 * Function to get EditFieldByModal view url for the record
	 * @return <String> - EditFieldByModal View Url
	 */
	public function getEditFieldByModalUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=EditFieldByModal&record=' . $this->getId();
	}

	public function getFieldToEditByModal()
	{
		return [
			'addClass' => '',
			'iconClass' => '',
			'listViewClass' => '',
			'titleTag' => '',
			'name' => '',
		];
	}

	public function editFieldByModalPermission($profileAction = false)
	{
		if (isset($this->privileges['editFieldByModal']) && $this->privileges['editFieldByModal'] === true && $profileAction) {
			return Users_Privileges_Model::isPermitted($this->getModuleName(), 'OpenRecord', $this->getId());
		}
		return (bool) $this->privileges['editFieldByModal'];
	}

	public function setInventoryData($data)
	{
		$this->inventoryData = $data;
	}

	public function clearPrivilegesCache($name = false)
	{
		$privilegesName = ['isEditable', 'isCreateable', 'isViewable'];
		foreach ($privilegesName as $name) {
			if (!empty($name) && isset($this->privileges[$name])) {
				unset($this->privileges[$name]);
			}
		}
		Users_Privileges_Model::clearLockEditCache($this->getModuleName() . $this->getId());
		$wsId = vtws_getWebserviceEntityId($this->getModuleName(), $this->getId());
		VTEntityCache::setCachedEntity($wsId, false);
	}
}
