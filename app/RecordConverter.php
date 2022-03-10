<?php

/**
 * RecordConverter class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class RecordConverter.
 */
class RecordConverter extends Base
{
	/** @var int Field to mapping */
	const FIELD_TO_MAPPING = 1;

	/**
	 * Source module name.
	 *
	 * @var string
	 */
	public $sourceModule = '';

	/**
	 * Source module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	public $sourceModuleModel;

	/**
	 * Destiny module name.
	 *
	 * @var string
	 */
	public $destinyModule = '';

	/**
	 * Destiny module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	public $destinyModuleModel;

	/**
	 * Record models of created records.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	public $cleanRecordModels = [];

	/**
	 * Source record models.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	public $recordModels = [];

	/**
	 * Convert field mapping.
	 *
	 * @var array|string
	 */
	public $fieldMapping;

	/**
	 * Convert inventory mapping.
	 *
	 * @var array|string
	 */
	public $inventoryMapping;

	/**
	 * Contains values from text parser.
	 *
	 * @var array
	 */
	public $textParserValues = [];

	/**
	 * Source module inventory fields.
	 *
	 * @var array
	 */
	public $sourceInvFields = [];

	/**
	 * Destiny module inventory fields.
	 *
	 * @var array
	 */
	public $destinyInvFields = [];

	/**
	 * Variable determines the possibility of fields mapping.
	 *
	 * @var bool
	 */
	public $fieldMappingExecute = false;

	/**
	 * Variable determines the possibility of inventory fields mapping.
	 *
	 * @var bool
	 */
	public $inventoryMappingExecute = false;

	/**
	 * Created records ids.
	 *
	 * @var int[]
	 */
	public $createdRecords = [];

	/**
	 * @var string
	 */
	public $error = '';

	/**
	 * Variable determines the type of group records.
	 *
	 * @var bool
	 */
	public $groupRecordConvert = false;

	/**
	 * Variable determines if merge field exist.
	 *
	 * @var bool
	 */
	public $isFieldMergeExists = false;

	/**
	 * Function to get the instance of the record converter model.
	 *
	 * @param int    $id
	 * @param string $moduleName
	 *
	 * @return \self
	 */
	public static function getInstanceById(int $id, string $moduleName = ''): self
	{
		$query = (new Db\Query())->from('a_#__record_converter')->where(['id' => $id]);
		if ($moduleName) {
			$query->andWhere(['source_module' => Module::getModuleId($moduleName)]);
		}
		$row = $query->one(Db::getInstance('admin'));
		$self = new self();
		if ($row) {
			$self->setData($row);
		} else {
			Log::error("Could not find record converter id: $id module name: $moduleName");
			throw new Exceptions\AppException('ERR_NOT_FOUND_RECORD_CONVERTER|' . $id);
		}
		return $self;
	}

	/**
	 * Function check if convert for module and view exist.
	 *
	 * @param int|string $moduleName
	 * @param string     $view
	 *
	 * @return bool
	 */
	public static function isActive($moduleName, string $view = ''): bool
	{
		return self::getQuery($moduleName, $view)->exists(Db::getInstance('admin'));
	}

	/**
	 * Function check if convert for record and view is available.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $view
	 * @param int|null             $userId
	 *
	 * @return bool
	 */
	public static function isAvailable(\Vtiger_Record_Model $recordModel, string $view = '', ?int $userId = null): bool
	{
		$result = false;
		if (null === $userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$dataReader = self::getQuery($recordModel->getModuleName(), $view)->createCommand()->query();
		while (!$result && ($row = $dataReader->read())) {
			$modulePermitted = false;
			$destinyModules = array_filter(explode(',', $row['destiny_module']));
			foreach ($destinyModules as $destinyModuleId) {
				if (!($modulePermitted = \App\Privilege::isPermitted(\App\Module::getModuleName($destinyModuleId), 'CreateView', false, $userId))) {
					break;
				}
			}
			$conditions = $row['conditions'] ? \App\Json::decode($row['conditions']) : [];
			$result = $modulePermitted && \App\Condition::checkConditions($conditions, $recordModel);
		}
		$dataReader->close();
		return $result;
	}

	/**
	 * Function gets module converters.
	 *
	 * @param string   $moduleName
	 * @param string   $view
	 * @param array    $recordIds
	 * @param int|null $userId
	 *
	 * @return array
	 */
	public static function getModuleConverters(string $moduleName, string $view = '', array $recordIds = [], ?int $userId = null): array
	{
		$converters = [];
		if (null === $userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$dataReader = self::getQuery($moduleName, $view)->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modulePermitted = false;
			$destinyModules = array_filter(explode(',', $row['destiny_module']));
			foreach ($destinyModules as $destinyModuleId) {
				if (!($modulePermitted = \App\Privilege::isPermitted(\App\Module::getModuleName($destinyModuleId), 'CreateView', false, $userId))) {
					break;
				}
			}
			$conditions = $row['conditions'] ? \App\Json::decode($row['conditions']) : [];
			if ($modulePermitted && $conditions && $recordIds) {
				foreach ($recordIds as $recordId) {
					if (\App\Condition::checkConditions($conditions, \Vtiger_Record_Model::getInstanceById($recordId))) {
						$converters[$row['id']] = $row;
						break;
					}
				}
			} elseif ($modulePermitted) {
				$converters[$row['id']] = $row;
			}
		}
		$dataReader->close();
		return $converters;
	}

	/**
	 * Function return query about module converters in view.
	 *
	 * @param int|string $moduleName
	 * @param string     $view
	 *
	 * @return Db\Query
	 */
	public static function getQuery($moduleName, string $view = ''): Db\Query
	{
		if (\is_string($moduleName)) {
			$moduleName = Module::getModuleId($moduleName);
		}
		if (Cache::has('getQueryRecordConverter', $moduleName . $view)) {
			return Cache::get('getQueryRecordConverter', $moduleName . $view);
		}
		$query = (new Db\Query())->from('a_#__record_converter')->where(['source_module' => $moduleName, 'status' => 1]);
		if ($view) {
			$query->andWhere(['show_in_' . \strtolower($view) => 1]);
		}
		Cache::save('getQueryRecordConverter', $moduleName . $view, $query);
		return $query;
	}

	/**
	 * Gets ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function variable initializing.
	 *
	 * @throws Exceptions\AppException
	 */
	public function init()
	{
		$this->sourceModule = Module::getModuleName($this->get('source_module'));
		$this->sourceModuleModel = \Vtiger_Module_Model::getInstance($this->sourceModule);
		$this->fieldMapping = $this->get('field_mapping') ? Json::decode($this->get('field_mapping')) : [];
		$this->getMappingFields();
		$this->inventoryMapping = $this->get('inv_field_mapping') ? Json::decode($this->get('inv_field_mapping')) : [];
		if ($this->sourceModuleModel->isInventory()) {
			$this->sourceInvFields = \Vtiger_Inventory_Model::getInstance($this->sourceModule)->getFields();
		}
		$this->defaultValuesCreatedRecord = $this->get('default_values') ? Json::decode($this->get('default_values')) : [];
	}

	/**
	 * Check permissions.
	 *
	 * @param int $recordId
	 *
	 * @return bool
	 */
	public function isPermitted(int $recordId): bool
	{
		$moduleName = Module::getModuleName($this->get('source_module'));
		return \App\Privilege::isPermitted($moduleName, 'RecordConventer') && \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)
			&& (\App\Json::isEmpty($this->get('conditions')) || \App\Condition::checkConditions(\App\Json::decode($this->get('conditions')), \Vtiger_Record_Model::getInstanceById($recordId)));
	}

	/**
	 * Gets fields form mapping.
	 *
	 * @return array
	 */
	public function getMappingFields(): array
	{
		if (!isset($this->mapping)) {
			$this->mapping = [];
			$dataReader = (new Db\Query())->from('a_#__record_converter_mapping')->where(['id' => $this->getId()])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->mapping[$row['dest_module']][$row['dest_field']] = $row;
			}
		}

		return $this->mapping;
	}

	/**
	 * Main function of class.
	 *
	 * @param array $records
	 *
	 * @throws Exceptions\AppException
	 */
	public function process(array $records)
	{
		$this->init();
		$recordsAmount = \count($records);
		foreach ($this->getDestinyModules() as $destinyModuleName) {
			$this->initDestinyModuleValues($destinyModuleName);
			$this->checkFieldMergeExist();
			$this->setFieldsMapCanExecute($recordsAmount);
			$this->setInvMapCanExecute();
			if ($this->isFieldMergeExists) {
				$this->groupRecordConvert = true;
				$this->getRecordsGroupBy($records);
			} else {
				$this->getRecordModelsWithoutMerge($records);
			}
		}
	}

	/**
	 * Gets destiny modules.
	 *
	 * @return string[]
	 */
	public function getDestinyModules(): array
	{
		$modules = [];
		foreach (explode(',', $this->get('destiny_module')) as $destinyModuleId) {
			$destinyModuleName = Module::getModuleName($destinyModuleId);
			if (Privilege::isPermitted($destinyModuleName, 'CreateView')) {
				$modules[$destinyModuleId] = $destinyModuleName;
			}
		}
		return $modules;
	}

	/**
	 * Function check if field mapping process can be proced.
	 *
	 * @param int $recordsAmount
	 */
	public function setFieldsMapCanExecute(int $recordsAmount)
	{
		$this->fieldMappingExecute = $this->fieldMapping && (isset($this->fieldMapping['auto']) || isset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()])) && (!$this->isFieldMergeExists || 1 === $recordsAmount);
	}

	/**
	 * Function check if inventory mapping process can be proced.
	 */
	public function setInvMapCanExecute()
	{
		$this->inventoryMappingExecute = $this->inventoryMapping && $this->sourceModuleModel->isInventory() && $this->destinyModuleModel->isInventory();
	}

	/**
	 * Function initializing destiny module values.
	 *
	 * @param string $moduleName
	 */
	public function initDestinyModuleValues(string $moduleName)
	{
		$this->destinyModule = $moduleName;
		$this->destinyModuleModel = \Vtiger_Module_Model::getInstance($this->destinyModule);
		if ($this->destinyModuleModel->isInventory()) {
			$this->destinyInvFields = \Vtiger_Inventory_Model::getInstance($this->destinyModule)->getFields();
		}
	}

	/**
	 * Function to edit process.
	 *
	 * @param int    $record
	 * @param string $destinyModule
	 *
	 * @throws Exceptions\AppException
	 *
	 * @return Vtiger_Module_Model
	 */
	public function processToEdit(int $record, string $destinyModule): \Vtiger_Record_Model
	{
		$this->initDestinyModuleValues($destinyModule);
		$this->init();
		$this->checkFieldMergeExist();
		if ($this->fieldMapping && (isset($this->fieldMapping['auto']) || isset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()])) && $this->isFieldMergeExists) {
			$this->fieldMappingExecute = true;
		}
		$this->setInvMapCanExecute();
		$this->getRecordModelsWithoutMerge([$record]);
		return \current($this->cleanRecordModels);
	}

	/**
	 * Function get query to group records.
	 *
	 * @param int[] $records
	 *
	 * @return array
	 */
	public function getGroupRecords(array $records): array
	{
		$fieldModel = $this->sourceModuleModel->getField($this->get('field_merge'));
		$focus = \CRMEntity::getInstance($this->sourceModule);
		return (new Db\Query())->select([$fieldModel->getTableName() . ".{$fieldModel->getColumnName()}", $focus->tab_name_index[$fieldModel->getTableName()]])->from($fieldModel->getTableName())->where([$focus->tab_name_index[$fieldModel->getTableName()] => $records])->createCommand()->queryAllByGroup(2);
	}

	/**
	 * Function prepare records model group by field merge.
	 *
	 * @param array $records
	 */
	public function getRecordsGroupBy(array $records)
	{
		foreach ($this->getGroupRecords($records) as $groupBy => $recordsId) {
			$this->cleanRecordModels[$groupBy] = \Vtiger_Record_Model::getCleanInstance($this->destinyModule);
			$this->cleanRecordModels[$groupBy]->set($this->fieldMapping['field_merge'][$this->destinyModuleModel->getId()], $groupBy);
			foreach ($recordsId as $recordId) {
				if (!isset($this->recordModels[$groupBy][$recordId])) {
					$this->recordModels[$groupBy][$recordId] = \Vtiger_Record_Model::getInstanceById($recordId, $this->sourceModule);
				}
			}
			$this->processInventoryMapping();
			$this->checkIfDuplicateRecordExists();
			$this->saveChanges();
		}
	}

	/**
	 * Function prepare records model.
	 *
	 * @param array $records
	 */
	public function getRecordModelsWithoutMerge(array $records)
	{
		foreach ($records as $recordId) {
			if (!$this->isPermitted($recordId)) {
				continue;
			}
			$this->cleanRecordModels[$recordId] = \Vtiger_Record_Model::getCleanInstance($this->destinyModule);
			if (!isset($this->recordModels[$recordId])) {
				$this->recordModels[$recordId] = \Vtiger_Record_Model::getInstanceById($recordId, $this->sourceModule);
			}
			$this->processFieldMapping();
			$this->checkFieldMappingFields();
			$this->processInventoryMapping();
			$this->checkIfDuplicateRecordExists();
			if (!$this->get('redirect_to_edit')) {
				$this->saveChanges();
			}
		}
	}

	/**
	 * Function prepare mapping fields.
	 */
	public function processFieldMapping()
	{
		if (isset($this->inventoryMapping[0]) && 'auto' === $this->inventoryMapping[0]) {
			$this->initFieldValuesAuto();
		} else {
			$this->initFieldValuesByUser();
		}
	}

	/**
	 * Function set values to new record automatically.
	 */
	public function initFieldValuesAuto()
	{
		foreach ($this->cleanRecordModels as $groupBy => $newRecordModel) {
			foreach ($this->sourceModuleModel->getFields() as $fieldModel) {
				if ('picklist' === $fieldModel->getFieldDataType()) {
					if (Fields\Picklist::isExists($fieldModel->getFieldName(), $this->recordModels[$groupBy]->get($fieldModel->getFieldName()))) {
						$newRecordModel->set($fieldModel->getFieldName(), $this->recordModels[$groupBy]->get($fieldModel->getFieldName()));
					}
				} else {
					$newRecordModel->set($fieldModel->getFieldName(), $this->recordModels[$groupBy]->get($fieldModel->getFieldName()));
				}
			}
		}
	}

	/**
	 * Function set values to new record defined by user.
	 */
	public function initFieldValuesByUser()
	{
		$fieldsData = $this->getMappingFields()[$this->destinyModuleModel->getId()] ?? [];
		$destFieldList = $this->destinyModuleModel->getFieldsById();
		foreach ($this->cleanRecordModels as $key => $cleanRecordModel) {
			$sourceFieldList = $this->recordModels[$key]->getModule()->getFieldsById();
			foreach ($fieldsData as $destFieldId => $fieldRow) {
				if (self::FIELD_TO_MAPPING === $fieldRow['state'] && ($fieldModel = $destFieldList[$destFieldId])->isEditable() && ($sourceFieldModel = $sourceFieldList[$fieldRow['source_field']])->isActiveField()) {
					$cleanRecordModel->set($fieldModel->getName(), $this->recordModels[$key]->get($sourceFieldModel->getName()));
				}
			}
		}
	}

	/**
	 * Function save changes in new record models.
	 */
	public function saveChanges()
	{
		foreach ($this->cleanRecordModels as $key => $recordModel) {
			try {
				$recordModel->save();
				$this->createdRecords[] = $recordModel->getId();
				$this->set('sourceRecord', $this->recordModels[$key]);
				$eventHandler = $recordModel->getEventHandler();
				$eventHandler->setParams(['converter' => $this]);
				$eventHandler->trigger(EventHandler::RECORD_CONVERTER_AFTER_SAVE);
				unset($this->cleanRecordModels[$key]);
			} catch (\Throwable $ex) {
				$this->error = $ex->getMessage();
			}
		}
	}

	/**
	 * Function prepare inventory mapping.
	 */
	public function processInventoryMapping()
	{
		if ($this->inventoryMappingExecute && $this->inventoryMapping) {
			if (isset($this->inventoryMapping[0]) && 'auto' === $this->inventoryMapping[0]) {
				$this->initInventoryValuesAuto();
			} else {
				$this->initInventoryValuesByUser();
			}
		}
	}

	/**
	 * Function prepare auto inventory mapping.
	 */
	private function initInventoryValuesAuto()
	{
		$invData = [];
		$counter = 1;
		foreach ($this->cleanRecordModels as $groupBy => $newRecordModel) {
			if (!\is_array($this->recordModels[$groupBy])) {
				$sourceRecordModels = [$this->recordModels[$groupBy]];
			} else {
				$sourceRecordModels = $this->recordModels[$groupBy];
			}
			foreach ($sourceRecordModels as $recordModel) {
				if (!\is_array($recordModel)) {
					$recordModel = [$recordModel];
				}
				foreach ($recordModel as $recordModelGroupBy) {
					foreach ($recordModelGroupBy->getInventoryData() as $inventoryRow) {
						foreach ($this->destinyInvFields as $columnName => $fieldModel) {
							if (isset($this->sourceInvFields[$columnName])) {
								$inventoryFieldValue = $inventoryRow[$columnName];
							} else {
								$inventoryFieldValue = $fieldModel->getDefaultValue();
							}
							$invData[$groupBy][$counter][$columnName] = $inventoryFieldValue;
							$fieldCustomColumn = $fieldModel->getCustomColumn();
							if ($fieldCustomColumn) {
								foreach (array_keys($fieldCustomColumn) as $customColumn) {
									$invData[$groupBy][$counter][$customColumn] = $inventoryRow[$customColumn] ?? [];
								}
							}
						}
						++$counter;
					}
				}
			}
		}
		$newRecordModel->initInventoryDataFromRequest(new Request(['inventory' => $invData[$groupBy]], false));
	}

	/**
	 * Function prepare user inventory mapping.
	 */
	private function initInventoryValuesByUser()
	{
		$invData = [];
		$counter = 1;
		foreach ($this->cleanRecordModels as $groupBy => $newRecordModel) {
			if (!\is_array($this->recordModels[$groupBy])) {
				$sourceRecordModels = [$this->recordModels[$groupBy]];
			} else {
				$sourceRecordModels = $this->recordModels[$groupBy];
			}
			foreach ($sourceRecordModels as $recordModel) {
				if (!\is_array($recordModel)) {
					$recordModel = [$recordModel];
				}
				foreach ($recordModel as $recordModelGroupBy) {
					foreach ($recordModelGroupBy->getInventoryData() as $inventoryRow) {
						if (isset($this->inventoryMapping[$this->destinyModuleModel->getId()])) {
							foreach ($this->inventoryMapping[$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
								if ($fieldCustomColumn = $this->destinyInvFields[$destinyField]->getCustomColumn()) {
									foreach (array_keys($fieldCustomColumn) as $customColumn) {
										$invData[$groupBy][$counter][$customColumn] = $inventoryRow[$customColumn] ?? [];
									}
								}
								$invData[$groupBy][$counter][$destinyField] = $inventoryRow[$sourceField];
							}
						}
						foreach ($this->destinyInvFields as $columnName => $fieldModel) {
							if (!isset($invData[$groupBy][$counter][$columnName]) && $fieldModel->has('defaultValue')) {
								$invData[$groupBy][$counter][$columnName] = $fieldModel->getDefaultValue();
							} elseif (!isset($invData[$groupBy][$counter][$columnName])) {
								$invData[$groupBy][$counter][$columnName] = $inventoryRow[$columnName];
							}
							if ($fieldCustomColumn = $fieldModel->getCustomColumn()) {
								foreach (array_keys($fieldCustomColumn) as $customColumn) {
									$invData[$groupBy][$counter][$customColumn] = '';
								}
							}
						}
						$invData[$groupBy][$counter]['id'] = $inventoryRow['id'];
						++$counter;
					}
				}
			}
		}
		$newRecordModel->initInventoryDataFromRequest(new Request(['inventory' => $invData[$groupBy]], false));
	}

	/**
	 * Function check mapped fields.
	 */
	public function checkFieldMappingFields()
	{
		if (isset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()])) {
			foreach ($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
				if (!$this->destinyModuleModel->getField($destinyField)) {
					unset($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()][$destinyField]);
				}
			}
		}
	}

	/**
	 * Function check if merge can be execute.
	 */
	public function checkFieldMergeExist()
	{
		$this->isFieldMergeExists = false;
		if (isset($this->fieldMapping['field_merge'])) {
			$destinyReferenceFields = $this->destinyModuleModel->getFieldsByReference();
			$referenceDestinyField = $this->fieldMapping['field_merge'][$this->destinyModuleModel->getId()];
			if ($referenceDestinyField) {
				if (!$this->destinyModuleModel->getField($referenceDestinyField) || !$this->sourceModuleModel->getField($this->get('field_merge')) || !isset($destinyReferenceFields[$referenceDestinyField])) {
					$this->isFieldMergeExists = false;
				}
				$this->isFieldMergeExists = true;
			}
		}
	}

	/**
	 * Function get query for searching duplicates.
	 *
	 * @return Db\Query
	 */
	public function getQueryForDuplicate()
	{
		$focus = \CRMEntity::getInstance($this->destinyModule);
		return (new Db\Query())->from($focus->table_name)->innerJoin($focus->customFieldTable[0], $focus->table_name . '.' . $focus->table_index . '=' . $focus->customFieldTable[0] . '.' . $focus->customFieldTable[1])
			->innerJoin('vtiger_crmentity', $focus->table_name . '.' . $focus->table_index . '= vtiger_crmentity.crmid');
	}

	/**
	 * Function check if exist duplicate of records.
	 */
	public function checkIfDuplicateRecordExists()
	{
		if ($this->get('check_duplicate') && isset($this->fieldMapping['field_merge'][$this->destinyModuleModel->getId()])) {
			$referenceDestinyField = $this->fieldMapping['field_merge'][$this->destinyModuleModel->getId()];
			if ($referenceDestinyField) {
				foreach ($this->cleanRecordModels as $groupBy => $recordModel) {
					$columnsToCheck = [];
					if ($this->isFieldMergeExists) {
						if ($this->groupRecordConvert) {
							$columnsToCheck[$referenceDestinyField] = $groupBy;
						} else {
							$sourceRecordModel = \Vtiger_Record_Model::getInstanceById($groupBy, $this->sourceModule);
							$columnsToCheck[$referenceDestinyField] = $sourceRecordModel->get($this->get('field_merge'));
						}
					} else {
						foreach ($this->fieldMapping['mapping'][$this->destinyModuleModel->getId()] as $destinyField => $sourceField) {
							$columnsToCheck[$destinyField] = $this->textParserValues[$groupBy][$sourceField];
						}
					}
					$query = $this->getQueryForDuplicate();
					if ($query->where($columnsToCheck)->exists()) {
						unset($this->cleanRecordModels[$groupBy]);
					}
				}
			}
		}
	}
}
