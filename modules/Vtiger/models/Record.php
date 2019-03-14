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
	private $inventoryData;
	protected $privileges = [];
	protected $fullForm = true;
	protected $changes = [];
	private $changesInventory = [];
	protected $handlerExceptions;
	public $summaryRowCount = 4;
	public $isNew = true;
	public $ext = [];
	protected $dataForSave = [];

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
		if (!$this->isNew && !in_array($key, ['mode', 'id', 'newRecord', 'modifiedtime', 'modifiedby', 'createdtime']) && (isset($this->value[$key]) && $this->value[$key] != $value)) {
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
		if ('' === $value) {
			return $this;
		}
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		$this->set($fieldName, $fieldModel->getUITypeModel()->getDBValue($value, $this));

		return $this;
	}

	/**
	 * Set data for save.
	 *
	 * @param array $array
	 */
	public function setDataForSave(array $array)
	{
		$this->dataForSave = array_merge($this->dataForSave, $array);
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
	public function getPreviousValue($key = '')
	{
		return $key ? ($this->changes[$key] ?? false) : $this->changes;
	}

	/**
	 * Gets previous values by inventory.
	 *
	 * @param null|int|string $key
	 *
	 * @return array|bool
	 */
	public function getPreviousInventoryItems($key = null)
	{
		return null !== $key ? ($this->changesInventory[$key] ?? false) : $this->changesInventory;
	}

	/**
	 * Gets previous values.
	 *
	 * @return array
	 */
	public function getChanges()
	{
		$changes = $this->getPreviousValue();
		if ($this->getModule()->isInventory() && ($prevInv = $this->getPreviousInventoryItems())) {
			$changes['inventory'] = $prevInv;
		}
		return $changes;
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
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $this->getModule()->getDetailViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getFullDetailViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $this->getModule()->getDetailViewName() . '&record=' . $this->getId() . '&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record.
	 *
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $this->getModule()->getEditViewName() . ($this->getId() ? '&record=' . $this->getId() : '');
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
		return 'index.php?module=' . $this->getModuleName() . '&action=' . $this->getModule()->getDeleteActionName() . '&record=' . $this->getId();
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
	 * @param bool|int $record    Record Id
	 * @param bool     $rawText
	 * @param bool|int $length    Length of the text
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
	 * @param string|Vtiger_Field_Model $field
	 * @param bool                      $rawText
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getListViewDisplayValue($field, $rawText = false)
	{
		if ($field instanceof Vtiger_Field_Model) {
			if (!empty($field->get('source_field_name')) && isset($this->ext[$field->get('source_field_name')][$field->getModuleName()])) {
				return $this->ext[$field->get('source_field_name')][$field->getModuleName()]->getListViewDisplayValue($field, $rawText);
			}
		} else {
			$field = $this->getModule()->getFieldByName($field);
		}
		return $field->getUITypeModel()->getListViewDisplayValue($this->get($field->getFieldName()), $this->getId(), $this, $rawText);
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
			}
			$recordId = $this->getId();
			Users_Privileges_Model::setSharedOwner($this->get('shownerid'), $recordId);
			if ('link' === \App\Request::_get('createmode') && \App\Request::_has('return_module') && \App\Request::_has('return_id')) {
				vtlib\Deprecated::relateEntities(CRMEntity::getInstance(\App\Request::_get('return_module')), \App\Request::_get('return_module'), \App\Request::_getInteger('return_id'), $moduleName, $recordId);
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
		\App\Cache::staticDelete('UnlockFields', $this->getId());
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
				if ('vtiger_crmentity' === $tableName) {
					$db->createCommand()->insert($tableName, $tableData)->execute();
					$this->setId((int) $db->getLastInsertID('vtiger_crmentity_crmid_seq'));
				} else {
					$db->createCommand()->insert($tableName, [$entityInstance->tab_name_index[$tableName] => $this->getId()] + $tableData)->execute();
				}
			} else {
				$db->createCommand()->update($tableName, $tableData, [$entityInstance->tab_name_index[$tableName] => $this->getId()])->execute();
			}
		}
		if ($this->getModule()->isInventory()) {
			$this->saveInventoryData();
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
			$saveFields = array_intersect($saveFields, array_merge(array_keys($this->changes), array_keys($moduleModel->getFieldsByUiType(4))));
		} else {
			$entityModel = $this->getEntity();
			$forSave[$entityModel->table_name] = [];
			if (!empty($entityModel->customFieldTable)) {
				$forSave[$entityModel->customFieldTable[0]] = [];
			}
		}
		foreach ($this->dataForSave as $tableName => $values) {
			$forSave[$tableName] = array_merge($forSave[$tableName] ?? [], $values);
		}
		foreach ($saveFields as &$fieldName) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			if ($fieldModel) {
				$value = $this->get($fieldName);
				$uitypeModel = $fieldModel->getUITypeModel();
				$uitypeModel->validate($value);
				if ('' === $value || null === $value) {
					$defaultValue = $fieldModel->getDefaultFieldValue();
					if ('' !== $defaultValue) {
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
		if (isset($focus->column_fields)) {
			$instance->setData($focus->column_fields);
		}
		$instance->setEntity($focus);
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
			if ('Leads' === $row['setype']) {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \App\Record::getLabel($ids);
		foreach ($rows as $row) {
			if ('Leads' === $row['setype'] && $convertedInfo[$row['crmid']]) {
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

	/**
	 * Function check if record is viewable.
	 *
	 * @return bool
	 */
	public function isViewable()
	{
		if (!isset($this->privileges['isViewable'])) {
			$this->privileges['isViewable'] = \App\Privilege::isPermitted($this->getModuleName(), 'DetailView', $this->getId());
		}
		return $this->privileges['isViewable'];
	}

	/**
	 * Function check if record is createable.
	 *
	 * @return bool
	 */
	public function isCreateable()
	{
		if (!isset($this->privileges['isCreateable'])) {
			$this->privileges['isCreateable'] = $this->getModule()->isPermitted('CreateView');
		}
		return $this->privileges['isCreateable'];
	}

	/**
	 * Function check if record is editable.
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		if (!isset($this->privileges['isEditable'])) {
			$this->privileges['isEditable'] = $this->isPermitted('EditView') && !$this->isLockByFields() && false === Users_Privileges_Model::checkLockEdit($this->getModuleName(), $this) && empty($this->getUnlockFields());
		}
		return $this->privileges['isEditable'];
	}

	/**
	 * Function to check permission.
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	public function isPermitted(string $action)
	{
		if (!isset($this->privileges[$action])) {
			$this->privileges[$action] = \App\Privilege::isPermitted($this->getModuleName(), $action, $this->getId());
		}
		return $this->privileges[$action];
	}

	/**
	 * The function decide about mandatory save record.
	 *
	 * @return bool
	 */
	public function isMandatorySave()
	{
		if ($this->getModule()->isInventory() && $this->getPreviousInventoryItems()) {
			return true;
		}
		if (!empty($this->dataForSave)) {
			return true;
		}
		return false;
	}

	/**
	 * The function decide about locking the record by field.
	 *
	 * @return bool
	 */

	/**
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function isLockByFields()
	{
		if (!isset($this->privileges['isLockByFields'])) {
			$isLock = false;
			$moduleName = $this->getModuleName();
			$recordId = $this->getId();
			$focus = $this->getEntity();
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
					if (!$this->has($fieldName) && isset($focus->column_fields[$fieldName])) {
						parent::set($fieldName, $focus->column_fields[$fieldName]);
					}
					foreach ($values as $value) {
						if ($this->get($fieldName) === $value) {
							$isLock = true;
							break 2;
						}
					}
				}
			}
			$this->privileges['isLockByFields'] = $isLock;
		}
		return $this->privileges['isLockByFields'];
	}

	/**
	 * Function check if record is to unlock.
	 *
	 * @return bool
	 */
	public function isUnlockByFields()
	{
		if (!isset($this->privileges['Unlock'])) {
			$this->privileges['Unlock'] = !$this->isNew() && $this->isPermitted('EditView') && $this->isPermitted('OpenRecord') &&
				false === Users_Privileges_Model::checkLockEdit($this->getModuleName(), $this) && !$this->isLockByFields() && !empty($this->getUnlockFields());
		}
		return $this->privileges['Unlock'];
	}

	/**
	 * Gets unlock fields.
	 *
	 * @return array
	 */
	public function getUnlockFields()
	{
		$cacheName = 'UnlockFields';
		if (\App\Cache::staticHas($cacheName, $this->getId())) {
			return \App\Cache::staticGet($cacheName, $this->getId());
		}
		$lockFields = \App\Fields\Picklist::getCloseStates($this->getModule()->getId());
		foreach ($lockFields as $fieldName => $values) {
			if (!in_array($this->getValueByField($fieldName), $values) || !$this->getField($fieldName)->isAjaxEditable()) {
				unset($lockFields[$fieldName]);
			}
		}
		\App\Cache::staticSave($cacheName, $this->getId(), $lockFields);
		return $lockFields;
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
			$this->privileges['MoveToTrash'] = 'Trash' !== \App\Record::getState($this->getId()) && \App\Privilege::isPermitted($this->getModuleName(), 'MoveToTrash', $this->getId());
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
			$this->privileges['Archive'] = 'Archived' !== \App\Record::getState($this->getId()) && \App\Privilege::isPermitted($this->getModuleName(), 'ArchiveRecord', $this->getId());
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
			$this->privileges['Activate'] = 'Active' !== \App\Record::getState($this->getId()) && \App\Privilege::isPermitted($this->getModuleName(), 'ActiveRecord', $this->getId());
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
		return 'index.php?module=' . $this->getModuleName() . '&view=' . $this->getModule()->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Function to get Display value for RelatedList.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getRelatedListDisplayValue($fieldName)
	{
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
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
	 * @param mixed $parentRecordModel
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
						if ('shownerid' === $fieldName) {
							$parentRecordModel->set($fieldName, \App\Fields\SharedOwner::getById($parentRecordModel->getId()));
						}
						$this->set($fieldName, $parentRecordModel->get($fieldName));
					}
				}
			}
			if ($parentRecordModel->getModule()->isInventory() && $this->getModule()->isInventory()) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($parentRecordModel->getModuleName());
				$inventoryFields = $inventoryModel->getFields();
				$sourceInv = $parentRecordModel->getInventoryData();
			}
			foreach ($mfInstance->getMapping() as $mapp) {
				if ('SELF' == $mapp['type'] && is_object($mapp['target'])) {
					$referenceList = $mapp['target']->getReferenceList();
					if (in_array($parentRecordModel->getModuleName(), $referenceList)) {
						$this->set($mapp['target']->getName(), $parentRecordModel->get($mapp['source']->getName()));
					}
				} elseif ('INVENTORY' == $mapp['type'] && $sourceInv) {
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
					if ('shownerid' === $mapp['source']->getName() && empty($parentMapName)) {
						$parentRecordModel->set($mapp['source']->getName(), \App\Fields\SharedOwner::getById($parentRecordModel->getId()));
					}
					$value = $parentRecordModel->get($mapp['source']->getName());
					if (!$value) {
						$value = $mapp['default'];
					}
					$this->set($mapp['target']->getName(), $value);
				}
			}
			if ($newInvData) {
				$this->initInventoryData($newInvData, false);
			}
		}
	}

	public function getListFieldsToGenerate($parentModuleName, $moduleName)
	{
		$moduleInstance = CRMEntity::getInstance($parentModuleName);

		return $moduleInstance->fieldsToGenerate[$moduleName] ? $moduleInstance->fieldsToGenerate[$moduleName] : [];
	}

	/**
	 * Save the inventory data.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \yii\db\Exception
	 */
	public function saveInventoryData()
	{
		\App\Log::trace('Start ' . __METHOD__);
		$inventoryData = $this->getInventoryData();
		$prevValue = $this->getPreviousInventoryItems();
		if (($this->isNew() && $inventoryData) || (!$this->isNew() && $prevValue)) {
			$db = App\Db::getInstance();
			$dbCommand = $db->createCommand();
			$inventory = Vtiger_Inventory_Model::getInstance($this->getModuleName());
			$tableName = $inventory->getDataTableName();
			if ($prevValue && ($ids = array_column($prevValue, 'id'))) {
				$dbCommand->delete($tableName, ['id' => $ids])->execute();
			}
			foreach ($inventoryData as $key => $item) {
				if (isset($item['id'])) {
					$dbCommand->update($tableName, $item, ['id' => $item['id']])->execute();
				} else {
					$item['crmid'] = $this->getId();
					$dbCommand->insert($tableName, $item)->execute();
					$item['id'] = $db->getLastInsertID("{$tableName}_id_seq");
					$this->inventoryData[$key] = $item;
				}
			}
		}
		\App\Log::trace('End ' . __METHOD__);
	}

	/**
	 * Function to gets inventory default data fields.
	 *
	 * @return null|int|string
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
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array Inventory data
	 */
	public function getInventoryData()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		if (!isset($this->inventoryData) && $this->getId()) {
			$this->inventoryData = \Vtiger_Inventory_Model::getInventoryDataById($this->getId(), $this->getModuleName());
		} elseif (!isset($this->inventoryData) && $this->get('record_id')) {
			$this->inventoryData = \Vtiger_Inventory_Model::getInventoryDataById($this->get('record_id'), $this->getModuleName());
		} else {
			$this->inventoryData = $this->inventoryData ?? [];
		}
		\App\Log::trace('Exiting ' . __METHOD__);
		return $this->inventoryData;
	}

	/**
	 * Gets inventory item.
	 *
	 * @param int|string $key
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function getInventoryItem($key)
	{
		return $this->getInventoryData()[$key] ?? null;
	}

	/**
	 * Initialization of inventory data.
	 *
	 * @param array     $items
	 * @param null|bool $userFormat
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\Security
	 */
	public function initInventoryData(array $items, bool $userFormat = true)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$inventoryModel = Vtiger_Inventory_Model::getInstance($this->getModuleName());
		$fields = $inventoryModel->getFields();
		$this->getInventoryData();
		$requiredField = $inventoryModel->getField('name');
		if (!$this->isNew()) {
			$newKeys = array_column($items, 'id', 'id');
			foreach ($this->inventoryData as $key => $item) {
				if (!isset($newKeys[$key])) {
					$this->changesInventory[$key] = $item;
					unset($this->inventoryData[$key]);
				}
			}
		}
		$i = 0;
		foreach ($items as $key => $item) {
			if (empty($item['id']) && $requiredField && !$requiredField->isRequired() && !isset($item[$requiredField->getColumnName()])) {
				continue;
			}
			$item['id'] = empty($item['id']) ? '#' . $i++ : (int) $item['id'];
			foreach ($fields as $field) {
				$field->setValueToRecord($this, $item, $userFormat);
			}
		}
		if ($this->isNew() || $this->getPreviousInventoryItems()) {
			foreach ($inventoryModel->getSummaryFields() as $fieldName) {
				if ($this->has('sum_' . $fieldName)) {
					$value = $fields[$fieldName]->getSummaryValuesFromData($this->getInventoryData());
					$this->set('sum_' . $fieldName, $value);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * Initialization of inventory data from request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\Security
	 */
	public function initInventoryDataFromRequest(App\Request $request)
	{
		$inventory = Vtiger_Inventory_Model::getInstance($this->getModuleName());
		$this->initInventoryData($request->getMultiDimensionArray('inventory', ['id' => \App\Purifier::INTEGER] + $inventory->getPurifyTemplate()));
	}

	/**
	 * Sets inventory item part.
	 *
	 * @param mixed  $itemId
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function setInventoryItemPart($itemId, string $name, $value)
	{
		if (!$this->isNew()) {
			if (is_numeric($itemId) && ($prevValue = ($this->getInventoryData()[$itemId][$name] ?? false)) != $value) {
				$this->changesInventory[$itemId][$name] = $prevValue;
			} elseif (!is_numeric($itemId)) {
				$this->changesInventory[$itemId] = [];
			}
		}
		$this->inventoryData[$itemId][$name] = $value;
	}

	/**
	 * Clear privileges.
	 */
	public function clearPrivilegesCache()
	{
		$this->privileges = [];
		Users_Privileges_Model::clearLockEditCache($this->getModuleName() . $this->getId());
		\vtlib\Functions::clearCacheMetaDataRecord($this->getId());
		\App\Cache::staticDelete('UnlockFields', $this->getId());
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
		if ($this->isViewable()) {
			if ($this->getModule()->isSummaryViewSupported()) {
				$recordLinks[] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_SHOW_QUICK_DETAILS',
					'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $this->getId(),
					'linkicon' => 'far fa-caret-square-right',
					'linkclass' => 'btn-sm btn-default',
					'modalView' => true,
				];
			}
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
				'linkclass' => 'btn-sm ' . ($watching ? 'btn-dark' : 'btn-outline-dark'),
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => (int) !$watching, 'on' => 'btn-dark', 'off' => 'btn-outline-dark', 'icon-on' => 'fa-eye', 'icon-off' => 'fa-eye-slash'],
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
				'linkclass' => 'btn-sm btn-dark recordEvent',
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
					'linkhref' => 'ListPreview' === $defaultViewName ? false : true,
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
				'linkclass' => 'btn-sm ' . ($watching ? 'btn-dark' : 'btn-outline-dark'),
				'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => (int) !$watching, 'on' => 'btn-dark', 'off' => 'btn-outline-dark', 'icon-on' => 'fa-eye', 'icon-off' => 'fa-eye-slash'],
			]);
		}
		if ($relationModel->privilegeToDelete()) {
			if ($this->privilegeToMoveToTrash()) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_REMOVE_RELATION',
					'linkicon' => 'fas fa-unlink',
					'linkclass' => 'btn-sm btn-secondary relationDelete entityStateBtn',
					'linkdata' => [
						'content' => \App\Language::translate('LBL_REMOVE_RELATION'),
						'confirm' => \App\Language::translate('LBL_REMOVE_RELATION_CONFIRMATION'),
						'id' => $this->getId()
					]
				]);
			}
			if ($this->privilegeToMoveToTrash()) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_MOVE_TO_TRASH',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Trash&record=' . $this->getId(),
					'linkicon' => 'fas fa-trash-alt',
					'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
					'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC')],
					'linkclass' => 'btn-sm btn-outline-dark relationDelete entityStateBtn'
				]);
			}
			if ($this->privilegeToDelete()) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
					'linkicon' => 'fas fa-eraser',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=Delete&record=' . $this->getId(),
					'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
					'linkclass' => 'btn-sm btn-dark relationDelete entityStateBtn'
				]);
			}
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
		return $this->isPermitted('AssignToYourself') && \App\PrivilegeUtil::MEMBER_TYPE_GROUPS === \App\Fields\Owner::getType($this->getValueByField('assigned_user_id')) &&
			array_key_exists(\App\User::getCurrentUserId(), \App\Fields\Owner::getInstance($this->getModuleName())->getAccessibleUsers('', 'owner'));
	}

	/**
	 * Function checks if user can use records auto assign mechanism.
	 *
	 * @return bool
	 */
	public function autoAssignRecord()
	{
		if ($this->isPermitted('AutoAssignRecord') && \App\PrivilegeUtil::MEMBER_TYPE_GROUPS === \App\Fields\Owner::getType($this->getValueByField('assigned_user_id'))) {
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
	public function getValueByField(string $fieldName)
	{
		if (!$this->has($fieldName)) {
			$focus = $this->getEntity();
			if (isset($focus->column_fields[$fieldName]) && '' !== $focus->column_fields[$fieldName]) {
				$value = $focus->column_fields[$fieldName];
			} else {
				$fieldModel = $this->getModule()->getFieldByName($fieldName);
				$idName = $focus->tab_name_index[$fieldModel->getTableName()];
				$value = \vtlib\Functions::getSingleFieldValue($fieldModel->getTableName(), $fieldModel->getColumnName(), $idName, $this->getId());
			}
			parent::set($fieldName, $value);
		}
		return $this->get($fieldName);
	}

	/**
	 * Clear changes.
	 */
	public function clearChanges()
	{
		$this->changes = null;
	}

	/**
	 * Change record state.
	 *
	 * @param type $state
	 */
	public function changeState($state)
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
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
				default:
					break;
			}
			$dbCommand = $db->createCommand();
			$dbCommand->update('vtiger_crmentity', [
				'deleted' => $stateId, 'modifiedtime' => date('Y-m-d H:i:s'),
				'modifiedby' => \App\User::getCurrentUserId()
			], ['crmid' => $this->getId()])->execute();
			if ('Active' !== $state) {
				$dbCommand->delete('u_#__crmentity_search_label', ['crmid' => $this->getId()])->execute();
			}
			$eventHandler = new App\EventHandler();
			$eventHandler->setRecordModel($this);
			$eventHandler->setModuleName($this->getModuleName());
			if ($this->getHandlerExceptions()) {
				$eventHandler->setExceptions($this->getHandlerExceptions());
			}
			$eventHandler->trigger('EntityChangeState');

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
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
		if (!$this->isEmpty('imagename') && '[]' !== $this->get('imagename') && '""' !== $this->get('imagename')) {
			$image = \App\Json::decode($this->get('imagename'));
			if (empty($image) || !($image = \current($image)) || empty($image['path'])) {
				\App\Log::warning("Problem with data compatibility: No parameter path [{$this->get('imagename')}]");
				return [];
			}
			$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
			$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field=imagename&record={$this->getId()}&key={$image['key']}";
		} else {
			foreach ($this->getModule()->getFieldsByType('multiImage') as $fieldModel) {
				if (!$this->isEmpty($fieldModel->getName()) && '[]' !== $this->get($fieldModel->getName()) && '""' !== $this->get($fieldModel->getName())) {
					$image = \App\Json::decode($this->get($fieldModel->getName()));
					if (empty($image) || !($image = \current($image)) || empty($image['path'])) {
						\App\Log::warning("Problem with data compatibility: No parameter path [{$this->get('imagename')}]");
						return [];
					}
					$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
					$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field={$fieldModel->getName()}&record={$this->getId()}&key={$image['key']}";
					break;
				}
			}
		}
		return $image;
	}
}
