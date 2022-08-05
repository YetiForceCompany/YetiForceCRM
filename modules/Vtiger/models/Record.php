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

/**
 * Vtiger Entity Record Model Class.
 */
class Vtiger_Record_Model extends \App\Base
{
	/**
	 * @var Vtiger_Module_Model Module model
	 */
	protected $module;
	/**
	 * @var array Inventory data
	 */
	protected $inventoryData;
	/**
	 * @var array Record changes
	 */
	protected $changes = [];
	/**
	 * @var array Record inventory changes
	 */
	protected $changesInventory = [];
	/**
	 * @var array Data for save
	 */
	protected $dataForSave = [];
	/**
	 * @var array Event handler exceptions
	 */
	protected $handlerExceptions = [];
	protected $handler;
	protected $privileges = [];
	/**
	 * @var string Record label
	 */
	public $label;
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
		if (!$this->isNew && !\in_array($key, ['mode', 'id', 'newRecord', 'modifiedtime', 'modifiedby', 'createdtime']) && (\array_key_exists($key, $this->value) && $this->value[$key] != $value)) {
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
	 * Set custom data for save.
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setDataForSave(array $data): self
	{
		$db = \App\Db::getInstance();
		foreach ($data as $tableName => $tableData) {
			$tableName = $db->quoteSql($tableName);
			$this->dataForSave[$tableName] = isset($this->dataForSave[$tableName]) ? array_merge($this->dataForSave[$tableName], $tableData) : $tableData;
		}
		return $this;
	}

	/**
	 * Gets custom data for save.
	 *
	 * @param array
	 */
	public function getDataForSave()
	{
		return $this->dataForSave;
	}

	/**
	 * Function to get the Name of the record.
	 *
	 * @return string - Entity Name of the record
	 */
	public function getName(): string
	{
		if (!isset($this->label)) {
			$this->label = $this->getDisplayName();
		}
		return $this->label;
	}

	/**
	 * Get pervious value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getPreviousValue(string $fieldName = '')
	{
		return $fieldName ? ($this->changes[$fieldName] ?? false) : $this->changes;
	}

	/**
	 * Revert previous value.
	 *
	 * @param string $fieldName
	 *
	 * @return void
	 */
	public function revertPreviousValue(string $fieldName): void
	{
		if (isset($this->changes[$fieldName])) {
			$this->value[$fieldName] = $this->changes[$fieldName];
		}
	}

	/**
	 * Gets previous values by inventory.
	 *
	 * @param int|string|null $key
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
	public function getModule(): Vtiger_Module_Model
	{
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs.
	 *
	 * @param string $moduleName
	 *
	 * @return Vtiger_Record_Model Record Model instance
	 */
	public function setModule(string $moduleName): self
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance.
	 *
	 * @param Vtiger_Module_Model $module
	 *
	 * @return Vtiger_Record_Model Record Model instance
	 */
	public function setModuleFromInstance(Vtiger_Module_Model $module): self
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
	 * Function to get raw data value by field.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getRawValue(string $fieldName)
	{
		$value = $this->get($fieldName);
		if ($fieldName && $fieldModel = $this->getField($fieldName)) {
			$value = $fieldModel->getUITypeModel()->getRawValue($value);
		}
		return $value;
	}

	/**
	 * Get record number.
	 *
	 * @return string
	 */
	public function getRecordNumber(): string
	{
		return $this->get($this->getModule()->getSequenceNumberFieldName()) ?? '';
	}

	/**
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$menuUrl = '';
		if (!empty($_REQUEST['parent']) && 'Settings' !== $_REQUEST['parent']) {
			$menuUrl .= '&parent=' . \App\Request::_getInteger('parent');
		}
		if (isset($_REQUEST['mid'])) {
			$menuUrl .= '&mid=' . \App\Request::_getInteger('mid');
		}
		return "index.php?module={$this->getModuleName()}&view={$this->getModule()->getDetailViewName()}&record={$this->getId()}{$menuUrl}";
	}

	/**
	 * Function to get the complete Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getFullDetailViewUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record.
	 *
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$menuUrl = '';
		if (isset($_REQUEST['parent'])) {
			$menuUrl .= '&parent=' . \App\Request::_getInteger('parent');
		}
		if (isset($_REQUEST['mid'])) {
			$menuUrl .= '&mid=' . \App\Request::_getInteger('mid');
		}
		return "index.php?module={$this->getModuleName()}&view={$this->getModule()->getEditViewName()}{$menuUrl}" . ($this->getId() ? '&record=' . $this->getId() : '');
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
	public function getModuleName(): string
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
	 * @return bool|string
	 */
	public function getDisplayValue($fieldName, $record = false, $rawText = false, $length = false)
	{
		if (empty($record)) {
			$record = $this->getId();
		}
		$result = false;
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		if ($fieldModel) {
			$result = $fieldModel->getDisplayValue($this->get($fieldName), $record, $this, $rawText, $length);
		}
		return $result;
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
		$fieldModel = $this->getModule()->getFieldByName($fieldName);
		return $fieldModel->getUITypeModel()->getRelatedListViewDisplayValue($this->get($fieldName), $this->getId(), $this);
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
		return $field->getUITypeModel()->getListViewDisplayValue($this->get($field->getName()), $this->getId(), $this, $rawText);
	}

	/**
	 * Function to get the display value in Tiles.
	 *
	 * @param string|Vtiger_Field_Model $field
	 * @param bool                      $rawText
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string
	 */
	public function getTilesDisplayValue($field, $rawText = false)
	{
		if ($field instanceof Vtiger_Field_Model) {
			if (!empty($field->get('source_field_name')) && isset($this->ext[$field->get('source_field_name')][$field->getModuleName()])) {
				return $this->ext[$field->get('source_field_name')][$field->getModuleName()]->getTilesDisplayValue($field, $rawText);
			}
		} else {
			$field = $this->getModule()->getFieldByName($field);
		}
		return $field->getUITypeModel()->getTilesDisplayValue($this->get($field->getName()), $this->getId(), $this, $rawText);
	}

	/**
	 * Function returns the Vtiger_Field_Model.
	 *
	 * @param string $fieldName - field name
	 *
	 * @return Vtiger_Field_Model|false
	 */
	public function getField($fieldName)
	{
		return $this->getModule()->getFieldByName($fieldName);
	}

	/**
	 * Function returns all the field values in user format.
	 *
	 * @return array
	 */
	public function getDisplayableValues(): array
	{
		$displayableValues = [];
		$data = $this->getData();
		foreach ($data as $fieldName => $value) {
			$fieldValue = $this->getDisplayValue($fieldName);
			$displayableValues[$fieldName] = ($fieldValue) ?: $value;
		}
		return $displayableValues;
	}

	/**
	 * Gets Event Handler.
	 *
	 * @return \App\EventHandler
	 */
	public function getEventHandler(): App\EventHandler
	{
		if (!$this->handler) {
			$this->handler = (new \App\EventHandler())->setRecordModel($this)->setModuleName($this->getModuleName());
		}
		return $this->handler;
	}

	/**
	 * Function to save the current Record Model.
	 */
	public function save()
	{
		$eventHandler = $this->getEventHandler();
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
			Users_Privileges_Model::setSharedOwner($this->get('shownerid'), $this->getId());
			\App\Record::updateLabelOnSave($this);
			$this->addRelations();
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

		if ($this->isNew()) {
			$entityModel = $this->getEntity();
			$forSave[$entityModel->table_name] = [];
			if (!empty($entityModel->customFieldTable)) {
				$forSave[$entityModel->customFieldTable[0]] = [];
			}
			foreach ($entityModel->tab_name as $tableName) {
				if (empty($forSave[$tableName])) {
					$forSave[$tableName] = [];
				}
			}
		} else {
			$saveFields = array_intersect($saveFields, array_merge(array_keys($this->changes), [$moduleModel->getSequenceNumberFieldName()]));
		}
		foreach ($this->dataForSave as $tableName => $values) {
			$forSave[$tableName] = array_merge($forSave[$tableName] ?? [], $values);
		}
		foreach ($saveFields as $fieldName) {
			if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
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
					$this->set($fieldName, $value);
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
			$this->set('created_user_id', $row['smcreatorid']);
		}
		$row['modifiedtime'] = $this->getPreviousValue('modifiedtime') ? $this->get('modifiedtime') : $time;
		$row['modifiedby'] = $this->getPreviousValue('modifiedby') ? $this->get('modifiedby') : \App\User::getCurrentUserRealId();
		$this->set('modifiedtime', $row['modifiedtime']);
		$this->set('modifiedby', $row['modifiedby']);
		return ['vtiger_crmentity' => $row];
	}

	/**
	 * Add relations on save.
	 * The main purpose of the function to share relational data in workflow.
	 *
	 * @return void
	 */
	public function addRelations(): void
	{
		$recordId = $this->getId();
		if (isset($this->ext['relations']) && \is_array($this->ext['relations'])) {
			foreach ($this->ext['relations'] as $value) {
				if ($reverse = empty($value['reverse'])) {
					$relationModel = Vtiger_Relation_Model::getInstance($this->getModule(), Vtiger_Module_Model::getInstance($value['relatedModule']), ($value['relationId'] ?? false));
				} else {
					$relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($value['relatedModule']), $this->getModule(), ($value['relationId'] ?? false));
				}
				if ($relationModel) {
					foreach ($value['relatedRecords'] as $record) {
						if ($reverse) {
							$relationModel->addRelation($this->getId(), $record, $value['params'] ?? false);
						} else {
							$relationModel->addRelation($record, $this->getId(), $value['params'] ?? false);
						}
					}
				} else {
					\App\Log::warning("Relation model does not exist: {$this->getModuleName()} | relatedModule: {$value['relatedModule']} (relationId: {$value['relationId']})| reverse: $reverse");
				}
			}
		}
		if ('link' === \App\Request::_get('createmode') && \App\Request::_has('return_module') && \App\Request::_has('return_id')) {
			Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance(\App\Request::_get('return_module')), $this->getModule())
				->addRelation(\App\Request::_getInteger('return_id'), $recordId);
		}
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
			$db->createCommand()->delete('vtiger_crmentity', ['crmid' => $this->getId()])->execute();
			\App\Db::getInstance('admin')->createCommand()->delete('s_#__privileges_updater', ['crmid' => $this->getId()])->execute();
			\App\Fields\File::deleteForRecord($this);
			$eventHandler->trigger('EntityAfterDelete');
			if ($this->getModule()->isCommentEnabled()) {
				(new \App\BatchMethod(['method' => 'ModComments_Module_Model::deleteForRecord', 'params' => [$this->getId()]]))->save();
			}
			$this->clearPrivilegesCache($this->getId());
			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
	}

	/**
	 * Static Function to get the instance of a clean Vtiger Record Model for the given module name.
	 *
	 * @uses \App\Base::__construct()
	 *
	 * @param string $moduleName
	 *
	 * @return $this or Module Specific Record Model instance
	 */
	public static function getCleanInstance(string $moduleName)
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
	 * @uses self::__construct()
	 *
	 * @param int    $recordId
	 * @param string $module
	 *
	 * @return $this Module Specific Record Model instance
	 */
	public static function getInstanceById($recordId, $module = null)
	{
		if (\is_object($module) && is_a($module, 'Vtiger_Module_Model')) {
			$moduleName = $module->get('name');
		} elseif (\is_string($module)) {
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
	public function isEditable(): bool
	{
		if (!isset($this->privileges['isEditable'])) {
			return $this->privileges['isEditable'] = $this->isPermitted('EditView') && !$this->isBlocked();
		}
		return $this->privileges['isEditable'];
	}

	/**
	 * Function check if record is blocked.
	 *
	 * @return bool
	 */
	public function isBlocked(): bool
	{
		if (!isset($this->privileges['isBlocked'])) {
			$this->privileges['isBlocked'] = $this->isLockByFields()
			|| true === Users_Privileges_Model::checkLockEdit($this->getModuleName(), $this)
			|| !empty($this->getUnlockFields()) || $this->isReadOnly();
		}
		return $this->privileges['isBlocked'];
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
			return $this->privileges[$action] = \App\Privilege::isPermitted($this->getModuleName(), $action, $this->getId());
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
		return !empty($this->dataForSave) || ($this->getModule()->isInventory() && $this->getPreviousInventoryItems());
	}

	/**
	 * Function to check read only record.
	 *
	 * @return bool
	 */
	public function isReadOnly(): bool
	{
		if (!isset($this->privileges['isReadOnly'])) {
			return $this->privileges['isReadOnly'] = !$this->isNew() && \App\Components\InterestsConflict::CHECK_STATUS_CONFLICT === \App\Components\InterestsConflict::check($this->getId(), $this->getModuleName());
		}
		return $this->privileges['isReadOnly'];
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
			$this->privileges['Unlock'] = !$this->isNew() && $this->isPermitted('EditView') && $this->isPermitted('OpenRecord')
				&& false === Users_Privileges_Model::checkLockEdit($this->getModuleName(), $this) && !$this->isLockByFields() && !empty($this->getUnlockFields(true));
		}
		return $this->privileges['Unlock'];
	}

	/**
	 * Gets unlock fields.
	 *
	 * @param bool $isAjaxEditable
	 *
	 * @return array
	 */
	public function getUnlockFields($isAjaxEditable = false)
	{
		$id = $this->getId();
		$cacheName = 'UnlockFields' . $isAjaxEditable;
		if ($id && \App\Cache::staticHas($cacheName, $id)) {
			return \App\Cache::staticGet($cacheName, $id);
		}
		$lockFields = \App\RecordStatus::getLockStatus($this->getModule()->getName());
		foreach ($lockFields as $fieldName => $values) {
			if (!\in_array($this->getValueByField($fieldName), $values) || ($isAjaxEditable && !$this->getField($fieldName)->isAjaxEditable())) {
				unset($lockFields[$fieldName]);
			}
		}
		if ($id) {
			\App\Cache::staticSave($cacheName, $this->getId(), $lockFields);
		}
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
			$this->privileges['Deleted'] = \App\Privilege::isPermitted($this->getModuleName(), 'Delete', $this->getId()) && false === Users_Privileges_Model::checkLockEdit($this->getModuleName(), $this) && !$this->isLockByFields();
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

	public function getSummaryInfo()
	{
		$moduleName = $this->getModuleName();
		$path = "modules/$moduleName/summary_blocks";
		if (!is_dir($path)) {
			return [];
		}
		$tempSummaryBlocks = [];
		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$tmp = explode('.', $fileinfo->getFilename());
				$fullPath = $path . DIRECTORY_SEPARATOR . $tmp[0] . '.php';
				if (file_exists($fullPath)) {
					require_once $fullPath;
					$block = new $tmp[0]();
					if (isset($block->reference) && !\App\Module::isModuleActive($block->reference)) {
						continue;
					}
					$tempSummaryBlocks[$block->sequence] = [
						'name' => $block->name,
						'data' => $block->process($this),
						'reference' => $block->reference,
						'type' => $block->type ?? false,
						'icon' => $block->icon ?? false,
					];
				}
			}
		}
		ksort($tempSummaryBlocks);
		$blockCount = 0;
		$summaryBlocks = [];
		foreach ($tempSummaryBlocks as $key => $block) {
			$summaryBlocks[(int) ($blockCount / $this->summaryRowCount)][$key] = $tempSummaryBlocks[$key];
			++$blockCount;
		}
		return $summaryBlocks;
	}

	/**
	 * Function to set record module field values.
	 *
	 * @param self $parentRecordModel
	 */
	public function setRecordFieldValues($parentRecordModel)
	{
		$mfInstance = Vtiger_MappedFields_Model::getInstanceByModules($parentRecordModel->getModule()->getId(), $this->getModule()->getId());
		if ($mfInstance) {
			$defaultInvRow = [];
			$params = $mfInstance->get('params');
			if (!empty($params['autofill'])) {
				$fieldsList = array_keys($this->getModule()->getFields());
				$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());
				$commonFields = array_intersect($fieldsList, $parentFieldsList);
				foreach ($commonFields as $fieldName) {
					if (\App\Field::getFieldPermission($parentRecordModel->getModuleName(), $fieldName)) {
						$value = $parentRecordModel->get($fieldName);
						$this->getField($fieldName)->getUITypeModel()->validate($value);
						$this->set($fieldName, $value);
					}
				}
			}
			if ($parentRecordModel->getModule()->isInventory() && $this->getModule()->isInventory()) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($this->getModuleName());
				$sourceInv = $parentRecordModel->getInventoryData();
				foreach ($inventoryModel->getFields() as $fieldModel) {
					$defaultInvRow[$fieldModel->getColumnName()] = $fieldModel->getDefaultValue();
				}
			}

			foreach ($mfInstance->getMapping() as $mapp) {
				$fieldTarget = $mapp['target'];
				$fieldSource = $mapp['source'];
				if ((!\is_object($fieldTarget) || !\is_object($fieldSource))) {
					continue;
				}
				$type = $mapp['type'];
				if ('SELF' == $type && \in_array($parentRecordModel->getModuleName(), $fieldTarget->getReferenceList())) {
					$this->set($fieldTarget->getName(), $parentRecordModel->get($fieldSource->getName()));
				} elseif ('INVENTORY' == $type && $sourceInv) {
					foreach ($sourceInv as $key => $base) {
						if (!isset($base[$fieldSource->getName()]) || !($fieldInventory = $inventoryModel->getField($fieldTarget->getName()))) {
							continue;
						}
						$fieldInventory->validate($base[$fieldSource->getName()], $fieldInventory->getColumnName(), false);
						if (null === $this->getInventoryItem($key)) {
							$this->inventoryData[$key] = $defaultInvRow;
						}
						$this->setInventoryItemPart($key, $fieldInventory->getColumnName(), $base[$fieldSource->getName()]);
						foreach (array_keys($fieldInventory->getCustomColumn()) as $customColumn) {
							if (\array_key_exists($customColumn, $base)) {
								$fieldInventory->validate($base[$customColumn], $customColumn, false);
								$this->setInventoryItemPart($key, $customColumn, $base[$customColumn]);
							}
						}
					}
				} elseif (!\in_array($type, ['INVENTORY', 'SELF']) && \App\Field::getFieldPermission($parentRecordModel->getModuleName(), $fieldSource->getName())) {
					$value = $parentRecordModel->get($fieldSource->getName());
					if (!$value) {
						$value = $mapp['default'];
					}
					$this->getField($fieldTarget->getName())->getUITypeModel()->validate($value);
					$this->set($fieldTarget->getName(), $value);
				}
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
				foreach ($inventory->getFields() as $field) {
					$field->validate($item[$field->getColumnName()] ?? null, $field->getColumnName(), false);
				}
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
	 * @return int|string|null
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
	 * @param array $items
	 * @param bool  $userFormat
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
	 *
	 * @return void
	 */
	public function initInventoryDataFromRequest(App\Request $request): void
	{
		$inventory = Vtiger_Inventory_Model::getInstance($this->getModuleName());
		$rawInventory = $request->getRaw('inventory');
		if (isset($rawInventory['_NUM_'])) {
			unset($rawInventory['_NUM_']);
		}
		if ($inventory->getField('name')) {
			foreach ($rawInventory as $key => $inventoryRow) {
				if (empty($inventoryRow['name'])) {
					unset($rawInventory[$key]);
				}
			}
		}
		$request->set('inventory', $rawInventory, true);
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
	public function setHandlerExceptions($exceptions): void
	{
		$this->handlerExceptions = $exceptions;
	}

	/**
	 * get handler exceptions.
	 *
	 * @return array
	 */
	public function getHandlerExceptions(): array
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
		if (!$this->isViewable()) {
			return [];
		}
		$links = $recordLinks = [];
		if ($this->getModule()->isSummaryViewSupported() && array_filter($this->getModule()->getWidgets())) {
			$recordLinks['LBL_SHOW_QUICK_DETAILS'] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SHOW_QUICK_DETAILS',
				'linkurl' => 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $this->getId(),
				'linkicon' => 'far fa-caret-square-right',
				'linkclass' => 'btn-sm btn-default',
				'modalView' => true,
			];
		}
		$recordLinks['LBL_SHOW_COMPLETE_DETAILS'] = [
			'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
			'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
			'linkurl' => $this->getFullDetailViewUrl(),
			'linkicon' => 'fas fa-th-list',
			'linkclass' => 'btn-sm btn-default',
			'linkhref' => true,
		];
		if ($this->isEditable()) {
			$recordLinks['LBL_EDIT'] = [
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn-sm btn-default',
				'linkhref' => true,
			];
			if ($this->getModule()->isQuickCreateSupported()) {
				$recordLinks['LBL_QUICK_EDIT'] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_QUICK_EDIT',
					'linkicon' => 'yfi yfi-quick-creation',
					'linkclass' => 'btn-sm btn-default js-quick-edit-modal',
					'linkdata' => [
						'module' => $this->getModuleName(),
						'record' => $this->getId(),
					],
				];
			}
			if ($link = \App\Fields\ServerAccess::getLinks($this, 'List')) {
				$recordLinks['BTN_SERVER_ACCESS'] = $link;
			}
		}
		if (!$this->isReadOnly()) {
			if ($this->isViewable() && $this->getModule()->isPermitted('WatchingRecords')) {
				$watching = (int) ($this->isWatchingRecord());
				$recordLinks['BTN_WATCHING_RECORD'] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'BTN_WATCHING_RECORD',
					'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
					'linkicon' => 'fas ' . ($watching ? 'fa-eye-slash' : 'fa-eye'),
					'linkclass' => 'btn-sm ' . ($watching ? 'btn-dark' : 'btn-outline-dark'),
					'linkdata' => ['module' => $this->getModuleName(), 'record' => $this->getId(), 'value' => (int) !$watching, 'on' => 'btn-dark', 'off' => 'btn-outline-dark', 'icon-on' => 'fa-eye', 'icon-off' => 'fa-eye-slash'],
				];
			}
			$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
			if ($this->privilegeToActivate()) {
				$recordLinks['LBL_ACTIVATE_RECORD'] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_ACTIVATE_RECORD',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Active&record=' . $this->getId(),
					'linkicon' => 'fas fa-undo-alt',
					'style' => empty($stateColors['Active']) ? '' : "background: {$stateColors['Active']};",
					'linkdata' => ['confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC'), 'source-view' => 'List'],
					'linkclass' => 'btn-sm btn-default entityStateBtn js-action-confirm',
				];
			}
			if ($this->privilegeToArchive()) {
				$recordLinks[] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_ARCHIVE_RECORD',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Archived&record=' . $this->getId(),
					'linkicon' => 'fas fa-archive',
					'style' => empty($stateColors['Archived']) ? '' : "background: {$stateColors['Archived']};",
					'linkdata' => ['confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC'), 'source-view' => 'List'],
					'linkclass' => 'btn-sm btn-default entityStateBtn js-action-confirm',
				];
			}
			if ($this->privilegeToMoveToTrash()) {
				$recordLinks['LBL_MOVE_TO_TRASH'] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_MOVE_TO_TRASH',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Trash&record=' . $this->getId(),
					'linkicon' => 'fas fa-trash-alt',
					'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
					'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC'), 'source-view' => 'List'],
					'linkclass' => 'btn-sm btn-default entityStateBtn js-action-confirm',
				];
			}
			if ($this->privilegeToDelete()) {
				$recordLinks['LBL_DELETE_RECORD_COMPLETELY'] = [
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
					'linkicon' => 'fas fa-eraser',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=Delete&record=' . $this->getId(),
					'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC'), 'source-view' => 'List'],
					'linkclass' => 'btn-sm btn-dark js-action-confirm',
				];
			}
		}
		foreach ($recordLinks as $key => $recordLink) {
			$links[$key] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		$allRecordListButtons = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['LIST_VIEW_BUTTONS']);
		if (!$this->isReadOnly() && isset($allRecordListButtons['LIST_VIEW_BUTTONS'])) {
			foreach ($allRecordListButtons['LIST_VIEW_BUTTONS'] as $recordListButton) {
				$url = $recordListButton->linkurl ?: $recordListButton->dataUrl;
				$queryParams = vtlib\Functions::getQueryParams($url);
				if (property_exists($recordListButton, 'permit') && isset($queryParams['module']) && !\App\Privilege::isPermitted($queryParams['module'], $recordListButton->get('permit'))) {
					continue;
				}
				if (isset($recordListButton->dataUrl)) {
					$recordListButton->dataUrl .= "&sourceModule={$this->getModuleName()}&sourceRecord={$this->getId()}";
				}
				$links[$recordListButton->get('linklabel')] = $recordListButton;
			}
		}
		return \App\Utils::changeSequence($links, App\Config::module($this->getModuleName(), 'recordListViewButtonSequence', []));
	}

	/**
	 * Get the related list view actions for the record.
	 *
	 * @param Vtiger_RelationListView_Model $viewModel
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$links = [];
		if (!$this->isViewable()) {
			return [];
		}
		if ($this->getModule()->isSummaryViewSupported()) {
			$defaultViewName = $viewModel->getParentRecordModel()->getModule()->getDefaultViewName();
			$links['LBL_SHOW_QUICK_DETAILS'] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_SHOW_QUICK_DETAILS',
				'linkhref' => 'ListPreview' !== $defaultViewName,
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

		if (!$this->isReadOnly()) {
			$relationModel = $viewModel->getRelationModel();
			if ($relationModel->isEditable() && $this->isEditable()) {
				$links['LBL_EDIT'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_EDIT',
					'linkhref' => true,
					'linkurl' => $this->getEditViewUrl(),
					'linkicon' => 'yfi yfi-full-editing-view',
					'linkclass' => 'btn-sm btn-default',
				]);
				if ($this->getModule()->isQuickCreateSupported()) {
					$links['LBL_QUICK_EDIT'] = Vtiger_Link_Model::getInstanceFromValues([
						'linklabel' => 'LBL_QUICK_EDIT',
						'linkicon' => 'yfi yfi-quick-creation',
						'linkclass' => 'btn-sm btn-default js-quick-edit-modal',
						'linkdata' => [
							'module' => $this->getModuleName(),
							'record' => $this->getId(),
						],
					]);
				}
				if ($link = \App\Fields\ServerAccess::getLinks($this, 'RelatedList')) {
					$links['BTN_SERVER_ACCESS'] = $link;
				}
			}
			if ($this->getModule()->isPermitted('WatchingRecords')) {
				$watching = (int) ($this->isWatchingRecord());
				$links['BTN_WATCHING_RECORD'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'BTN_WATCHING_RECORD',
					'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
					'linkicon' => 'fas ' . ($watching ? 'fa-eye-slash' : 'fa-eye'),
					'linkclass' => 'btn-sm ' . ($watching ? 'btn-dark' : 'btn-outline-dark'),
					'linkdata' => [
						'module' => $this->getModuleName(),
						'record' => $this->getId(),
						'value' => (int) !$watching,
						'on' => 'btn-dark',
						'off' => 'btn-outline-dark',
						'icon-on' => 'fa-eye',
						'icon-off' => 'fa-eye-slash', ],
				]);
			}
			if ($this->getModule()->isPermitted('ExportPdf')) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $this->getModuleName());
				$pdfModel = new $handlerClass();
				if ($pdfModel->checkActiveTemplates($this->getId(), $this->getModuleName(), 'Detail')) {
					$links['LBL_EXPORT_PDF'] = Vtiger_Link_Model::getInstanceFromValues([
						'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
						'linklabel' => 'LBL_EXPORT_PDF',
						'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&view=PDF&fromview=Detail&record=' . $this->getId(),
						'linkicon' => 'fas fa-file-pdf',
						'linkclass' => 'btn-sm btn-outline-danger showModal js-pdf',
					]);
				}
			}
			$privilegeToDelete = $relationModel->privilegeToDelete($this);
			if ($privilegeToDelete) {
				$links['LBL_REMOVE_RELATION'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_REMOVE_RELATION',
					'linkicon' => 'fas fa-unlink',
					'linkclass' => 'btn-sm btn-secondary relationDelete entityStateBtn',
					'linkdata' => [
						'content' => \App\Language::translate('LBL_REMOVE_RELATION'),
						'confirm' => \App\Language::translate('LBL_REMOVE_RELATION_CONFIRMATION'),
						'id' => $this->getId(),
					],
				]);
			}
			$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
			if ($this->privilegeToActivate()) {
				$links['LBL_ACTIVATE_RECORD'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_ACTIVATE_RECORD',
					'linkicon' => 'fas fa-undo-alt',
					'linkclass' => 'btn-sm btn-secondary relationDelete entityStateBtn',
					'style' => empty($stateColors['Active']) ? '' : "background: {$stateColors['Active']};",
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Active&record=' . $this->getId(),
					'linkdata' => [
						'content' => \App\Language::translate('LBL_ACTIVATE_RECORD'),
						'confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC'),
						'id' => $this->getId(),
					],
				]);
			}
			if ($this->privilegeToArchive()) {
				$links['LBL_ARCHIVE_RECORD'] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => 'LBL_ARCHIVE_RECORD',
					'linkicon' => 'fas fa-archive',
					'linkclass' => 'btn-sm btn-secondary relationDelete entityStateBtn',
					'style' => empty($stateColors['Archived']) ? '' : "background: {$stateColors['Archived']};",
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Archived&record=' . $this->getId(),
					'linkdata' => [
						'content' => \App\Language::translate('LBL_ARCHIVE_RECORD'),
						'confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC'),
						'id' => $this->getId(),
					],
				]);
			}
			if ($privilegeToDelete && $this->privilegeToMoveToTrash()) {
				$links['LBL_MOVE_TO_TRASH'] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_MOVE_TO_TRASH',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Trash&record=' . $this->getId(),
					'linkicon' => 'fas fa-trash-alt',
					'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
					'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC')],
					'linkclass' => 'btn-sm btn-outline-dark relationDelete entityStateBtn',
				]);
			}
			if ($privilegeToDelete && $this->privilegeToDelete()) {
				$links['LBL_DELETE_RECORD_COMPLETELY'] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
					'linkicon' => 'fas fa-eraser',
					'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=Delete&record=' . $this->getId(),
					'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
					'linkclass' => 'btn-sm btn-dark relationDelete entityStateBtn',
				]);
			}
			if (!empty($relationModel->getTypeRelationModel()->customFields) && ($relationModel->getTypeRelationModel()->getFields(true)) && ($parentRecord = $relationModel->get('parentRecord')) && $parentRecord->isEditable() && $this->isEditable()) {
				$changeRelationDataButton = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
					'linklabel' => 'LBL_CHANGE_RELATION_DATA',
					'dataUrl' => "index.php?module={$relationModel->getParentModuleModel()->getName()}&view=ChangeRelationData&record={$this->getId()}&fromRecord={$parentRecord->getId()}&relationId={$relationModel->getId()}",
					'linkicon' => 'mdi mdi-briefcase-edit-outline',
					'linkclass' => 'btn-sm btn-warning js-show-modal',
				]);
				if (App\Config::relation('separateChangeRelationButton')) {
					$links['BUTTONS']['LBL_CHANGE_RELATION_DATA'] = $changeRelationDataButton;
				} else {
					$links['LBL_CHANGE_RELATION_DATA'] = $changeRelationDataButton;
				}
			}
			$allRecordListButtons = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['RELATED_LIST_VIEW_BUTTONS']);
			if (isset($allRecordListButtons['RELATED_LIST_VIEW_BUTTONS'])) {
				foreach ($allRecordListButtons['RELATED_LIST_VIEW_BUTTONS'] as $recordListButton) {
					$url = $recordListButton->linkurl ?: $recordListButton->dataUrl;
					$queryParams = vtlib\Functions::getQueryParams($url);
					if (property_exists($recordListButton, 'permit') && isset($queryParams['module']) && !\App\Privilege::isPermitted($queryParams['module'], $recordListButton->get('permit'))) {
						continue;
					}
					if (isset($recordListButton->dataUrl)) {
						$recordListButton->dataUrl .= "&sourceModule={$this->getModuleName()}&sourceRecord={$this->getId()}";
						if ($relationModel->get('parentRecord') && ($relationField = $relationModel->getRelationField())) {
							$recordListButton->dataUrl .= '&' . $relationField->getName() . '=' . $relationModel->get('parentRecord')->getId();
						}
					}
					$links[$recordListButton->get('linklabel')] = $recordListButton;
				}
			}
		}
		return \App\Utils::changeSequence($links, App\Config::module($this->getModuleName(), 'recordRelatedListViewButtonSequence', []));
	}

	/**
	 * Function checks if user can assign record to himself.
	 *
	 * @return bool
	 */
	public function isCanAssignToHimself()
	{
		return $this->isPermitted('AssignToYourself') && \App\PrivilegeUtil::MEMBER_TYPE_GROUPS === \App\Fields\Owner::getType($this->getValueByField('assigned_user_id'))
			&& \array_key_exists(\App\User::getCurrentUserId(), \App\Fields\Owner::getInstance($this->getModuleName())->getAccessibleUsers('', 'owner'));
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
		if (!$this->has($fieldName) || '' === $this->get($fieldName)) {
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
	 * Function gets the value from this record.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 *
	 * @return mixed
	 */
	public function getValueByFieldModel(Vtiger_Field_Model $fieldModel)
	{
		if ($fieldModel->get('source_field_name')) {
			return isset($this->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()]) ? $this->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()]->get($fieldModel->getName()) : null;
		}
		return $this->get($fieldModel->getName());
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
				'modifiedby' => \App\User::getCurrentUserId(),
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
		$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
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
		if (!$this->isEmpty('imagename') && \App\Json::isJson($this->get('imagename')) && !\App\Json::isEmpty($this->get('imagename'))) {
			$image = \App\Json::decode($this->get('imagename'));
			if (empty($image) || !($image = \current($image)) || empty($image['path'])) {
				\App\Log::warning("Problem with data compatibility: No parameter path [{$this->get('imagename')}]");
				return [];
			}
			$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
			if (file_exists($image['path'])) {
				$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field=imagename&record={$this->getId()}&key={$image['key']}";
			} else {
				$image = [];
			}
		} else {
			foreach ($this->getModule()->getFieldsByType('multiImage') as $fieldModel) {
				if (!$this->isEmpty($fieldModel->getName()) && \App\Json::isJson($this->get($fieldModel->getName()))) {
					$image = \App\Json::decode($this->get($fieldModel->getName()));
					if (empty($image) || !($image = \current($image)) || empty($image['path'])) {
						\App\Log::warning("Problem with data compatibility: No parameter path [{$this->get('imagename')}]");
						return [];
					}
					$image['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
					if (file_exists($image['path'])) {
						$image['url'] = "file.php?module={$this->getModuleName()}&action=MultiImage&field={$fieldModel->getName()}&record={$this->getId()}&key={$image['key']}";
						break;
					}
					$image = [];
				}
			}
		}
		return $image;
	}
}
