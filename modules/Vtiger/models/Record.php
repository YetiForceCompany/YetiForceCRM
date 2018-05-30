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
 * Vtiger Entity Record Model Class.
 */
class Vtiger_Record_Model extends \App\Base
{
	protected $module = false;
	protected $inventoryData;
	protected $inventoryDataExist = false;
	protected $inventoryRawData;
	protected $privileges = [];
	protected $fullForm = true;
	protected $changes = [];
	protected $handlerExceptions;
	public $summaryRowCount = 4;
	public $isNew = true;
	public $ext = [];

	/**
	 * Function to get the id of the record.
	 *
	 * @return int - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record.
	 *
	 * @param int $value - id value
	 */
	public function setId($value)
	{
		return $this->set('id', (int) $value);
	}

	/**
	 * Is new record.
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return $this->isNew;
	}

	/**
	 * Function to set the value for a given key.
	 *
	 * @param $key
	 * @param $value
	 */
	public function set($key, $value)
	{
		if (!$this->isNew && !in_array($key, ['mode', 'id', 'newRecord', 'modifiedtime', 'modifiedby', 'createdtime']) && $this->value[$key] != $value) {
			$this->changes[$key] = $this->get($key);
		}
		$this->value[$key] = $value;

		return $this;
	}

	/**
	 * Function to set the value for a given key and user farmat.
	 *
	 * @param string $fieldName
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setFromUserValue($fieldName, $value)
	{
		if ($value === '') {
			return $this;
		}
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		$this->set($fieldName, $fieldModel->getUITypeModel()->getDBValue($value, $this));

		return $this;
	}

	/**
	 * Fuction to get the Name of the record.
	 *
	 * @return string - Entity Name of the record
	 */
	public function getName()
	{
		$displayName = $this->get('label');
		if (empty($displayName)) {
			return $this->getDisplayName();
		}

		return \App\Purifier::encodeHtml($displayName);
	}

	/**
	 * Get pevious value by field.
	 *
	 * @param string $key
	 *
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
	 * Set full form.
	 *
	 * @param bool $value
	 */
	public function setFullForm($value)
	{
		$this->fullForm = $value;
	}

	public function getSearchName()
	{
		$displayName = $this->get('searchlabel');

		return \App\Purifier::encodeHtml(App\Purifier::decodeHtml($displayName));
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
	 * Function to get the Module to which the record belongs.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);

		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance.
	 *
	 * @param Vtiger_Module_Model $module
	 *
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module)
	{
		$this->module = $module;

		return $this;
	}

	/**
	 * Function to get the entity instance of the recrod.
	 *
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
	 * Function to set the entity instance of the record.
	 *
	 * @param CRMEntity $entity
	 *
	 * @return Vtiger_Record_Model instance
	 */
	public function setEntity($entity)
	{
		$this->entity = $entity;

		return $this;
	}

	/**
	 * Function to get raw data.
	 *
	 * @return <Array>
	 */
	public function getRawData()
	{
		return isset($this->rawData) ? $this->rawData : false;
	}

	/**
	 * Function to set raw data.
	 *
	 * @param <Array> $data
	 *
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
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getFullDetailViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getDetailViewName() . '&record=' . $this->getId() . '&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record.
	 *
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . ($this->getId() ? '&record=' . $this->getId() : '');
	}

	/**
	 * Function to get the Update View url for the record.
	 *
	 * @return string - Record Upadte view Url
	 */
	public function getUpdatesUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES';
	}

	/**
	 * Timeline view URL.
	 *
	 * @return string
	 */
	public function getTimeLineUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=TimeLineModal&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record.
	 *
	 * @return string - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&action=' . $module->getDeleteActionName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the name of the module to which the record belongs.
	 *
	 * @return string - Record Module Name
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record.
	 *
	 * @return string - Entity Display Name for the record
	 */
	public function getDisplayName()
	{
		return \App\Record::getLabel($this->getId());
	}

	/**
	 * Function to retieve display value for a field.
	 *
	 * @param string   $fieldName Field name for which values need to get
	 * @param int|bool $record    Record Id
	 * @param bool     $rawText
	 * @param int|bool $length    Length of the text
	 *
	 * @return bool
	 */
	public function getDisplayValue($fieldName, $record = false, $rawText = false, $length = false)
	{
		if (empty($record)) {
			$record = $this->getId();
		}
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		if ($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $record, $this, $rawText, $length);
		}
		$fieldModelByColumn = $this->getModule()->getFieldByColumn($fieldName);
		if ($fieldModelByColumn) {
			return $fieldModelByColumn->getDisplayValue($this->get($fieldName), $record, $this, $rawText, $length);
		}

		return false;
	}

	/**
	 * Function to get the display value in RelatedListView.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getRelatedListViewDisplayValue($fieldName)
	{
		$recordId = $this->getId();
		$fieldModel = $this->getModule()->getFieldByName($fieldName);

		return $fieldModel->getUITypeModel()->getRelatedListViewDisplayValue($this->get($fieldName), $recordId, $this);
	}

	/**
	 * Function to get the display value in ListView.
	 *
	 * @param string $fieldName
	 * @param bool   $rawText
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getListViewDisplayValue($fieldName, $rawText = false)
	{
		return $this->getModule()->getFieldByName($fieldName)->getUITypeModel()->getListViewDisplayValue($this->get($fieldName), $this->getId(), $this, $rawText);
	}

	/**
	 * Function returns the Vtiger_Field_Model.
	 *
	 * @param string $fieldName - field name
	 *
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName)
	{
		return $this->getModule()->getFieldByName($fieldName);
	}

	/**
	 * Function returns all the field values in user format.
	 *
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
	 * Function to save the current Record Model.
	 */
	public function save()
	{
		$moduleModel = $this->getModule();
		if ($moduleModel->isInventory()) {
			$this->initInventoryData();
		}
		$moduleName = $moduleModel->get('name');
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this);
		$eventHandler->setModuleName($moduleName);
		if ($this->getHandlerExceptions()) {
			$eventHandler->setExceptions($this->getHandlerExceptions());
		}
		$eventHandler->trigger('EntityBeforeSave');
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			if (!$this->isNew() && !$this->isMandatorySave() && empty($this->getPreviousValue())) {
				App\Log::info('ERR_NO_DATA');
			} else {
				if (method_exists($this, 'validate')) {
					$this->validate();
				}
				$this->saveToDb();
				if (method_exists($this, 'afterSaveToDb')) {
					$this->afterSaveToDb();
				}
			}
			$recordId = $this->getId();
			Users_Privileges_Model::setSharedOwner($this->get('shownerid'), $recordId);
			if ($moduleModel->isInventory()) {
				$this->saveInventoryData($moduleName);
			}
			if (\App\Request::_get('createmode') === 'link') {
				// vtlib customization: Hook provide to enable generic module relation.
				if (\App\Request::_has('return_module') && \App\Request::_has('return_id')) {
					vtlib\Deprecated::relateEntities(CRMEntity::getInstance(\App\Request::_get('return_module')), \App\Request::_get('return_module'), \App\Request::_get('return_id'), $moduleName, $recordId);
				}
			}
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		$eventHandler->trigger('EntityAfterSave');
		if ($this->isNew()) {
			\App\Cache::staticSave('RecordModel', $this->getId() . ':' . $this->getModuleName(), $this);
			$this->isNew = false;
		}
		\App\Cache::delete('recordLabel', $this->getId());
		\App\PrivilegeUpdater::updateOnRecordSave($this);
	}

	/**
	 * Save data to the database.
	 */
	public function saveToDb()
	{
		$entityInstance = $this->getModule()->getEntityInstance();
		$db = \App\Db::getInstance();
		foreach ($this->getValuesForSave() as $tableName => $tableData) {
			if ($this->isNew()) {
				if ($tableName === 'vtiger_crmentity') {
					$db->createCommand()->insert($tableName, $tableData)->execute();
					$this->setId((int) $db->getLastInsertID('vtiger_crmentity_crmid_seq'));
				} else {
					$db->createCommand()->insert($tableName, [$entityInstance->tab_name_index[$tableName] => $this->getId()] + $tableData)->execute();
				}
			} else {
				$db->createCommand()->update($tableName, $tableData, [$entityInstance->tab_name_index[$tableName] => $this->getId()])->execute();
			}
		}
	}

	/**
	 * Prepare value to save.
	 *
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
				$uitypeModel = $fieldModel->getUITypeModel();
				$uitypeModel->validate($value);
				if ($value === '' || $value === null) {
					$defaultValue = $fieldModel->getDefaultFieldValue();
					if ($defaultValue !== '') {
						$value = $defaultValue;
					} else {
						$value = $uitypeModel->getDBValue($value, $this);
					}
				}
				$forSave[$fieldModel->getTableName()][$fieldModel->getColumnName()] = $uitypeModel->convertToSave($value, $this);
			}
		}

		return $forSave;
	}

	/**
	 * Get entity data for save.
	 *
	 * @return array
	 */
	public function getEntityDataForSave()
	{
		$row = [];
		$time = date('Y-m-d H:i:s');
		if ($this->isNew()) {
			$row['setype'] = $this->getModuleName();
			$row['users'] = ',' . \App\User::getCurrentUserId() . ',';
			$row['smcreatorid'] = $this->isEmpty('created_user_id') ? \App\User::getCurrentUserRealId() : $this->get('created_user_id');
			$row['createdtime'] = $this->isEmpty('createdtime') ? $time : $this->get('createdtime');
			$this->set('createdtime', $row['createdtime']);
		}
		$row['modifiedtime'] = $this->getPreviousValue('modifiedtime') ? $this->get('modifiedtime') : $time;
		$row['modifiedby'] = $this->getPreviousValue('modifiedby') ? $this->get('modifiedby') : \App\User::getCurrentUserRealId();
		$this->set('modifiedtime', $row['modifiedtime']);
		$this->set('modifiedby', $row['modifiedby']);

		return ['vtiger_crmentity' => $row];
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			$moduleName = $this->getModuleName();
			$eventHandler = new App\EventHandler();
			$eventHandler->setRecordModel($this);
			$eventHandler->setModuleName($moduleName);
			$eventHandler->trigger('EntityBeforeDelete');

			$focus = $this->getModule()->getEntityInstance();
			if (method_exists($focus, 'transferRelatedRecords') && $this->get('transferRecordIDs')) {
				$focus->transferRelatedRecords($moduleName, $this->get('transferRecordIDs'), $this->getId());
			}
			Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
			Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
			$workflows = (new VTWorkflowManager())->getWorkflowsForModule($moduleName, VTWorkflowManager::$ON_DELETE);
			if (count($workflows)) {
				foreach ($workflows as &$workflow) {
					if ($workflow->evaluate($this)) {
						$workflow->performTasks($this);
					}
				}
			}
			$dbCommand = $db->createCommand();
			$dbCommand->delete('u_#__crmentity_label', ['crmid' => $this->getId()])->execute();
			$dbCommand->delete('u_#__crmentity_search_label', ['crmid' => $this->getId()])->execute();
			$dbCommand->delete('vtiger_crmentity', ['crmid' => $this->getId()])->execute();
			\App\Db::getInstance('admin')->createCommand()->delete('s_#__privileges_updater', ['crmid' => $this->getId()])->execute();
			Vtiger_MultiImage_UIType::deleteRecord($this);
			$eventHandler->trigger('EntityAfterDelete');
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Static Function to get the instance of a clean Vtiger Record Model for the given module name.
	 *
	 * @param string $moduleName
	 *
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
		$instance->setData($focus->column_fields)->setEntity($focus);
		\App\Cache::staticSave('RecordModelCleanInstance', $moduleName, clone $instance);
		return $instance;
	}

	/**
	 * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name.
	 *
	 * @param int    $recordId
	 * @param string $module
	 *
	 * @return \Vtiger_Record_Model Module Specific Record Model instance
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
		$focus->retrieveEntityInfo($recordId, $moduleName);
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
		$recordModel->setData($focus->column_fields)->setId($recordId)->setModuleFromInstance($moduleModel)->setEntity($focus);

		return $recordModel;
	}

	/**
	 * Static Function to get the list of records matching the search key.
	 *
	 * @param string $searchKey
	 *
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
		foreach ($rows as $row) {
			$ids[] = $row['crmid'];
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \App\Record::getLabel($ids);
		foreach ($rows as $row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = App\Purifier::decodeHtml($labels[$row['crmid']]);
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
			$this->privileges['isViewable'] = \App\Privilege::isPermitted($this->getModuleName(), 'DetailView', $this->getId());
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

			$isPermitted = \App\Privilege::isPermitted($moduleName, 'EditView', $recordId);
			$checkLockEdit = Users_Privileges_Model::checkLockEdit($moduleName, $this);
			$this->privileges['isEditable'] = $isPermitted && $this->checkLockFields() && $checkLockEdit === false;
		}

		return $this->privileges['isEditable'];
	}

	/**
	 * The function decide about mandatory save record.
	 *
	 * @return type
	 */
	public function isMandatorySave()
	{
		if ($this->getModule()->isInventory()) {
			return true;
		}

		return false;
	}

	/**
	 * The function decide about locking the record by field.
	 *
	 * @return bool
	 */
	public function checkLockFields()
	{
		if (!isset($this->privileges['isNoLockByField'])) {
			$isNoLock = true;
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
					$focus->retrieveEntityInfo($recordId, $moduleName);
					$this->setEntity($focus);
				}
				foreach ($lockFields as $fieldName => $values) {
					foreach ($values as $value) {
						if ($this->get($fieldName) == $value) {
							$isNoLock = false;
							break 2;
						}
						if (isset($focus->column_fields[$fieldName]) && $focus->column_fields[$fieldName] == $value) {
							$isNoLock = false;
							break 2;
						}
					}
				}
			}
			$this->privileges['isNoLockByField'] = $isNoLock;
		}

		return $this->privileges['isNoLockByField'];
	}

	/**
	 * Checking for permission to delete.
	 *
	 * @return bool
	 */
	public function privilegeToDelete()
	{
		if (!isset($this->privileges['Deleted'])) {
			$this->privileges['Deleted'] = \App\Privilege::isPermitted($this->getModuleName(), 'Delete', $this->getId());
		}

		return $this->privileges['Deleted'];
	}

	/**
	 * Checking for permission to move to trash.
	 *
	 * @return bool
	 */
	public function privilegeToMoveToTrash()
	{
		if (!isset($this->privileges['MoveToTrash'])) {
			$this->privileges['MoveToTrash'] = \App\Record::getState($this->getId()) !== 'Trash' && \App\Privilege::isPermitted($this->getModuleName(), 'MoveToTrash', $this->getId());
		}

		return $this->privileges['MoveToTrash'];
	}

	/**
	 * Checking for permission to archive.
	 *
	 * @return bool
	 */
	public function privilegeToArchive()
	{
		if (!isset($this->privileges['Archive'])) {
			$this->privileges['Archive'] = \App\Record::getState($this->getId()) !== 'Archived' && \App\Privilege::isPermitted($this->getModuleName(), 'ArchiveRecord', $this->getId());
		}

		return $this->privileges['Archive'];
	}

	/**
	 * Checking for permission to activate.
	 *
	 * @return bool
	 */
	public function privilegeToActivate()
	{
		if (!isset($this->privileges['Activate'])) {
			$this->privileges['Activate'] = \App\Record::getState($this->getId()) !== 'Active' && \App\Privilege::isPermitted($this->getModuleName(), 'ActiveRecord', $this->getId());
		}

		return $this->privileges['Activate'];
	}

	/**
	 * Funtion to get Duplicate Record Url.
	 *
	 * @return string
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get Display value for RelatedList.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getRelatedListDisplayValue($fieldName)
	{
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
	}

	/**
	 * Function to get Descrption value for this record.
	 *
	 * @return string Descrption
	 */
	public function getDescriptionValue()
	{
		$description = $this->get('description');
		if (empty($description)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT description FROM vtiger_crmentity WHERE crmid = ?', [$this->getId()]);
			$description = $db->queryResult($result, 0, 'description');
		}

		return $description;
	}

	/**
	 * Function to transfer related records of parent records to this record.
	 *
	 * @param <Array> $recordIds
	 *
	 * @return bool true/false
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
					$blockObiect = new $tmp[0]();
					if (isset($blockObiect->reference) && !\App\Module::isModuleActive($blockObiect->reference)) {
						continue;
					}
					$summaryBlocks[(int) ($blockCount / $this->summaryRowCount)][$blockObiect->sequence] = ['name' => $blockObiect->name, 'data' => $blockObiect->process($this), 'reference' => $blockObiect->reference];
					++$blockCount;
				}
			}
		}
		foreach ($summaryBlocks as $key => $block) {
			ksort($summaryBlocks[$key]);
		}

		return $summaryBlocks;
	}

	/**
	 * Function to set record module field values.
	 *
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
						if ($fieldName === 'shownerid') {
							$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $parentRecordModel->getModule());
							$parentRecordModel->set($fieldName, $fieldInstance->getUITypeModel()->getSharedOwners($parentRecordModel->getId()));
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
					if ($mapp['source']->getName() === 'shownerid' && empty($parentMapName)) {
						$fieldInstance = Vtiger_Field_Model::getInstance($mapp['source']->getName(), $parentRecordModel->getModule());
						$parentRecordModel->set($mapp['source']->getName(), $fieldInstance->getUITypeModel()->getSharedOwners($parentRecordModel->getId()));
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

	/**
	 * Set inventory data.
	 *
	 * @param array $data
	 */
	public function setInventoryData($data)
	{
		$this->inventoryData = $data;
	}

	/**
	 * Set inventory raw data.
	 *
	 * @param array $data
	 */
	public function setInventoryRawData($data)
	{
		$this->inventoryRawData = $data;
	}

	/**
	 * Save the inventory data.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public function saveInventoryData($moduleName)
	{
		if (!$this->inventoryDataExist) {
			return false;
		}
		\App\Log::trace('Start ' . __METHOD__);
		$db = App\Db::getInstance();
		$inventory = Vtiger_InventoryField_Model::getInstance($moduleName);
		$table = $inventory->getTableName('data');

		$inventoryData = $this->getInventoryData();
		$db->createCommand()->delete($table, ['id' => $this->getId()])->execute();
		if (is_array($inventoryData)) {
			foreach ($inventoryData as $insertData) {
				$insertData['id'] = $this->getId();
				$db->createCommand()->insert($table, $insertData)->execute();
			}
		}
		\App\Log::trace('End ' . __METHOD__);
	}

	/**
	 * Function to gets inventory default data fields.
	 *
	 * @return string|int|null
	 */
	public function getInventoryDefaultDataFields()
	{
		$inventoryData = $this->getInventoryData();
		$lastItem = end($inventoryData);
		$defaultData = [];
		if (!empty($lastItem)) {
			$items = ['discountparam', 'currencyparam', 'taxparam', 'taxmode', 'discountmode'];
			foreach ($items as $key) {
				$defaultData[$key] = $lastItem[$key] ?? null;
			}
		}

		return $defaultData;
	}

	/**
	 * Loading the inventory data.
	 *
	 * @return array inventory data
	 */
	public function getInventoryData()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		if (!isset($this->inventoryData)) {
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

	/**
	 * Function to get data of inventory for record.
	 *
	 * @param int    $id
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getInventoryDataById($id, $moduleName)
	{
		$table = Vtiger_InventoryField_Model::getInstance($moduleName)->getTableName('data');

		return (new \App\Db\Query())->from($table)->where(['id' => $id])->orderBy(['seq' => SORT_ASC])->all();
	}

	/**
	 * Save the inventory data.
	 */
	public function initInventoryData()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$inventory = Vtiger_InventoryField_Model::getInstance($this->getModuleName());
		$fields = $inventory->getFields();
		$summaryFields = $inventory->getSummaryFields();
		$inventoryData = [];
		if (isset($this->inventoryRawData)) {
			$request = $this->inventoryRawData;
		} else {
			$request = App\Request::init();
		}
		if ($request->has('inventoryItemsNo')) {
			$numRow = $request->getInteger('inventoryItemsNo');
			for ($i = 1; $i <= $numRow; ++$i) {
				if (!$request->has('name') && !$request->has('name' . $i)) {
					continue;
				}
				$insertData = ['seq' => $request->getInteger('seq' . $i)];
				foreach ($fields as $field) {
					$field->getValueFromRequest($insertData, $request, $i);
				}
				$inventoryData[] = $insertData;
			}
			foreach ($summaryFields as $fieldName) {
				if ($this->has('sum_' . $fieldName)) {
					$value = $fields[$fieldName]->getSummaryValuesFromData($inventoryData);
					$this->set('sum_' . $fieldName, $value);
				}
			}
			$this->inventoryData = $inventoryData;
			$this->inventoryDataExist = true;
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Clear privileges.
	 */
	public function clearPrivilegesCache()
	{
		$privilegesName = ['isEditable', 'isCreateable', 'isViewable', 'isNoLockByField'];
		foreach ($privilegesName as $name) {
			if (!empty($name) && isset($this->privileges[$name])) {
				unset($this->privileges[$name]);
			}
		}
		Users_Privileges_Model::clearLockEditCache($this->getModuleName() . $this->getId());
	}

	/**
	 * Set handler exceptions.
	 *
	 * @param array $exceptions
	 */
	public function setHandlerExceptions($exceptions)
	{
		$this->handlerExceptions = $exceptions;
	}

	/**
	 * get handler exceptions.
	 *
	 * @return array
	 */
	public function getHandlerExceptions()
	{
		return $this->handlerExceptions;
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordListViewLinksRightSide()
	{
		$links = $recordLinks = [];
		if ($this->isEditable() && $this->isCanAssignToHimself()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_RIGHT_SIDE',
				'linklabel' => 'BTN_ASSIGN_TO_ME',
				'linkurl' => 'javascript:Vtiger_Index_Js.assignToOwner(this)',
				'linkicon' => 'fas fa-user',
				'linkclass' => 'btn-sm btn-success',
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId()],
			];
		}
		if ($this->isEditable() && $this->autoAssignRecord()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_RIGHT_SIDE',
				'linklabel' => 'BTN_ASSIGN_TO',
				'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=AutoAssignRecord&record=' . $this->getId(),
				'linkicon' => 'fas fa-random',
				'linkclass' => 'btn-sm btn-primary',
				'modalView' => true,
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordListViewLinksLeftSide()
	{
		$links = $recordLinks = [];
		if ($this->isViewable() && $this->getModule()->isSummaryViewSupported()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SHOW_QUICK_DETAILS',
				'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $this->getId(),
				'linkicon' => 'far fa-caret-square-right',
				'linkclass' => 'btn-sm btn-default',
				'modalView' => true,
			];
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
				'linkurl' => $this->getFullDetailViewUrl(),
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-default',
				'linkhref' => true,
			];
		}
		if ($this->isEditable()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn-sm btn-default',
				'linkhref' => true,
			];
		}
		if ($this->isViewable() && $this->getModule()->isPermitted('WatchingRecords')) {
			$watching = (int) ($this->isWatchingRecord());
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_WATCHING_RECORD',
				'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
				'linkicon' => 'fas ' . ($watching ? 'fa-eye-slash' : 'fa-eye'),
				'linkclass' => 'btn-sm ' . ($watching ? 'btn-info' : 'btn-default'),
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => (int) !$watching, 'on' => 'btn-info', 'off' => 'btn-default', 'icon-on' => 'fa-eye', 'icon-off' => 'fa-eye-slash'],
			];
		}
		$stateColors = AppConfig::search('LIST_ENTITY_STATE_COLOR');
		if ($this->privilegeToActivate()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_ACTIVATE_RECORD',
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Active',
				'linkicon' => 'fas fa-undo-alt',
				'style' => empty($stateColors['Active']) ? '' : "background: {$stateColors['Active']};",
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC')],
				'linkclass' => 'btn-sm btn-default recordEvent entityStateBtn',
			];
		}
		if ($this->privilegeToArchive()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_ARCHIVE_RECORD',
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Archived',
				'linkicon' => 'fas fa-archive',
				'style' => empty($stateColors['Archived']) ? '' : "background: {$stateColors['Archived']};",
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC')],
				'linkclass' => 'btn-sm btn-default recordEvent entityStateBtn',
			];
		}
		if ($this->privilegeToMoveToTrash()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_MOVE_TO_TRASH',
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Trash',
				'linkicon' => 'fas fa-trash-alt',
				'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
				'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC')],
				'linkclass' => 'btn-sm btn-default recordEvent entityStateBtn',
			];
		}
		if ($this->privilegeToDelete()) {
			$recordLinks[] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
				'linkicon' => 'fas fa-eraser',
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=Delete',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
				'linkclass' => 'btn-sm btn-black recordEvent',
			];
		}
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Get the related list view actions for the record.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$stateColors = AppConfig::search('LIST_ENTITY_STATE_COLOR');
		$links = [];
		if ($this->isViewable()) {
			if ($this->getModule()->isSummaryViewSupported()) {
				$defaultViewName = $viewModel->getParentRecordModel()->getModule()->getDefaultViewName();
				$links['LBL_SHOW_QUICK_DETAILS'] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_SHOW_QUICK_DETAILS',
						'linkhref' => $defaultViewName === 'ListPreview' ? false : true,
						'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $this->getId(),
						'linkicon' => 'far fa-caret-square-right',
						'linkclass' => 'btn-sm btn-default',
						'modalView' => true,
				]);
			}
			$links['LBL_SHOW_COMPLETE_DETAILS'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
					'linkurl' => $this->getFullDetailViewUrl(),
					'linkhref' => true,
					'linkicon' => 'fas fa-th-list',
					'linkclass' => 'btn-sm btn-default',
			]);
		}
		$relationModel = $viewModel->getRelationModel();
		if ($relationModel->isEditable() && $this->isEditable()) {
			$links['LBL_EDIT'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_EDIT',
					'linkhref' => true,
					'linkurl' => $this->getEditViewUrl(),
					'linkicon' => 'fas fa-edit',
					'linkclass' => 'btn-sm btn-default',
			]);
		}
		if ($this->isViewable() && $this->getModule()->isPermitted('WatchingRecords')) {
			$watching = (int) ($this->isWatchingRecord());
			$links['BTN_WATCHING_RECORD'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'BTN_WATCHING_RECORD',
					'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
					'linkicon' => 'fas ' . ($watching ? 'fa-eye-slash' : 'fa-eye'),
					'linkclass' => 'btn-sm ' . ($watching ? 'btn-info' : 'btn-default'),
					'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => (int) !$watching, 'on' => 'btn-info', 'off' => 'btn-default', 'icon-on' => 'fa-eye', 'icon-off' => 'fa-eye-slash'],
			]);
		}
		if ($relationModel->privilegeToDelete() && $this->privilegeToMoveToTrash()) {
			$links['LBL_DELETE'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_DELETE',
					'linkicon' => 'fas fa-trash-alt',
					'linkclass' => 'btn-sm btn-default relationDelete entityStateBtn',
					'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
			]);
		}

		return $links;
	}

	/**
	 * Function checks if user can assign record to himself.
	 *
	 * @return bool
	 */
	public function isCanAssignToHimself()
	{
		return \App\Fields\Owner::getType($this->getValueByField('assigned_user_id')) === \App\PrivilegeUtil::MEMBER_TYPE_GROUPS &&
			array_key_exists(\App\User::getCurrentUserId(), \App\Fields\Owner::getInstance($this->getModuleName())->getAccessibleUsers('', 'owner'));
	}

	/**
	 * Function checks if user can use records auto assign mechanism.
	 *
	 * @return bool
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
	 * Function gets the value from this record.
	 *
	 * @param string $fieldName
	 *
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

	/**
	 * Clear changes.
	 */
	public function clearChanges()
	{
		unset($this->changes);
	}

	/**
	 * Change record state.
	 *
	 * @param type $state
	 */
	public function changeState($state)
	{
		$this->set('deleted', $state);
		$stateId = 0;
		switch ($state) {
			case 'Active':
				$stateId = 0;
				break;
			case 'Trash':
				$stateId = 1;
				break;
			case 'Archived':
				$stateId = 2;
				break;
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['deleted' => $stateId, 'modifiedtime' => date('Y-m-d H:i:s'), 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => $this->getId()])->execute();
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this);
		$eventHandler->setModuleName($this->getModuleName());
		if ($this->getHandlerExceptions()) {
			$eventHandler->setExceptions($this->getHandlerExceptions());
		}
		$eventHandler->trigger('EntityChangeState');
	}

	/**
	 * Get list view color for record.
	 *
	 * @return string[]
	 */
	public function getListViewColor()
	{
		$colors = [];
		$stateColors = AppConfig::search('LIST_ENTITY_STATE_COLOR');
		$state = \App\Record::getState($this->getId());
		if (!empty($stateColors[$state])) {
			$colors['leftBorder'] = $stateColors[$state];
		}

		return $colors;
	}

	/**
	 * Function to get record image.
	 *
	 * @return array
	 */
	public function getImage()
	{
		$image = [];
		if (!$this->isEmpty('imagename') && $this->get('imagename') !== '[]' && $this->get('imagename') !== '""') {
			$image = array_shift(\App\Json::decode($this->get('imagename')));
			$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
			$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field=imagename&record={$this->getId()}&key={$image['key']}";
		} else {
			foreach ($this->getModule()->getFieldsByType('multiImage') as $fieldModel) {
				if (!$this->isEmpty($fieldModel->getName()) && $this->get($fieldModel->getName()) !== '[]' && $this->get($fieldModel->getName()) !== '""') {
					$image = array_shift(\App\Json::decode($this->get($fieldModel->getName())));
					$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
					$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field={$fieldModel->getName()}&record={$this->getId()}&key={$image['key']}";
					break;
				}
			}
		}
		return $image;
	}
}
