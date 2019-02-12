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

class Import_Data_Action extends \App\Controller\Action
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
	 * Constructor.
	 *
	 * @param array     $importInfo
	 * @param \App\User $user
	 */
	public function __construct($importInfo, \App\User $user)
	{
		$this->id = $importInfo['id'];
		$this->module = $importInfo['module'];
		$this->fieldMapping = $importInfo['field_mapping'];
		$this->mergeType = $importInfo['merge_type'];
		$this->mergeFields = $importInfo['merge_fields'];
		$this->defaultValues = $importInfo['default_values'];
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
	}

	/**
	 * Get default field values.
	 *
	 * @return array
	 */
	public function getDefaultFieldValues()
	{
		$key = $this->module . '_' . $this->user->getId();
		if (\App\Cache::staticHas('DefaultFieldValues', $key)) {
			return \App\Cache::staticGet('DefaultFieldValues', $key);
		}

		$defaultValue = [];
		if (!empty($this->defaultValues)) {
			if (!is_array($this->defaultValues)) {
				$this->defaultValues = \App\Json::decode($this->defaultValues);
			}
			if ($this->defaultValues) {
				$defaultValue = $this->defaultValues;
			}
		}
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		foreach ($moduleModel->getMandatoryFieldModels() as $fieldInstance) {
			$mandatoryFieldName = $fieldInstance->getName();
			if (empty($defaultValue[$mandatoryFieldName])) {
				if ($fieldInstance->getFieldDataType() === 'owner') {
					$defaultValue[$mandatoryFieldName] = $this->user->getId();
				} elseif (!in_array($fieldInstance->getFieldDataType(), ['datetime', 'date', 'time', 'reference'])) {
					$defaultValue[$mandatoryFieldName] = '????';
				}
			}
		}
		foreach ($moduleModel->getFields() as $fieldName => $fieldInstance) {
			$fieldDefaultValue = $fieldInstance->getDefaultFieldValue();
			if (empty($defaultValue[$fieldName])) {
				if ($fieldInstance->getUIType() === 52) {
					$defaultValue[$fieldName] = $this->user->getId();
				} elseif (!empty($fieldDefaultValue)) {
					$defaultValue[$fieldName] = $fieldDefaultValue;
				}
			}
		}
		\App\Cache::staticSave('DefaultFieldValues', $key, $defaultValue);

		return $defaultValue;
	}

	/**
	 * Get default mandatory field values.
	 *
	 * @return array
	 */
	public function getDefaultMandatoryFieldValues()
	{
		$key = $this->module . '_' . $this->user->getId();
		if (\App\Cache::staticHas('DefaultMandatoryFieldValues', $key)) {
			return \App\Cache::staticGet('DefaultMandatoryFieldValues', $key);
		}
		$defaultMandatoryValues = [];
		if (!empty($this->defaultValues) && !is_array($this->defaultValues)) {
			$this->defaultValues = \App\Json::decode($this->defaultValues);
		}
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		foreach ($moduleModel->getMandatoryFieldModels() as $fieldInstance) {
			$mandatoryFieldName = $fieldInstance->getName();
			$fieldDefaultValue = $fieldInstance->getDefaultFieldValue();
			if (!empty($this->defaultValues[$mandatoryFieldName])) {
				$defaultMandatoryValues[$mandatoryFieldName] = $this->defaultValues[$mandatoryFieldName];
			} elseif (!empty($fieldDefaultValue)) {
				$defaultMandatoryValues[$mandatoryFieldName] = $fieldDefaultValue;
			} elseif ($fieldInstance->getFieldDataType() === 'owner') {
				$defaultMandatoryValues[$mandatoryFieldName] = $this->user->getId();
			} elseif (!in_array($fieldInstance->getFieldDataType(), ['datetime', 'date', 'time', 'reference'])) {
				$defaultMandatoryValues[$mandatoryFieldName] = '????';
			}
		}
		\App\Cache::staticSave('DefaultMandatoryFieldValues', $key, $defaultMandatoryValues);
		return $defaultMandatoryValues;
	}

	public function import()
	{
		if (!$this->initializeImport()) {
			return false;
		}
		$this->importData();
		$this->finishImport();
	}

	/**
	 * Import data.
	 */
	public function importData()
	{
		$this->importRecords();
		$this->updateModuleSequenceNumber();
	}

	public function initializeImport()
	{
		$lockInfo = Import_Lock_Action::isLockedForModule($this->module);
		if ($lockInfo) {
			if ($lockInfo['userid'] != $this->user->getId()) {
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
		\App\Fields\RecordNumber::getInstance($this->module)->updateRecords();
	}

	/**
	 * Update import status.
	 *
	 * @param int   $entryId
	 * @param array $entityInfo
	 */
	public function updateImportStatus($entryId, $entityInfo)
	{
		$tableName = Import_Module_Model::getDbTableName($this->user);
		$entityId = $entityInfo['id'] ?? null;
		\App\Db::getInstance()->createCommand()->update($tableName, ['temp_status' => $entityInfo['status'], 'recordid' => $entityId], ['id' => $entryId])->execute();
	}

	/**
	 * Import records.
	 *
	 * @return bool
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

		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rowId = $row['id'];

			if ($isInventory) {
				$inventoryFieldData = (new \App\Db\Query())->from($inventoryTableName)->where(['id' => $rowId])->all();
			}

			$entityInfo = null;
			$fieldData = [];
			foreach ($this->fieldMapping as $fieldName => $index) {
				$fieldData[$fieldName] = \App\Purifier::decodeHtml($row[$fieldName]);
			}

			$mergeTypeValue = $this->mergeType;
			$createRecord = false;

			if (!empty($mergeTypeValue) && $mergeTypeValue !== Import_Module_Model::AUTO_MERGE_NONE) {
				$queryGenerator = new App\QueryGenerator($moduleName, $this->user->getId());
				$queryGenerator->setFields(['id']);
				$moduleFields = $queryGenerator->getModuleFields();
				foreach ($this->mergeFields as $index => $mergeField) {
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
					switch ($mergeTypeValue) {
						case Import_Module_Model::AUTO_MERGE_IGNORE:
							$entityInfo['status'] = self::IMPORT_RECORD_SKIPPED;
							break;
						case Import_Module_Model::AUTO_MERGE_OVERWRITE:
							$recordModel = Vtiger_Record_Model::getInstanceById($baseRecordId);
							$defaultMandatoryFieldValues = $this->getDefaultMandatoryFieldValues();
							$mandatoryFieldNames = array_keys($defaultMandatoryFieldValues);
							$forUnset = [];
							foreach ($fieldData as $fieldName => &$fieldValue) {
								$currentValue = $recordModel->get($fieldName);
								if (in_array($fieldName, $mandatoryFieldNames)) {
									if ($fieldValue === '' && $currentValue !== '' && $currentValue !== null) {
										$forUnset[] = $fieldName;
									} elseif ($fieldValue === '' && ($currentValue === '' || $currentValue === null) && isset($defaultMandatoryFieldValues[$fieldName]) && $defaultMandatoryFieldValues[$fieldName] !== '') {
										$fieldValue = $defaultMandatoryFieldValues[$fieldName];
									}
								}
							}
							foreach ($forUnset as $unsetName) {
								unset($fieldData[$unsetName]);
							}
							$fieldData = $this->transformForImport($fieldData);
							$this->updateRecordByModel($baseRecordId, $fieldData, $moduleName);
							$entityInfo['status'] = self::IMPORT_RECORD_UPDATED;
							break;
						case Import_Module_Model::AUTO_MERGE_MERGEFIELDS:
							// fill out empty values with defaults for all fields
							// only if actual record field is empty and file field is empty too
							$recordModel = Vtiger_Record_Model::getInstanceById($baseRecordId);
							$defaultFieldValues = $this->getDefaultFieldValues();
							foreach ($fieldData as $fieldName => &$fieldValue) {
								$currentValue = $recordModel->get($fieldName);
								if ($fieldValue === '' && ($currentValue === '' || $currentValue === null) && isset($defaultFieldValues[$fieldName]) && $defaultFieldValues[$fieldName] !== '') {
									$fieldValue = $defaultFieldValues[$fieldName];
								}
							}
							// remove empty values - do not modify existing
							$fieldData = array_filter($fieldData, function ($fieldValue) {
								return $fieldValue !== '';
							});
							$fieldData = $this->transformForImport($fieldData);
							$this->updateRecordByModel($baseRecordId, $fieldData, $moduleName);
							$entityInfo['status'] = self::IMPORT_RECORD_MERGED;
							break;
						case Import_Module_Model::AUTO_MERGE_EXISTINGISPRIORITY:
							// fill out empty values with defaults for all fields
							// only if actual record field is empty and file field is empty too
							$recordModel = Vtiger_Record_Model::getInstanceById($baseRecordId);
							$defaultFieldValues = $this->getDefaultFieldValues();
							foreach ($fieldData as $fieldName => &$fieldValue) {
								$currentValue = $recordModel->get($fieldName);
								if ($currentValue !== null && $currentValue !== '') {
									// existing record data is priority - do not override - save only empty record fields
									$fieldValue = '';
								} elseif ($fieldValue === '' && isset($defaultFieldValues[$fieldName]) && $defaultFieldValues[$fieldName] !== '') {
									$fieldValue = $defaultFieldValues[$fieldName];
								}
							}
							// remove empty values - do not modify existing
							$fieldData = array_filter($fieldData, function ($fieldValue) {
								return $fieldValue !== '';
							});
							$fieldData = $this->transformForImport($fieldData);
							$this->updateRecordByModel($baseRecordId, $fieldData, $moduleName);
							$entityInfo['status'] = self::IMPORT_RECORD_MERGED;
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
				}
			}
			if ($entityInfo === null) {
				$entityInfo = ['id' => null, 'status' => self::IMPORT_RECORD_FAILED];
			}
			$this->importedRecordInfo[$rowId] = $entityInfo;
			$this->updateImportStatus($rowId, $entityInfo);
		}
		$dataReader->close();

		return true;
	}

	/**
	 * Transform inventory for import.
	 *
	 * @param $inventoryData
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return mixed
	 */
	public function transformInventoryForImport($inventoryData)
	{
		$inventoryModel = Vtiger_Inventory_Model::getInstance($this->module);
		$inventoryFields = $inventoryModel->getFields();
		$maps = $inventoryModel->getAutoCompleteFields();

		foreach ($inventoryData as &$data) {
			$this->currentInventoryRawData = $data;
			unset($data['id']);
			foreach ($data as $fieldName => &$value) {
				if (isset($inventoryFields[$fieldName])) {
					$fieldInstance = $inventoryFields[$fieldName];
					if (in_array($fieldInstance->getType(), ['Name', 'Reference'])) {
						$value = $this->transformInventoryReference($value);
					} elseif ($fieldInstance->getType() == 'Currency') {
						$value = \App\Fields\Currency::getCurrencyIdByName($value);
						$currencyParam = $data['currencyparam'];
						$currencyParam = $fieldInstance->getCurrencyParam([], $currencyParam);
						$newCurrencyParam = [];
						foreach ($currencyParam as $key => $currencyData) {
							$valueData = \App\Fields\Currency::getCurrencyIdByName($key);
							if ($valueData) {
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

	/**
	 * Transform inventory field from map.
	 *
	 * @param $value
	 * @param $mapData
	 *
	 * @return false|int|string
	 */
	public function transformInventoryFieldFromMap($value, $mapData)
	{
		if (!empty($value)) {
			if ($this->currentInventoryRawData['name']) {
				list($entityName) = $this->transformInventoryReference($this->currentInventoryRawData['name'], true);
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
					if ($fieldObject->getFieldDataType() === 'picklist') {
						$picklist = $fieldObject->getValuesName();
						if (in_array($value, $picklist)) {
							$value = array_search($value, $picklist);
						} elseif (!array_key_exists($value, $picklist)) {
							$value = '';
						}
					}
				} else {
					$value = '';
				}
			}
		}
		return $value;
	}

	/**
	 * Function transforms value for reference type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return mixed
	 */
	public function transformInventoryReference($value, $getArray = false)
	{
		$value = trim($value);
		if (!empty($value)) {
			if (strpos($value, '::::') > 0) {
				$fieldValueDetails = explode('::::', $value);
			} elseif (strpos($value, ':::') > 0) {
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
	 * Function transforms value for owner type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return int
	 */
	public function transformOwner($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
		$ownerId = \App\User::getUserIdByName(trim($fieldValue));
		if (empty($ownerId)) {
			$ownerId = \App\Fields\Owner::getGroupId($fieldValue);
		}
		if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
			$ownerId = $defaultFieldValues[$fieldName];
		}
		if (!empty($ownerId) && \App\Fields\Owner::getType($ownerId) === 'Users' && !array_key_exists($ownerId, \App\Fields\Owner::getInstance($fieldInstance->getModuleName(), $this->user->getId())->getAccessibleUsers('', 'owner'))) {
			$ownerId = '';
		}
		if (empty($ownerId)) {
			$ownerId = $this->user->getId();
		}
		return $ownerId;
	}

	/**
	 * Function transforms value for sharedOwner type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return array
	 */
	public function transformSharedOwner($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
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
	 * Function transforms value for multipicklist type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return string
	 */
	public function transformMultipicklist($fieldInstance, $fieldValue)
	{
		$trimmedValue = trim($fieldValue);
		if ($trimmedValue === '') {
			return $trimmedValue;
		}
		$explodedValue = explode(' |##| ', $trimmedValue);
		foreach ($explodedValue as $key => $value) {
			$explodedValue[$key] = trim($value);
		}
		return implode(' |##| ', $explodedValue);
	}

	/**
	 * Function transforms value for reference type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return bool|int
	 */
	public function transformReference($fieldInstance, $fieldValue)
	{
		$defaultFieldValues = $this->getDefaultFieldValues();
		$fieldName = $fieldInstance->getFieldName();
		$entityId = false;
		if ($fieldValue === '') {
			return $fieldValue;
		}
		if (strpos($fieldValue, '::::') !== false) {
			$fieldValueDetails = explode('::::', $fieldValue);
		} elseif (strpos($fieldValue, ':::') !== false) {
			$fieldValueDetails = explode(':::', $fieldValue);
		}
		if ($fieldValueDetails && count($fieldValueDetails) > 1) {
			$referenceModuleName = trim($fieldValueDetails[0]);
			$entityLabel = trim($fieldValueDetails[1]);
			if (\App\Module::isModuleActive($referenceModuleName)) {
				$entityId = \App\Record::getCrmIdByLabel($referenceModuleName, App\Purifier::decodeHtml($entityLabel));
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
					if (empty($referenceEntityId) || !array_key_exists($referenceEntityId, \App\Fields\Owner::getInstance($fieldInstance->getModuleName(), $this->user->getId())->getAccessibleUsers('', 'owner'))) {
						$referenceEntityId = $this->user->getId();
					}
				} elseif ($referenceModule === 'Currency') {
					$referenceEntityId = \App\Fields\Currency::getCurrencyIdByName($entityLabel);
				} else {
					$referenceEntityId = \App\Record::getCrmIdByLabel($referenceModule, App\Purifier::decodeHtml($entityLabel));
				}
				if ($referenceEntityId) {
					$entityId = $referenceEntityId;
					break;
				}
			}
		}
		if (\AppConfig::module('Import', 'CREATE_REFERENCE_RECORD') && empty($entityId) && !empty($referenceModuleName) && \App\Privilege::isPermitted($referenceModuleName, 'CreateView')) {
			try {
				$entityId = $this->createEntityRecord($referenceModuleName, $entityLabel);
			} catch (Exception $e) {
				$entityId = false;
			}
		}
		return $entityId;
	}

	/**
	 * Function transforms value for picklist type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return string
	 */
	public function transformPicklist($fieldInstance, $fieldValue)
	{
		$defaultCharset = \AppConfig::main('default_charset', 'UTF-8');
		$fieldName = $fieldInstance->getFieldName();
		$fieldValue = trim($fieldValue);
		if ($fieldValue === '') {
			return $fieldValue;
		}
		if (!isset($this->allPicklistValues[$fieldName])) {
			$this->allPicklistValues[$fieldName] = array_keys($fieldInstance->getPicklistValues());
		}
		$picklistValues = $this->allPicklistValues[$fieldName];
		$picklistValueInLowerCase = strtolower(htmlentities($fieldValue, ENT_QUOTES, $defaultCharset));
		$allPicklistValuesInLowerCase = array_map('strtolower', $picklistValues);
		$picklistDetails = array_combine($allPicklistValuesInLowerCase, $picklistValues);
		if (!in_array($picklistValueInLowerCase, $allPicklistValuesInLowerCase)) {
			if (\AppConfig::module('Import', 'ADD_PICKLIST_VALUE')) {
				$fieldObject = \vtlib\Field::getInstance($fieldName, Vtiger_Module_Model::getInstance($this->module));
				$fieldObject->setPicklistValues([$fieldValue]);
				unset($this->allPicklistValues[$fieldName]);
				\App\Cache::delete('getValuesName', $fieldName);
				\App\Cache::delete('getPickListFieldValuesRows', $fieldName);
			}
		} else {
			$fieldValue = $picklistDetails[$picklistValueInLowerCase];
		}
		return $fieldValue;
	}

	/**
	 * Function transforms value for tree type field.
	 *
	 * @param \Vtiger_Field_Model $fieldInstance
	 * @param string              $fieldValue
	 *
	 * @return string
	 */
	public function transformTree($fieldInstance, $fieldValue)
	{
		if ($fieldValue === '') {
			return $fieldValue;
		}
		$values = explode(' |##| ', trim($fieldValue));
		$fieldValue = '';
		$trees = \App\Fields\Tree::getValuesById((int) $fieldInstance->getFieldParams());
		foreach ($trees as $tree) {
			foreach ($values as $value) {
				if ($tree['name'] === $value) {
					$fieldValue .= $tree['tree'] . ',';
					break;
				}
			}
		}
		if ($fieldInstance->getFieldDataType() === 'tree') {
			$fieldValue = trim($fieldValue, ',');
		} else {
			if ($fieldValue) {
				$fieldValue = ',' . $fieldValue;
			}
		}
		return $fieldValue;
	}

	/**
	 * Function transforms value for datetime type field.
	 *
	 * @param string $fieldValue
	 *
	 * @return string
	 */
	public function transformDatetime(string $fieldValue): string
	{
		if ($fieldValue === null || $fieldValue === '0000-00-00 00:00:00') {
			$fieldValue = '';
		}
		$valuesList = explode(' ', $fieldValue);
		if (count($valuesList) === 1) {
			$fieldValue = '';
		}
		if (preg_match('/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2} ([0-1][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $fieldValue) == 0) {
			$fieldValue = '';
		}
		return $fieldValue;
	}

	/**
	 * Function transforms value for date type field.
	 *
	 * @param string $fieldValue
	 *
	 * @return string
	 */
	public function transformDate(string $fieldValue): string
	{
		if ($fieldValue === null || $fieldValue === '0000-00-00') {
			$fieldValue = '';
		}
		if (preg_match('/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/', $fieldValue) == 0) {
			$fieldValue = '';
		}
		return $fieldValue;
	}

	/**
	 * Function parses data to import.
	 *
	 * @param array $fieldData
	 *
	 * @return array
	 */
	public function transformForImport($fieldData)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		foreach ($fieldData as $fieldName => $fieldValue) {
			$fieldInstance = $moduleModel->getFieldByName($fieldName);
			if ($fieldInstance->getFieldDataType() === 'owner') {
				$fieldData[$fieldName] = $this->transformOwner($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'sharedOwner') {
				$fieldData[$fieldName] = $this->transformSharedOwner($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'multipicklist') {
				$fieldData[$fieldName] = $this->transformMultipicklist($fieldInstance, $fieldValue);
			} elseif (in_array($fieldInstance->getFieldDataType(), Vtiger_Field_Model::$referenceTypes)) {
				$fieldData[$fieldName] = $this->transformReference($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'picklist') {
				$fieldData[$fieldName] = $this->transformPicklist($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'tree' || $fieldInstance->getFieldDataType() === 'categoryMultipicklist') {
				$fieldData[$fieldName] = $this->transformTree($fieldInstance, $fieldValue);
			} elseif ($fieldInstance->getModuleName() === 'Calendar' && in_array($fieldInstance->getFieldName(), ['date_start', 'due_date'])) {
				$fieldData[$fieldName] = $this->transformDate($fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'datetime' && $fieldValue !== '') {
				$fieldData[$fieldName] = $this->transformDatetime($fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'date' && $fieldValue !== '') {
				$fieldData[$fieldName] = $this->transformDate($fieldValue);
			} elseif ($fieldInstance->getFieldDataType() === 'country' && $fieldValue !== '') {
				$fieldData[$fieldName] = \App\Fields\Country::findCountryName($fieldValue);
			}
		}
		return $fieldData;
	}

	/**
	 * Function creates a record of the related module.
	 *
	 * @param string $moduleName
	 * @param string $entityLabel
	 *
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
		$recordModel->set('assigned_user_id', $this->user->getId());
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
	 * Get import status count.
	 *
	 * @return int
	 */
	public function getImportStatusCount()
	{
		$statusCount = ['TOTAL' => 0, 'IMPORTED' => 0, 'FAILED' => 0, 'PENDING' => 0,
			'CREATED' => 0, 'SKIPPED' => 0, 'UPDATED' => 0, 'MERGED' => 0, ];
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
		$dataReader->close();

		return $statusCount;
	}

	/**
	 * Function run scheduled import and send email.
	 */
	public static function runScheduledImport()
	{
		$scheduledImports = self::getScheduledImport();
		foreach ($scheduledImports as $importDataController) {
			$importDataController->batchImport = false;
			if (!$importDataController->initializeImport()) {
				continue;
			}
			App\User::setCurrentUserId($importDataController->user->getId());
			$importDataController->importData();
			$importStatusCount = $importDataController->getImportStatusCount();
			\App\Mailer::sendFromTemplate([
				'to' => [$importDataController->user->getDetail('email1') => $importDataController->user->getName()],
				'template' => 'ImportCron',
				'imported' => $importStatusCount['IMPORTED'],
				'total' => $importStatusCount['TOTAL'],
				'created' => $importStatusCount['CREATED'],
				'updated' => $importStatusCount['UPDATED'],
				'merged' => $importStatusCount['MERGED'],
				'skipped' => $importStatusCount['SKIPPED'],
				'failed' => $importStatusCount['FAILED'],
				'module' => App\Language::translate($importDataController->module, $importDataController->module)
			]);
			$importDataController->finishImport();
		}
	}

	public static function getScheduledImport()
	{
		$scheduledImports = [];
		$importQueue = Import_Queue_Action::getAll(Import_Queue_Action::$IMPORT_STATUS_SCHEDULED);
		foreach ($importQueue as $importId => $importInfo) {
			$scheduledImports[$importId] = new self($importInfo, \App\User::getUserModel($importInfo['user_id']));
		}
		return $scheduledImports;
	}

	/**
	 *  Function to get Record details of import.
	 *
	 * @parms \App\User $user Current Users
	 * @parms string $forModule Imported module
	 * @returns array Import Records with the list of skipped records and failed records
	 */
	public static function getImportDetails(\App\User $user, $forModule)
	{
		$db = App\Db::getInstance();
		$importRecords = [];
		$tableName = Import_Module_Model::getDbTableName($user);
		$query = new \App\Db\Query();
		$query->from($tableName)->where(['temp_status' => [self::IMPORT_RECORD_SKIPPED, self::IMPORT_RECORD_FAILED]]);
		$dataReader = $query->createCommand()->query();
		if ($dataReader->count()) {
			$moduleModel = Vtiger_Module_Model::getInstance($forModule);
			$columnNames = $db->getTableSchema($tableName, true)->getColumnNames();
			foreach ($columnNames as $key => $fieldName) {
				if ($key > 2) {
					$importRecords['headers'][$fieldName] = $moduleModel->getField($fieldName)->getFieldLabel();
				}
			}
			while ($row = $dataReader->read()) {
				$record = \Vtiger_Record_Model::getCleanInstance($forModule);
				foreach ($importRecords['headers'] as $columnName => $header) {
					$record->set($columnName, $row[$columnName]);
				}
				if ((int) $row['temp_status'] === self::IMPORT_RECORD_SKIPPED) {
					$importRecords['skipped'][] = $record;
				} else {
					$importRecords['failed'][] = $record;
				}
			}
			$dataReader->close();
		}
		return $importRecords;
	}

	/**
	 * Get import record status.
	 *
	 * @param string $value
	 *
	 * @return int
	 */
	public function getImportRecordStatus($value)
	{
		$temp_status = '';
		switch ($value) {
			case 'created':
				$temp_status = self::IMPORT_RECORD_CREATED;
				break;
			case 'skipped':
				$temp_status = self::IMPORT_RECORD_SKIPPED;
				break;
			case 'updated':
				$temp_status = self::IMPORT_RECORD_UPDATED;
				break;
			case 'merged':
				$temp_status = self::IMPORT_RECORD_MERGED;
				break;
			case 'failed':
				$temp_status = self::IMPORT_RECORD_FAILED;
				break;
			case 'none':
				$temp_status = self::IMPORT_RECORD_NONE;
				break;
			default:
				break;
		}
		return $temp_status;
	}

	/**
	 * Create rekord.
	 *
	 * @param string $moduleName
	 * @param array  $fieldData
	 *
	 * @return null|array
	 */
	public function createRecordByModel($moduleName, $fieldData)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		if (isset($fieldData['inventoryData'])) {
			$inventoryData = $fieldData['inventoryData'];
			unset($fieldData['inventoryData']);
		}
		if (!empty($inventoryData)) {
			$recordModel->initInventoryData($inventoryData, false);
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
	 * Update rekord.
	 *
	 * @param int    $rekord
	 * @param array  $fieldData
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
			$recordModel->initInventoryData($inventoryData, false);
		}
		foreach ($fieldData as $fieldName => &$value) {
			$recordModel->set($fieldName, $value);
		}
		$recordModel->save();
	}
}
