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

class Import_Data_Action extends Vtiger_Action_Controller
{

	public $id;
	public $user;
	public $module;
	public $type;
	public $fieldMapping;
	public $mergeType;
	public $mergeFields;
	public $defaultValues;
	public $importedRecordInfo = [];
	protected $allPicklistValues = [];
	protected $inventoryFieldMapData = [];
	public $batchImport = true;
	public $entitydata = [];

	const IMPORT_RECORD_NONE = 0;
	const IMPORT_RECORD_CREATED = 1;
	const IMPORT_RECORD_SKIPPED = 2;
	const IMPORT_RECORD_UPDATED = 3;
	const IMPORT_RECORD_MERGED = 4;
	const IMPORT_RECORD_FAILED = 5;

	/**
	 * Constructor
	 * @param array $importInfo
	 * @param Users_Record_Model $user
	 */
	public function __construct($importInfo, $user)
	{
		$this->id = $importInfo['id'];
		$this->module = $importInfo['module'];
		$this->fieldMapping = $importInfo['field_mapping'];
		$this->mergeType = $importInfo['merge_type'];
		$this->mergeFields = $importInfo['merge_fields'];
		$this->defaultValues = $importInfo['default_values'];
		$this->user = $user;
	}

	public function process(Vtiger_Request $request)
	{
		return;
	}

	/**
	 * Get default field values
	 * @return array
	 */
	public function getDefaultFieldValues()
	{
		$key = $this->module . '_' . $this->user->id;
		if (\App\Cache::staticHas('DefaultFieldValues', $key)) {
			return \App\Cache::staticGet('DefaultFieldValues', $key);
		}

		$defaultValues = [];
		if (!empty($this->defaultValues)) {
			if (!is_array($this->defaultValues)) {
				$this->defaultValues = \App\Json::decode($this->defaultValues);
			}
			if ($this->defaultValues) {
				$defaultValues = $this->defaultValues;
			}
		}
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		foreach ($moduleModel->getMandatoryFieldModels() as $fieldInstance) {
			$mandatoryFieldName = $fieldInstance->getName();
			if (empty($defaultValues[$mandatoryFieldName])) {
				if ($fieldInstance->getFieldDataType() === 'owner') {
					$defaultValues[$mandatoryFieldName] = $this->user->id;
				} elseif (!in_array($fieldInstance->getFieldDataType(), ['datetime', 'date', 'time', 'reference'])) {
					$defaultValues[$mandatoryFieldName] = '????';
				}
			}
		}
		foreach ($moduleModel->getFields() as $fieldName => $fieldInstance) {
			$fieldDefaultValue = $fieldInstance->getDefaultFieldValue();
			if (empty($defaultValues[$fieldName])) {
				if ($fieldInstance->getUIType() === 52) {
					$defaultValues[$fieldName] = $this->user->id;
				} elseif (!empty($fieldDefaultValue)) {
					$defaultValues[$fieldName] = $fieldDefaultValue;
				}
			}
		}
		\App\Cache::staticSave('DefaultFieldValues', $key, $defaultValues);
		return $defaultValues;
	}

	public function import()
	{
		if (!$this->initializeImport())
			return false;
		$this->importData();
		$this->finishImport();
	}

	/**
	 * Import data
	 */
	public function importData()
	{
		$this->importRecords();
		$this->updateModuleSequenceNumber();
	}

	public function initializeImport()
	{
		$lockInfo = Import_Lock_Action::isLockedForModule($this->module);
		if ($lockInfo != null) {
			if ($lockInfo['userid'] != $this->user->id) {
				Import_Utils_Helper::showImportLockedError($lockInfo);
				return false;
			} else {
				return true;
			}
		} else {
			Import_Lock_Action::lock($this->id, $this->module, $this->user);
			return true;
		}
	}

	public function finishImport()
	{
		Import_Lock_Action::unLock($this->user, $this->module);
		Import_Queue_Action::remove($this->id);
	}

	public function updateModuleSequenceNumber()
	{
		$moduleName = $this->module;
		$focus = CRMEntity::getInstance($moduleName);
		$focus->updateMissingSeqNumber($moduleName);
	}

	/**
	 * Update import status
	 * @param int $entryId
	 * @param array $entityInfo
	 */
	public function updateImportStatus($entryId, $entityInfo)
	{
		$tableName = Import_Module_Model::getDbTableName($this->user);
		\App\Db::getInstance()->createCommand()->update($tableName, ['temp_status' => $entityInfo['status'], 'recordid' => $entityInfo['id']], ['id' => $entryId])->execute();
	}

	/**
	 * Import records
	 * @return boolean
	 */
	public function importRecords()
	{
		$moduleName = $this->module;
		$tableName = Import_Module_Model::getDbTableName($this->user);

		$query = new \App\Db\Query();
		$query->from($tableName)->where(['temp_status' => self::IMPORT_RECORD_NONE]);
		if ($this->batchImport) {
			$importBatchLimit = \AppConfig::module('Import', 'BATCH_LIMIT');
			$query->limit($importBatchLimit);
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$isInventory = $moduleModel->isInventory();
		if ($isInventory) {
			$inventoryTableName = Import_Module_Model::getInventoryDbTableName($this->user);
		}

		$fieldMapping = $this->fieldMapping;

		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$handlerOn = false;
			$rowId = $row['id'];

			if ($isInventory) {
				$inventoryFieldData = (new \App\Db\Query())->from($inventoryTableName)->where(['id' => $rowId])->all();
			}

			$entityInfo = null;
			$fieldData = [];
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldData[$fieldName] = $row[$fieldName];
			}

			$mergeType = $this->mergeType;
			$createRecord = false;

			if (!empty($mergeType) && $mergeType !== Import_Module_Model::AUTO_MERGE_NONE) {
				$queryGenerator = new App\QueryGenerator($moduleName, $this->user->id);
				$queryGenerator->setFields(['id']);
				$moduleFields = $queryGenerator->getModuleFields();
				$mergeFields = $this->mergeFields;
				foreach ($mergeFields as $index => $mergeField) {
					$comparisonValue = $fieldData[$mergeField];
					$fieldInstance = $moduleFields[$mergeField];
					if ($fieldInstance->getFieldDataType() == 'owner') {
						$ownerId = \App\User::getUserIdByName($comparisonValue);
						if (empty($ownerId)) {
							$ownerId = \App\Fields\Owner::getGroupId($comparisonValue);
						}
						$comparisonValue = $ownerId ? $ownerId : 0;
					}
					if ($fieldInstance->getFieldDataType() == 'reference') {
						if (strpos($comparisonValue, '::::') > 0) {
							$referenceFileValueComponents = explode('::::', $comparisonValue);
						} else {
							$referenceFileValueComponents = explode(':::', $comparisonValue);
						}
						if (count($referenceFileValueComponents) > 1) {
							$comparisonValue = trim($referenceFileValueComponents[1]);
						}
					}
					if (in_array($fieldInstance->getFieldDataType(), ['date', 'datetime'])) {
						$comparisonValue = DateTimeField::convertToUserFormat($comparisonValue);
					}
					$queryGenerator->addCondition($mergeField, $comparisonValue, 'e');
				}
				$query = $queryGenerator->createQuery();
				$baseRecordId = $query->scalar();
				if ($baseRecordId) {
					switch ($mergeType) {
						case Import_Module_Model::AUTO_MERGE_IGNORE:
							$entityInfo['status'] = self::IMPORT_RECORD_SKIPPED;
							break;
						case Import_Module_Model::AUTO_MERGE_OVERWRITE:
							$fieldData = $this->transformForImport($fieldData);
							$this->updateRecordByModel($baseRecordId, $fieldData, $moduleName);
							$entityInfo['status'] = self::IMPORT_RECORD_UPDATED;
							break;
						case Import_Module_Model::$AUTO_MERGE_MERGEFIELDS:
							$defaultFieldValues = $this->getDefaultFieldValues();
							foreach ($fieldData as $fieldName => &$fieldValue) {
								if (empty($fieldValue) && !empty($defaultFieldValues[$fieldName])) {
									$fieldValue = $defaultFieldValues[$fieldName];
								}
							}
							$fieldData = array_filter($fieldData);
							$fieldData = $this->transformForImport($fieldData, false, false);
							$this->updateRecordByModel($baseRecordId, $fieldData, $moduleName);
							$entityInfo['status'] = self::$IMPORT_RECORD_MERGED;
							break;
						default:
							$createRecord = true;
							break;
					}
				} else {
					$createRecord = true;
				}
			} else {
				$createRecord = true;
			}
			if ($createRecord) {
				$fieldData = $this->transformForImport($fieldData);
				if ($fieldData && $isInventory) {
					$inventoryFieldData = $this->transformInventoryForImport($inventoryFieldData);
					$fieldData['inventoryData'] = $inventoryFieldData;
				}
				if ($fieldData === null) {
					$entityInfo = null;
				} else {
					$entityInfo = $this->createRecordByModel($moduleName, $fieldData);
					$handlerOn = true;
				}
			}
			if ($entityInfo === null) {
				$entityInfo = ['id' => null, 'status' => self::IMPORT_RECORD_FAILED];
			}
			$this->importedRecordInfo[$rowId] = $entityInfo;
			$this->updateImportStatus($rowId, $entityInfo);
		}
		return true;
	}

	public function transformInventoryForImport($inventoryData)
	{
		$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->module);
		$inventoryFields = $inventoryFieldModel->getFields();
		$maps = $inventoryFieldModel->getAutoCompleteFields();

		foreach ($inventoryData as &$data) {
			$this->currentInventoryRawData = $data;
			unset($data['id']);
			foreach ($data as $fieldName => &$value) {
				$fieldInstance = $inventoryFields[$fieldName];
				if ($fieldInstance) {
					if (in_array($fieldInstance->getName(), ['Name', 'Reference'])) {
						$value = $this->transformInventoryReference($value);
					} elseif ($fieldInstance->getName() == 'Currency') {
						$curencyName = $value;
						$value = \App\Currency::getCurrencyIdByName($entityLabel);
						$currencyParam = $data['currencyparam'];
						$currencyParam = $fieldInstance->getCurrencyParam([], $currencyParam);
						$newCurrencyParam = [];
						foreach ($currencyParam as $key => $currencyData) {
							$valueData = \App\Currency::getCurrencyIdByName($entityLabel);
							if ($valueData) {
								$currencyData['value'] = $valueData;
								$newCurrencyParam[$valueData] = $currencyData;
							}
						}
						$data['currencyparam'] = \App\Json::encode($newCurrencyParam);
					} elseif (array_key_exists($fieldName, $maps)) {
						$value = $this->transformInventoryFieldFromMap($value, $maps[$fieldName]);
					}
				}
			}
		}
		$this->currentInventoryRawData = [];
		return $inventoryData;
	}

	public function transformInventoryFieldFromMap($value, $mapData)
	{
		if (!empty($value)) {
			if ($this->currentInventoryRawData['name']) {
				list($entityName, $recordId) = $this->transformInventoryReference($this->currentInventoryRawData['name'], true);
			}
			if ($entityName) {
				if ($this->inventoryFieldMapData[$mapData['field']] && $this->inventoryFieldMapData[$mapData['field']][$entityName]) {
					$fieldObject = $this->inventoryFieldMapData[$mapData['field']][$entityName];
				} else {
					$moduleObject = vtlib\Module::getInstance($entityName);
					$fieldObject = $moduleObject ? Vtiger_Field_Model::getInstance($mapData['field'], $moduleObject) : null;
					if (!is_array($this->inventoryFieldMapData[$mapData['field']])) {
						$this->inventoryFieldMapData[$mapData['field']] = [];
					}
					$this->inventoryFieldMapData[$mapData['field']][$entityName] = $fieldObject;
				}
				if ($fieldObject) {
					$type = $fieldObject->getFieldDataType();
					switch ($type) {
						case 'picklist':
							$picklist = $fieldObject->getPicklistValues();
							if (in_array($value, $picklist)) {
								$value = array_search($value, $picklist);
							} elseif (array_key_exists($value, $picklist)) {
								$value = $value;
							} else {
								$value = '';
							}
							break;
						default:
							break;
					}
				} else {
					$value = '';
				}
			}
		}
		return $value;
	}

	/**
	 * Function transforms value for reference type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return mixed
	 */
	public function transformInventoryReference($value, $getArray = false)
	{
		$value = trim($value);
		if (!empty($value)) {
			if (strpos($value, '::::') > 0) {
				$fieldValueDetails = explode('::::', $value);
			} else if (strpos($value, ':::') > 0) {
				$fieldValueDetails = explode(':::', $value);
			}
			if (is_array($fieldValueDetails) && count($fieldValueDetails) > 1) {
				$referenceModuleName = trim($fieldValueDetails[0]);
				$entityLabel = trim($fieldValueDetails[1]);
				$value = \App\Record::getCrmIdByLabel($referenceModuleName, $entityLabel);
			}
		}
		return $getArray ? [$referenceModuleName, $value] : $value;
	}

	/**
	 * Function transforms value for owner type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return int
	 */
	public function transformOwner($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$ownerId = \App\User::getUserIdByName(trim($fieldValue));
		if (empty($ownerId)) {
			$ownerId = \App\Fields\Owner::getGroupId($fieldValue);
		}
		if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
			$ownerId = $defaultFieldValues[$fieldName];
		}
		if (!empty($ownerId) && \App\Fields\Owner::getType($ownerId) === 'Users' && !array_key_exists($ownerId, \App\Fields\Owner::getInstance($fieldInstance->getModuleName(), $this->user->id)->getAccessibleUsers('', 'owner'))) {
			$ownerId = '';
		}
		if (empty($ownerId)) {
			$ownerId = $this->user->id;
		}
		return $ownerId;
	}

	/**
	 * Function transforms value for sharedOwner type field
	 * @param string $fieldValue
	 * @return array
	 */
	public function transformSharedOwner($fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$values = [];
		if ($fieldValue) {
			$owners = explode(',', $fieldValue);
			foreach ($owners as $owner) {
				$ownerId = \App\User::getUserIdByName(trim($owner));
				if (empty($ownerId)) {
					$ownerId = \App\Fields\Owner::getGroupId($owner);
				}
				if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
					$ownerId = $defaultFieldValues[$fieldName];
				}
				if (!empty($ownerId)) {
					$values[] = $ownerId;
				}
			}
		}
		return $values;
	}

	/**
	 * Function transforms value for multipicklist type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return string
	 */
	public function transformMultipicklist($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
		$trimmedValue = trim($fieldValue);
		if (!$trimmedValue && isset($defaultFieldValues[$fieldName])) {
			$explodedValue = explode(',', $defaultFieldValues[$fieldName]);
		} else {
			$explodedValue = explode(' |##| ', $trimmedValue);
		}
		foreach ($explodedValue as $key => $value) {
			$explodedValue[$key] = trim($value);
		}
		$implodeValue = implode(' |##| ', $explodedValue);
		return $implodeValue;
	}

	/**
	 * Function transforms value for reference type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return bool|int
	 */
	public function transformReference($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
		$entityId = false;
		if (!empty($fieldValue)) {
			if (strpos($fieldValue, '::::') !== false) {
				$fieldValueDetails = explode('::::', $fieldValue);
			} else if (strpos($fieldValue, ':::') !== false) {
				$fieldValueDetails = explode(':::', $fieldValue);
			}
			if ($fieldValueDetails && count($fieldValueDetails) > 1) {
				$referenceModuleName = trim($fieldValueDetails[0]);
				$entityLabel = trim($fieldValueDetails[1]);
				if (\App\Module::isModuleActive($referenceModuleName)) {
					$entityId = \App\Record::getCrmIdByLabel($referenceModuleName, decode_html($entityLabel));
				} else {
					$referenceModuleName = $defaultFieldValues[$fieldName];
					$referencedModules = $fieldInstance->getReferenceList();
					if ($referenceModuleName && in_array($defaultFieldValues[$fieldName], $referencedModules)) {
						$entityId = \App\Record::getCrmIdByLabel($referenceModuleName, $entityLabel);
					}
				}
			} else {
				$referencedModules = $fieldInstance->getReferenceList();
				$entityLabel = $fieldValue;
				foreach ($referencedModules as $referenceModule) {
					$referenceModuleName = $referenceModule;
					if ($referenceModule === 'Users') {
						$referenceEntityId = \App\User::getUserIdByName(trim($entityLabel));
						if (empty($referenceEntityId) || !array_key_exists($referenceEntityId, \App\Fields\Owner::getInstance($fieldInstance->getModuleName(), $this->user->id)->getAccessibleUsers('', 'owner'))) {
							$referenceEntityId = $this->user->id;
						}
					} elseif ($referenceModule === 'Currency') {
						$referenceEntityId = \App\Currency::getCurrencyIdByName($entityLabel);
					} else {
						$referenceEntityId = \App\Record::getCrmIdByLabel($referenceModule, decode_html($entityLabel));
					}
					if ($referenceEntityId) {
						$entityId = $referenceEntityId;
						break;
					}
				}
			}
			if (\AppConfig::module('Import', 'CREATE_REFERENCE_RECORD') && empty($entityId) && !empty($referenceModuleName)) {
				if (\App\Privilege::isPermitted($referenceModuleName, 'CreateView')) {
					try {
						$entityId = $this->createEntityRecord($referenceModuleName, $entityLabel);
					} catch (Exception $e) {
						$entityId = false;
					}
				}
			}
		}
		return $entityId;
	}

	/**
	 * Function transforms value for picklist type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return string
	 */
	public function transformPicklist($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$defaultCharset = \AppConfig::main('default_charset', 'UTF-8');
		$fieldName = $fieldInstance->getFieldName();
		$fieldValue = trim($fieldValue);

		if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
			$fieldValue = $defaultFieldValues[$fieldName];
		}
		if (!isset($this->allPicklistValues[$fieldName])) {
			$this->allPicklistValues[$fieldName] = array_keys($fieldInstance->getPicklistValues());
		}
		$allPicklistValues = $this->allPicklistValues[$fieldName];
		$picklistValueInLowerCase = strtolower(htmlentities($fieldValue, ENT_QUOTES, $defaultCharset));
		$allPicklistValuesInLowerCase = array_map('strtolower', $allPicklistValues);
		$picklistDetails = array_combine($allPicklistValuesInLowerCase, $allPicklistValues);

		if (!in_array($picklistValueInLowerCase, $allPicklistValuesInLowerCase)) {
			if (\AppConfig::module('Import', 'ADD_PICKLIST_VALUE')) {
				$moduleObject = \vtlib\Module::getInstance($this->module);
				$fieldObject = \vtlib\Field::getInstance($fieldName, $moduleObject);
				$fieldObject->setPicklistValues([$fieldValue]);
				unset($this->allPicklistValues[$fieldName]);
				\App\Cache::delete('getPickListValues', $fieldName);
			}
		} else {
			$fieldValue = $picklistDetails[$picklistValueInLowerCase];
		}
		return $fieldValue;
	}

	/**
	 * Function transforms value for tree type field
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string $fieldValue
	 * @return string
	 */
	public function transformTree($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
		if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
			$fieldValue = $defaultFieldValues[$fieldName];
		} else if (!empty($fieldValue)) {
			$value = trim($fieldValue);
			$fieldValue = '';
			$trees = \App\Fields\Tree::getValuesById((int) $fieldInstance->getFieldParams());
			foreach ($trees as $tree) {
				if ($tree['name'] === $value) {
					$fieldValue = $tree['tree'];
					break;
				}
			}
		}
		return $fieldValue;
	}

	/**
	 * Function parses data to import
	 * @param array $fieldData
	 * @param array $fillDefault
	 * @param bool $checkMandatoryFieldValues
	 * @return array
	 */
	public function transformForImport($fieldData, $fillDefault = true, $checkMandatoryFieldValues = true)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		$defaultFieldValues = $this->getDefaultFieldValues();
		foreach ($fieldData as $fieldName => $fieldValue) {
			$fieldInstance = $moduleModel->getFieldByName($fieldName);
			if ($fieldInstance->getFieldDataType() === 'owner') {
				$fieldData[$fieldName] = $this->transformOwner($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'sharedOwner') {
				$fieldData[$fieldName] = $this->transformSharedOwner($fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'multipicklist') {
				$fieldData[$fieldName] = $this->transformMultipicklist($fieldInstance, $fieldValue);
			} elseif (in_array($fieldInstance->getFieldDataType(), Vtiger_Field_Model::$referenceTypes)) {
				$fieldData[$fieldName] = $this->transformReference($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'picklist') {
				$fieldData[$fieldName] = $this->transformPicklist($fieldInstance, $fieldValue);
			} else if ($fieldInstance->getFieldDataType() === 'tree') {
				$fieldData[$fieldName] = $this->transformTree($fieldInstance, $fieldValue);
			} else {
				if ($fieldInstance->getFieldDataType() === 'datetime' && !empty($fieldValue)) {
					if ($fieldValue === null || $fieldValue === '0000-00-00 00:00:00') {
						$fieldValue = '';
					}
					$valuesList = explode(' ', $fieldValue);
					if (count($valuesList) === 1)
						$fieldValue = '';
					$fieldValue = getValidDBInsertDateTimeValue($fieldValue);
					if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2} ([0-1][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/", $fieldValue) == 0) {
						$fieldValue = '';
					}
					$fieldData[$fieldName] = $fieldValue;
				}
				if ($fieldInstance->getFieldDataType() === 'date' && !empty($fieldValue)) {
					if ($fieldValue === null || $fieldValue === '0000-00-00') {
						$fieldValue = '';
					}
					$fieldValue = getValidDBInsertDateValue($fieldValue);
					if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/", $fieldValue) == 0) {
						$fieldValue = '';
					}
					$fieldData[$fieldName] = $fieldValue;
				}
				if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
					$fieldData[$fieldName] = $fieldValue = $defaultFieldValues[$fieldName];
				}
			}
		}
		if ($fillDefault) {
			foreach ($defaultFieldValues as $fieldName => $fieldValue) {
				if (!isset($fieldData[$fieldName])) {
					$fieldData[$fieldName] = $defaultFieldValues[$fieldName];
				}
			}
		}

		if ($fieldData != null && $checkMandatoryFieldValues) {
			foreach ($moduleModel->getMandatoryFieldModels() as $fieldName => $fieldInstance) {
				if (empty($fieldData[$fieldName])) {
					return null;
				}
			}
		}
		return $fieldData;
	}

	/**
	 * Function creates a record of the related module
	 * @param string $moduleName
	 * @param string $entityLabel
	 * @return int
	 */
	public function createEntityRecord($moduleName, $entityLabel)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $recordModel->getModule();
		$mandatoryFields = array_keys($moduleModel->getMandatoryFieldModels());
		$entityNameFields = $moduleModel->getNameFields();
		$save = $recordId = false;
		foreach ($entityNameFields as $entityNameField) {
			if (in_array($entityNameField, $mandatoryFields)) {
				$recordModel->set($entityNameField, $entityLabel);
				$save = true;
			}
		}
		$recordModel->set('assigned_user_id', $this->user->id);
		if ($save) {
			if (!\AppConfig::module('Import', 'SAVE_BY_HANDLERS')) {
				$recordModel->setHandlerExceptions(['disableHandlers' => true]);
			}
			$recordModel->save();
			$recordId = $recordModel->getId();
			if ($recordId) {
				\App\Record::updateLabel($moduleName, $recordModel->getId());
			}
		}
		return $recordId;
	}

	/**
	 * Get import status count
	 * @return int
	 */
	public function getImportStatusCount()
	{
		$statusCount = ['TOTAL' => 0, 'IMPORTED' => 0, 'FAILED' => 0, 'PENDING' => 0,
			'CREATED' => 0, 'SKIPPED' => 0, 'UPDATED' => 0, 'MERGED' => 0];
		$tableName = Import_Module_Model::getDbTableName($this->user);
		$query = (new \App\Db\Query())->select(['temp_status'])->from($tableName);
		$dataReader = $query->createCommand()->query();
		while (($status = $dataReader->readColumn(0)) !== false) {
			++$statusCount['TOTAL'];
			++$statusCount['IMPORTED'];
			switch ($status) {
				case self::IMPORT_RECORD_NONE:
					++$statusCount['PENDING'];
					--$statusCount['IMPORTED'];
					break;
				case self::IMPORT_RECORD_FAILED:
					++$statusCount['FAILED'];
					--$statusCount['IMPORTED'];
					break;
				case self::IMPORT_RECORD_CREATED:
					++$statusCount['CREATED'];
					break;
				case self::IMPORT_RECORD_SKIPPED:
					++$statusCount['SKIPPED'];
					break;
				case self::IMPORT_RECORD_UPDATED:
					++$statusCount['UPDATED'];
					break;
				case self::IMPORT_RECORD_MERGED:
					++$statusCount['MERGED'];
					break;
				default:
					break;
			}
		}
		return $statusCount;
	}

	public static function runScheduledImport()
	{
		$current_user = vglobal('current_user');
		$scheduledImports = self::getScheduledImport();
		foreach ($scheduledImports as $scheduledId => $importDataController) {
			$current_user = $importDataController->user;
			$importDataController->batchImport = false;

			if (!$importDataController->initializeImport()) {
				continue;
			}
			$importDataController->importData();

			$importStatusCount = $importDataController->getImportStatusCount();

			$emailSubject = 'vtiger CRM - Scheduled Import Report for ' . $importDataController->module;
			$viewer = new Vtiger_Viewer();
			$viewer->assign('FOR_MODULE', $importDataController->module);
			$viewer->assign('INVENTORY_MODULES', getInventoryModules());
			$viewer->assign('IMPORT_RESULT', $importStatusCount);
			$importResult = $viewer->view('Import_Result_Details.tpl', 'Import', true);
			$importResult = str_replace('align="center"', '', $importResult);
			$emailData = 'vtiger CRM has just completed your import process. <br/><br/>' .
				$importResult . '<br/><br/>' .
				'We recommend you to login to the CRM and check few records to confirm that the import has been successful.';

			$userName = \vtlib\Deprecated::getFullNameFromArray('Users', $importDataController->user->column_fields);
			$userEmail = $importDataController->user->email1;
			\App\Mailer::addMail([
				//'smtp_id' => 1,
				'to' => [$userEmail => $userName],
				'subject' => $emailSubject,
				'content' => $emailData,
			]);

			$importDataController->finishImport();
		}
	}

	public static function getScheduledImport()
	{

		$scheduledImports = array();
		$importQueue = Import_Queue_Action::getAll(Import_Queue_Action::$IMPORT_STATUS_SCHEDULED);
		foreach ($importQueue as $importId => $importInfo) {
			$userId = $importInfo['user_id'];
			$user = new Users();
			$user->id = $userId;
			$user->retrieve_entity_info($userId, 'Users');

			$scheduledImports[$importId] = new Import_Data_Action($importInfo, $user);
		}
		return $scheduledImports;
	}

	/**
	 *  Function to get Record details of import
	 *  @parms User Record Model $user Current Users
	 * 	@parms string $forModule Imported module
	 *  @returns array Import Records with the list of skipped records and failed records
	 */
	public static function getImportDetails($user, $forModule)
	{
		$db = App\Db::getInstance();
		$importRecords = [];
		$tableName = Import_Module_Model::getDbTableName($user);
		$query = new \App\Db\Query();
		$query->from($tableName)->where(['temp_status' => [self::IMPORT_RECORD_SKIPPED, self::IMPORT_RECORD_FAILED]]);
		$dataReader = $query->createCommand()->query();
		if ($dataReader->count()) {
			$moduleModel = Vtiger_Module_Model::getInstance($forModule);
			$columnNames = $db->getTableSchema($tableName)->getColumnNames();
			foreach ($columnNames as $key => $fieldName) {
				if ($key > 2) {
					$importRecords['headers'][$fieldName] = $moduleModel->getField($fieldName)->getFieldLabel();
				}
			}
			while ($row = $dataReader->read()) {
				$record = new Vtiger_Base_Model();
				foreach ($importRecords['headers'] as $columnName => $header) {
					$record->set($columnName, $row[$columnName]);
				}
				if ($row['temp_status'] === self::IMPORT_RECORD_SKIPPED) {
					$importRecords['skipped'][] = $record;
				} else {
					$importRecords['failed'][] = $record;
				}
			}
		}
		return $importRecords;
	}

	/**
	 * Get import record status
	 * @param string $value
	 * @return int
	 */
	public function getImportRecordStatus($value)
	{
		$temp_status = '';
		switch ($value) {
			case 'created': $temp_status = self::IMPORT_RECORD_CREATED;
				break;
			case 'skipped': $temp_status = self::IMPORT_RECORD_SKIPPED;
				break;
			case 'updated': $temp_status = self::IMPORT_RECORD_UPDATED;
				break;
			case 'merged' : $temp_status = self::IMPORT_RECORD_MERGED;
				break;
			case 'failed' : $temp_status = self::IMPORT_RECORD_FAILED;
				break;
			case 'none' : $temp_status = self::IMPORT_RECORD_NONE;
				break;
		}
		return $temp_status;
	}

	/**
	 * Create rekord
	 * @param string $moduleName
	 * @param array $fieldData
	 * @return null|array
	 */
	public function createRecordByModel($moduleName, $fieldData)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		if (isset($fieldData['inventoryData'])) {
			$inventoryData = $fieldData['inventoryData'];
			unset($fieldData['inventoryData']);
		}
		if ($inventoryData) {
			$recordModel->setInventoryRawData($this->convertInventoryDataToObject($inventoryData));
		}
		foreach ($fieldData as $fieldName => &$value) {
			$recordModel->set($fieldName, $value);
		}
		$recordModel->save();
		$ID = $recordModel->getId();
		if (!empty($ID)) {
			return ['id' => $ID, 'status' => self::IMPORT_RECORD_CREATED];
		}
		return null;
	}

	/**
	 * Update rekord
	 * @param int $rekord
	 * @param array $fieldData
	 * @param string $moduleName
	 */
	public function updateRecordByModel($rekord, $fieldData, $moduleName = false)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($rekord, $moduleName);
		if (isset($fieldData['inventoryData'])) {
			$inventoryData = $fieldData['inventoryData'];
			unset($fieldData['inventoryData']);
		}
		if ($inventoryData) {
			$recordModel->setInventoryRawData($this->convertInventoryDataToObject($inventoryData));
		}
		foreach ($fieldData as $fieldName => &$value) {
			$recordModel->set($fieldName, $value);
		}
		$recordModel->save();
	}

	/**
	 * Function creates advanced block data object
	 * @param array $inventoryData
	 * @return \Vtiger_Base_Model
	 */
	public function convertInventoryDataToObject($inventoryData = [])
	{
		$inventoryModel = new Vtiger_Base_Model();
		$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->module);
		$jsonFields = $inventoryFieldModel->getJsonFields();
		foreach ($inventoryData as $index => $data) {
			$i = $index + 1;
			$inventoryModel->set('inventoryItemsNo', $i);
			foreach ($data as $name => $value) {
				if (in_array($name, $jsonFields)) {
					$value = \App\Json::decode($value);
				}
				$inventoryModel->set($name . $i, $value);
			}
		}
		return $inventoryModel;
	}
}
