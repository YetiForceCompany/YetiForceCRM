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
	protected $inventoryData;
	protected $inventoryRawData;
	protected $privileges = [];
	protected $fullForm = true;
	protected $changes = [];
	protected $handlerExceptions;
	public $summaryRowCount = 4;
	public $isNew = true;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function get($key)
	{
		return isset($this->valueMap[$key]) ? $this->valueMap[$key] : null;
	}

	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', $value);
	}

	/**
	 * Is new record
	 * @return boolean
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * Function to set the value for a given key
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		if (!$this->isNew && !in_array($key, ['mode', 'id', 'newRecord', 'modifiedtime', 'modifiedby', 'createdtime']) && $this->valueMap[$key] != $value) {
			$this->changes[$key] = $this->get($key);
		}
		$this->valueMap[$key] = $value;
		return $this;
	}

	/**
	 * Function to set the value for a given key and user farmat
	 * @param $fieldName
	 * @param $value
	 */
	public function setInUserFormat($fieldName, $value)
	{
		if ($value === '') {
			return $this;
		}
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		$this->set($fieldName, $fieldModel->getUITypeModel()->getDBValue($value, $this));
		return $this;
	}

	/**
	 * Fuction to get the Name of the record
	 * @return string - Entity Name of the record
	 */
	public function getName()
	{
		$displayName = $this->get('label');
		if (empty($displayName)) {
			$displayName = $this->getDisplayName();
		}
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	/**
	 * Get pevious value by field
	 * @param string $key
	 * @return mixed
	 */
	public function getPreviousValue($key = false)
	{
		if (!$key) {
			return $this->changes;
		}
		return isset($this->changes[$key]) ? $this->changes[$key] : false;
	}

	/**
	 * Set full form
	 * @param boolean $value
	 */
	public function setFullForm($value)
	{
		$this->fullForm = $value;
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
	 * @param string $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param Vtiger_Module_Model $module
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
			$this->entity = CRMEntity::getInstance($this->getModuleName());
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
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record
	 * @return string - Record Detail View Url
	 */
	public function getFullDetailViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId() . '&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Update View url for the record
	 * @return string - Record Upadte view Url
	 */
	public function getUpdatesUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES';
	}

	/**
	 * Timeline view URL
	 * @return string
	 */
	public function getTimeLineUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=TimeLineModal&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return string - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&action=' . $module->getDeleteActionName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return string - Record Module Name
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record
	 * @return string - Entity Display Name for the record
	 */
	public function getDisplayName()
	{
		return \App\Record::getLabel($this->getId());
	}

	/**
	 * Function to retieve display value for a field
	 * @param string $fieldName - field name for which values need to get
	 * @return string
	 */
	public function getDisplayValue($fieldName, $recordId = false, $rawText = false)
	{
		if (empty($recordId)) {
			$recordId = $this->getId();
		}
		$fieldModel = $this->getModule()->getField($fieldName);
		if ($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $recordId, $this, $rawText);
		}
		return false;
	}

	/**
	 * Function to get the display value in ReletedListView
	 * @param string $fieldName
	 * @return string
	 */
	public function getReletedListViewDisplayValue($fieldName)
	{
		$recordId = $this->getId();
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		return $fieldModel->getUITypeModel()->getReletedListViewDisplayValue($this->get($fieldName), $recordId, $this);
	}

	/**
	 * Function to get the display value in ListView
	 * @param string $fieldName
	 * @return string
	 */
	public function getListViewDisplayValue($fieldName)
	{
		$recordId = $this->getId();
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		return $fieldModel->getUITypeModel()->getListViewDisplayValue($this->get($fieldName), $recordId, $this);
	}

	/**
	 * Function returns the Vtiger_Field_Model
	 * @param string $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName)
	{
		return $this->getModule()->getFieldByName($fieldName);
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
		$db->startTransaction();
		if ($this->getModule()->isInventory()) {
			$this->initInventoryData();
		}
		$this->getModule()->saveRecord($this);
		$db->completeTransaction();

		if ($this->isNew()) {
			\App\Cache::staticSave('RecordModel', $this->getId() . ':' . $this->getModuleName(), $this);
		}
		\App\Cache::delete('recordLabel', $this->getId());
		\App\PrivilegeUpdater::updateOnRecordSave($this);
	}

	/**
	 * Save data to the database
	 */
	public function saveToDb()
	{
		$entityInstance = $this->getModule()->getEntityInstance();
		$db = \App\Db::getInstance();
		foreach ($this->getValuesForSave() as $tableName => &$tableData) {
			$keyTable = [$entityInstance->tab_name_index[$tableName] => $this->getId()];
			if ($this->isNew()) {
				if ($tableName === 'vtiger_crmentity') {
					$db->createCommand()->insert($tableName, $tableData)->execute();
					$this->setId($db->getLastInsertID('vtiger_crmentity_crmid_seq'));
				} else {
					$db->createCommand()->insert($tableName, $keyTable + $tableData)->execute();
				}
			} else {
				$db->createCommand()->update($tableName, $tableData, [$entityInstance->tab_name_index[$tableName] => $this->getId()])->execute();
			}
		}
	}

	/**
	 * Prepare value to save
	 * @return array
	 */
	public function getValuesForSave()
	{
		$moduleModel = $this->getModule();
		$saveFields = $this->getModule()->getFieldsForSave($this);
		$forSave = $this->getEntityDataForSave();
		if (!$this->isNew()) {
			$saveFields = array_intersect($saveFields, array_keys($this->changes));
		} else {
			$entityModel = $this->getEntity();
			$forSave[$entityModel->table_name] = [];
			if (!empty($entityModel->customFieldTable)) {
				$forSave[$entityModel->customFieldTable[0]] = [];
			}
		}
		foreach ($saveFields as &$fieldName) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			if ($fieldModel) {
				$value = $this->get($fieldName);
				if ($value === '' || $value === null) {
					$value = $fieldModel->getUITypeModel()->getDBValue($value, $this);
				}
				$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $value;
			}
		}
		return $forSave;
	}

	public function getEntityDataForSave()
	{
		$row = [];
		$time = date('Y-m-d H:i:s');
		if ($this->isNew()) {
			$row['setype'] = $this->getModuleName();
			$row['smcreatorid'] = \App\User::getCurrentUserRealId();
			$row['createdtime'] = $time;
			$row['users'] = ',' . \App\User::getCurrentUserId() . ',';
			$this->set('createdtime', $time);
		}
		$row['modifiedtime'] = $time;
		$row['modifiedby'] = \App\User::getCurrentUserRealId();
		$this->set('modifiedtime', $time);
		$this->set('modifiedby', \App\User::getCurrentUserRealId());
		return ['vtiger_crmentity' => $row];
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
	 * @param string $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getCleanInstance($moduleName)
	{
		if (\App\Cache::staticHas('RecordModelCleanInstance', $moduleName)) {
			return clone \App\Cache::staticGet('RecordModelCleanInstance', $moduleName);
		}
		$focus = CRMEntity::getInstance($moduleName);
		$module = Vtiger_Module_Model::getInstance($moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		$instance->setModuleFromInstance($module);
		$instance->isNew = true;
		$instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
		\App\Cache::staticSave('RecordModelCleanInstance', $moduleName, clone $instance);
		return $instance;
	}

	/**
	 * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name
	 * @param <Number> $recordId
	 * @param string $moduleName
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
			$moduleName = \App\Record::getType($recordId);
			$module = Vtiger_Module_Model::getInstance($moduleName);
		}
		$cacheName = "$recordId:$moduleName";
		if (\App\Cache::staticHas('RecordModel', $cacheName)) {
			return \App\Cache::staticGet('RecordModel', $cacheName);
		}

		$focus = CRMEntity::getInstance($moduleName);
		$focus->id = $recordId;
		$focus->retrieve_entity_info($recordId, $moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		$instance->setEntity($focus)->setData($focus->column_fields)->setModuleFromInstance($module);
		$instance->setId($recordId);
		$instance->isNew = false;
		\App\Cache::staticSave('RecordModel', $cacheName, $instance);
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
	 * @param string $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module = false, $limit = false, $operator = false)
	{
		if (!$limit) {
			$limit = AppConfig::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
		}
		$recordSearch = new \App\RecordSearch($searchKey, $module, $limit);
		if ($operator) {
			$recordSearch->operator = $operator;
		}
		$rows = $recordSearch->search();
		$ids = $matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \App\Record::getLabel($ids);

		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = $labels[$row['crmid']];
			$row['smownerid'] = $recordMeta['smownerid'];
			$row['createdtime'] = $recordMeta['createdtime'];
			$row['permitted'] = \App\Privilege::isPermitted($row['setype'], 'DetailView', $row['crmid']);
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
			$checkLockEdit = Users_Privileges_Model::checkLockEdit($moduleName, $this);

			$this->privileges['isEditable'] = $isPermitted && $this->checkLockFields() && $checkLockEdit === false;
		}
		return $this->privileges['isEditable'];
	}

	/**
	 * The function decide about mandatory save record
	 * @return type
	 */
	public function isMandatorySave()
	{
		if ($this->getModule()->isInventory()) {
			return true;
		}
		return false;
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
	 * @return string
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param string $value
	 * @return string
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
	 * @return string Descrption
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
	 * @return boolean true/false
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
					if (isset($blockObiect->reference) && !\App\Module::isModuleActive($blockObiect->reference)) {
						continue;
					}
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
		$db = PearDatabase::getInstance();
		$id = $this->getId();
		\App\Log::trace("Track the viewing of a detail record: vtiger_tracker (user_id, module_name, item_id)($id)");
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
		$mfInstance = Vtiger_MappedFields_Model::getInstanceByModules($parentRecordModel->getModule()->getId(), $this->getModule()->getId());
		if ($mfInstance) {
			$moduleFields = $this->getModule()->getFields();
			$fieldsList = array_keys($moduleFields);
			$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());
			$params = $mfInstance->get('params');
			if ($params['autofill']) {
				$commonFields = array_intersect($fieldsList, $parentFieldsList);
				foreach ($commonFields as $fieldName) {
					if (\App\Field::getFieldPermission($parentRecordModel->getModuleName(), $fieldName)) {
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
				} elseif ((is_object($mapp['target']) && is_object($mapp['source'])) && \App\Field::getFieldPermission($parentRecordModel->getModuleName(), $mapp['source']->getName()) && in_array($mapp['source']->getName(), $parentFieldsList)) {
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
		\App\Log::trace('Entering ' . __METHOD__);
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
		\App\Log::trace('Exiting ' . __METHOD__);
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

		\App\Log::trace('Entering ' . __METHOD__);

		$moduleName = $this->getModuleName();
		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$fields = $inventory->getColumns();
		$summaryFields = $inventory->getSummaryFields();
		$inventoryData = $summary = [];
		if (isset($this->inventoryRawData)) {
			$request = $this->inventoryRawData;
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
				foreach ($fields as &$field) {
					$insertData[$field] = $inventory->getValueForSave($request, $field, $i);
				}
				$inventoryData[] = $insertData;
			}
			$prefix = 'sum_';
			$inventoryFields = $inventory->getFields();
			foreach ($summaryFields as &$fieldName) {
				if ($this->has($prefix . $fieldName)) {
					$value = $inventoryFields[$fieldName]->getSummaryValuesFromData($inventoryData);
					$this->set($prefix . $fieldName, $value);
				}
			}
		}
		$this->inventoryData = $inventoryData;
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Function to get EditFieldByModal view url for the record
	 * @return string - EditFieldByModal View Url
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

	/**
	 * Set inventory data
	 * @param array $data
	 */
	public function setInventoryData($data)
	{
		$this->inventoryData = $data;
	}

	/**
	 * Set inventory raw data
	 * @param array $data
	 */
	public function setInventoryRawData($data)
	{
		$this->inventoryRawData = $data;
	}

	/**
	 * Save the inventory data
	 */
	public function saveInventoryData($moduleName)
	{
		\App\Log::trace('Start ' . __METHOD__);
		$db = App\Db::getInstance();
		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventory->getTableName('data');

		$inventoryData = $this->getInventoryData();
		$db->createCommand()->delete($table, ['id' => $this->getId()])->execute();
		if (is_array($inventoryData)) {
			foreach ($inventoryData as &$insertData) {
				$insertData['id'] = $this->getId();
				$db->createCommand()->insert($table, $insertData)->execute();
			}
		}
		\App\Log::trace('End ' . __METHOD__);
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
	}

	/**
	 * This function is used to upload the attachment in the server and save that attachment information in db.
	 * @param array $fileDetails  - array which contains the file information(name, type, size, tmp_name and error)
	 * @return boolean
	 */
	public function uploadAndSaveFile($fileDetails, $attachmentType = 'Attachment')
	{
		$id = $this->getId();
		$module = AppRequest::get('module');
		\App\Log::trace("Entering into uploadAndSaveFile($id,$module,$fileDetails) method.");
		$db = \App\Db::getInstance();
		$userId = \App\User::getCurrentUserId();
		$date = date('Y-m-d H:i:s');

		//to get the owner id
		$ownerid = $this->get('assigned_user_id');
		if (!isset($ownerid) || $ownerid === '')
			$ownerid = $userId;

		if (isset($fileDetails['original_name']) && $fileDetails['original_name'] != null) {
			$fileName = $fileDetails['original_name'];
		} else {
			$fileName = $fileDetails['name'];
		}

		$fileInstance = \App\Fields\File::loadFromRequest($fileDetails);
		if (!$fileInstance->validate()) {
			return false;
		}
		$binFile = \App\Fields\File::sanitizeUploadFileName($fileName);

		$filename = ltrim(basename(' ' . $binFile)); //allowed filename like UTF-8 characters
		$filetype = $fileDetails['type'];
		$filesize = $fileDetails['size'];
		$filetmp_name = $fileDetails['tmp_name'];

		//get the file path inwhich folder we want to upload the file
		$uploadFilePath = \vtlib\Functions::initStorageFileDirectory($module);

		$params = [
			'smcreatorid' => $userId,
			'smownerid' => $ownerid,
			'setype' => $module . ' Image',
			'description' => $this->get('description'),
			'createdtime' => $date,
			'modifiedtime' => $date
		];
		if ($module === 'Contacts' || $module === 'Products') {
			$params['setype'] = $module . ' Image';
		} else {
			$params['setype'] = $module . ' Attachment';
		}
		$db->createCommand()->insert('vtiger_crmentity', $params)->execute();
		$currentId = $db->getLastInsertID('vtiger_crmentity_crmid_seq');
		$uploadStatus = move_uploaded_file($filetmp_name, $uploadFilePath . $currentId . '_' . $binFile);
		if ($uploadStatus) {
			$db->createCommand()->insert('vtiger_attachments', [
				'attachmentsid' => $currentId,
				'name' => $filename,
				'description' => $this->get('description'),
				'type' => $filetype,
				'path' => $uploadFilePath
			])->execute();

			if (AppRequest::get('mode') === 'edit') {
				if (!empty($id) && !empty(AppRequest::get('fileid'))) {
					$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => AppRequest::get('fileid')])->execute();
				}
			}
			if ($module === 'Documents') {
				$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $id])->execute();
			}
			if ($module === 'Contacts') {
				$attachmentsId = (new \App\Db\Query())->select(['vtiger_seattachmentsrel.attachmentsid'])
					->from('vtiger_seattachmentsrel')
					->innerJoin('vtiger_crmentity', 'vtiger_seattachmentsrel.attachmentsid=vtiger_crmentity.crmid')
					->where(['vtiger_crmentity.setype' => 'Contacts Image', 'vtiger_seattachmentsrel.crmid' => $id])
					->scalar();
				if (!empty($attachmentsId)) {
					$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $attachmentsId])->execute();
					$db->createCommand()->delete('vtiger_crmentity', ['crmid' => $attachmentsId])->execute();
					$db->createCommand()->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $currentId])->execute();
				} else {
					$db->createCommand()->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $currentId])->execute();
				}
			} else {
				$db->createCommand()->insert('vtiger_seattachmentsrel', ['crmid' => $id, 'attachmentsid' => $currentId])->execute();
			}
			return true;
		} else {
			\App\Log::trace('Skip the save attachment process.');
			return false;
		}
	}

	/**
	 * Set handler exceptions
	 * @param array $exceptions
	 */
	public function setHandlerExceptions($exceptions)
	{
		$this->handlerExceptions = $exceptions;
	}

	/**
	 * get handler exceptions
	 * @return array
	 */
	public function getHandlerExceptions()
	{
		return $this->handlerExceptions;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordListViewLinksRightSide()
	{
		$links = $recordLinks = [];
		if ($this->isEditable() && $this->isCanAssignToHimself()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_RIGHT_SIDE',
				'linklabel' => 'BTN_REALIZE',
				'linkurl' => 'javascript:Vtiger_Index_Js.assignToOwner(this)',
				'linkicon' => 'glyphicon glyphicon-user',
				'linkclass' => 'btn-sm btn-success',
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId()],
			];
		}
		if ($this->isEditable() && $this->autoAssignRecord()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_RIGHT_SIDE',
				'linklabel' => 'BTN_ASSIGN_TO',
				'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=AutoAssignRecord&record=' . $this->getId(),
				'linkicon' => 'glyphicon glyphicon-random',
				'linkclass' => 'btn-sm btn-primary',
				'modalView' => true
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordListViewLinksLeftSide()
	{
		$links = $recordLinks = [];
		if ($this->isViewable()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
				'linkurl' => $this->getFullDetailViewUrl(),
				'linkicon' => 'glyphicon glyphicon-th-list',
				'linkclass' => 'btn-sm btn-default'
			];
		}
		if ($this->isEditable()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil',
				'linkclass' => 'btn-sm btn-default'
			];
		}
		if ($this->isViewable() && $this->getModule()->isPermitted('WatchingRecords')) {
			$watching = intval($this->isWatchingRecord());
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_WATCHING_RECORD',
				'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
				'linkicon' => 'glyphicon ' . ($watching ? 'glyphicon-eye-close' : 'glyphicon-eye-open'),
				'linkclass' => 'btn-sm ' . ($watching ? 'btn-info' : 'btn-default'),
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => !$watching, 'on' => 'btn-info', 'off' => 'btn-default', 'icon-on' => 'glyphicon-eye-open', 'icon-off' => 'glyphicon-eye-close'],
			];
		}
		if (($this->isEditable() && $this->editFieldByModalPermission()) || $this->editFieldByModalPermission(true)) {
			$fieldEditData = $this->getFieldToEditByModal();
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => $fieldEditData['titleTag'],
				'linkurl' => $this->getEditFieldByModalUrl(),
				'linkicon' => 'glyphicon ' . $fieldEditData['iconClass'],
				'linkclass' => 'btn-sm ' . $fieldEditData['addClass'],
				'modalView' => true
			];
		}
		if ($this->isDeletable()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_DELETE',
				'linkicon' => 'glyphicon glyphicon-trash',
				'linkclass' => 'btn-sm btn-default deleteRecordButton'
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function checks if user can assign record to himself
	 * @return boolean
	 */
	public function isCanAssignToHimself()
	{
		return \App\Fields\Owner::getType($this->getValueByField('assigned_user_id')) === \App\PrivilegeUtil::MEMBER_TYPE_GROUPS &&
			array_key_exists(\App\User::getCurrentUserId(), \App\Fields\Owner::getInstance($this->getModuleName())->getAccessibleUsers('', 'owner'));
	}

	/**
	 * Function checks if user can use records auto assign mechanism
	 * @return boolean
	 */
	public function autoAssignRecord()
	{
		if (\App\Fields\Owner::getType($this->getValueByField('assigned_user_id')) === \App\PrivilegeUtil::MEMBER_TYPE_GROUPS) {
			$userModel = \App\User::getCurrentUserModel();
			$roleData = \App\PrivilegeUtil::getRoleDetail($userModel->getRole());
			if (!empty($roleData['auto_assign'])) {
				$autoAssignModel = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
				$autoAssignRecord = $autoAssignModel->searchRecord($this, $userModel->getRole());
				return $autoAssignRecord ? true : false;
			}
		}
		return false;
	}

	/**
	 * Function gets the value from this record
	 * @param string $fieldName
	 * @return mixed
	 */
	public function getValueByField($fieldName)
	{
		if (!$this->has($fieldName)) {
			$fieldModel = $this->getModule()->getFieldByName($fieldName);
			$idName = $this->getEntity()->tab_name_index[$fieldModel->getTableName()];
			$value = \vtlib\Functions::getSingleFieldValue($fieldModel->getTableName(), $fieldModel->getColumnName(), $idName, $this->getId());
			$this->set($fieldModel->getName(), $value);
		}
		return $this->get($fieldName);
	}
}
