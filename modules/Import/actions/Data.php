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

require_once 'include/Webservices/Create.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Delete.php';
require_once 'include/Webservices/Revise.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/DataTransform.php';
require_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/QueryGenerator/QueryGenerator.php';
require_once 'include/events/include.inc';

class Import_Data_Action extends Vtiger_Action_Controller
{

	var $id;
	var $user;
	var $module;
	var $type;
	var $fieldMapping;
	var $mergeType;
	var $mergeFields;
	var $defaultValues;
	var $importedRecordInfo = [];
	protected $allPicklistValues = [];
	protected $inventoryFieldMapData = [];
	var $batchImport = true;
	public $entitydata = [];
	static $IMPORT_RECORD_NONE = 0;
	static $IMPORT_RECORD_CREATED = 1;
	static $IMPORT_RECORD_SKIPPED = 2;
	static $IMPORT_RECORD_UPDATED = 3;
	static $IMPORT_RECORD_MERGED = 4;
	static $IMPORT_RECORD_FAILED = 5;

	public function __construct($importInfo, $user)
	{
		$this->id = $importInfo['id'];
		$this->module = $importInfo['module'];
		$this->fieldMapping = $importInfo['field_mapping'];
		$this->mergeType = $importInfo['merge_type'];
		$this->mergeFields = $importInfo['merge_fields'];
		$this->defaultValues = $importInfo['default_values'];
		$this->type = $importInfo['type'];
		$this->user = $user;
	}

	public function process(Vtiger_Request $request)
	{
		return;
	}

	public function getDefaultFieldValues($moduleMeta)
	{
		static $cachedDefaultValues = array();

		if (isset($cachedDefaultValues[$this->module])) {
			return $cachedDefaultValues[$this->module];
		}

		$defaultValues = array();
		if (!empty($this->defaultValues)) {
			if (!is_array($this->defaultValues)) {
				$this->defaultValues = \includes\utils\Json::decode($this->defaultValues);
			}
			if ($this->defaultValues != null) {
				$defaultValues = $this->defaultValues;
			}
		}
		$moduleFields = $moduleMeta->getModuleFields();
		$moduleMandatoryFields = $moduleMeta->getMandatoryFields();
		foreach ($moduleMandatoryFields as $mandatoryFieldName) {
			if (empty($defaultValues[$mandatoryFieldName])) {
				$fieldInstance = $moduleFields[$mandatoryFieldName];
				if ($fieldInstance->getFieldDataType() == 'owner') {
					$defaultValues[$mandatoryFieldName] = $this->user->id;
				} elseif ($fieldInstance->getFieldDataType() != 'datetime' && $fieldInstance->getFieldDataType() != 'date' && $fieldInstance->getFieldDataType() != 'time' && $fieldInstance->getFieldDataType() != 'reference') {
					$defaultValues[$mandatoryFieldName] = '????';
				}
			}
		}
		foreach ($moduleFields as $fieldName => $fieldInstance) {
			$fieldDefaultValue = $fieldInstance->getDefault();
			if (empty($defaultValues[$fieldName])) {
				if ($fieldInstance->getUIType() == '52') {
					$defaultValues[$fieldName] = $this->user->id;
				} elseif (!empty($fieldDefaultValue)) {
					$defaultValues[$fieldName] = $fieldDefaultValue;
				}
			}
		}
		$className = get_class($moduleMeta);
		if ($className != 'VtigerLineItemMeta') {
			$cachedDefaultValues[$this->module] = $defaultValues;
		}
		return $defaultValues;
	}

	public function import()
	{
		if (!$this->initializeImport())
			return false;
		$this->importData();
		$this->finishImport();
	}

	public function importData()
	{
		$focus = CRMEntity::getInstance($this->module);
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		// pre fetch the fields and premmisions of module
		Vtiger_Field_Model::getAllForModule($moduleModel);
		if ($this->user->is_admin == 'off') {
			Vtiger_Field_Model::preFetchModuleFieldPermission($moduleModel->getId());
		}
		if (method_exists($focus, 'createRecords')) {
			$focus->createRecords($this);
		} else {
			$this->createRecords();
		}
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

	public function updateImportStatus($entryId, $entityInfo)
	{
		$adb = PearDatabase::getInstance();
		$recordId = null;
		if (!empty($entityInfo['id'])) {
			$entityIdComponents = vtws_getIdComponents($entityInfo['id']);
			$recordId = $entityIdComponents[1];
		}
		$adb->pquery('UPDATE ' . Import_Utils_Helper::getDbTableName($this->user) . ' SET temp_status=?, recordid=? WHERE id=?', array($entityInfo['status'], $recordId, $entryId));
	}

	public function createRecords()
	{
		$adb = PearDatabase::getInstance();
		$moduleName = $this->module;

		$focus = CRMEntity::getInstance($moduleName);
		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $this->user);
		$moduleMeta = $moduleHandler->getMeta();
		$moduleObjectId = $moduleMeta->getEntityId();
		$moduleFields = $moduleMeta->getModuleFields();

		$entityData = [];
		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$sql = 'SELECT * FROM %s  WHERE temp_status = %s';
		$sql = sprintf($sql, $tableName, Import_Data_Action::$IMPORT_RECORD_NONE);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$isInventory = $moduleModel->isInventory();
		if ($isInventory) {
			$inventoryTableName = Import_Utils_Helper::getInventoryDbTableName($this->user);
		}

		if ($this->batchImport) {
			$configReader = new Import_Config_Model();
			$importBatchLimit = $configReader->get('importBatchLimit');
			$sql .= sprintf(' LIMIT %s', $importBatchLimit);
		}
		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$fieldMapping = $this->fieldMapping;
		$fieldColumnMapping = $moduleMeta->getFieldColumnMapping();

		while ($row = $adb->fetchByAssoc($result)) {
			$handlerOn = false;
			$rowId = $row['id'];

			if ($isInventory) {
				$sql = sprintf('SELECT * FROM %s WHERE id = ?', $inventoryTableName);
				$resultInventory = $adb->pquery($sql, [$rowId]);
				$inventoryFieldData = $adb->getArray($resultInventory);
			}

			$entityInfo = null;
			$fieldData = array();
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldData[$fieldName] = $row[$fieldName];
			}

			$mergeType = $this->mergeType;
			$createRecord = false;

			if (method_exists($focus, 'importRecord')) {
				$entityInfo = $focus->importRecord($this, $fieldData);
			} else {
				if (!empty($mergeType) && $mergeType != Import_Utils_Helper::$AUTO_MERGE_NONE) {

					$queryGenerator = new QueryGenerator($moduleName, $this->user);
					$customView = new CustomView($moduleName);
					$viewId = $customView->getViewIdByName('All', $moduleName);
					if (!empty($viewId)) {
						$queryGenerator->initForCustomViewById($viewId);
					} else {
						$queryGenerator->initForDefaultCustomView();
					}

					$fieldsList = array('id');
					$queryGenerator->setFields($fieldsList);

					$mergeFields = $this->mergeFields;
					if ($queryGenerator->getWhereFields() && $mergeFields) {
						$queryGenerator->addConditionGlue(QueryGenerator::$AND);
					}
					foreach ($mergeFields as $index => $mergeField) {
						if ($index != 0) {
							$queryGenerator->addConditionGlue(QueryGenerator::$AND);
						}
						$comparisonValue = $fieldData[$mergeField];
						$fieldInstance = $moduleFields[$mergeField];
						if ($fieldInstance->getFieldDataType() == 'owner') {
							$userId = getUserId_Ol($comparisonValue);
							$comparisonValue = \includes\fields\Owner::getUserLabel($userId);
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
						$queryGenerator->addCondition($mergeField, $comparisonValue, 'e', '', '', '', true);
					}
					$query = $queryGenerator->getQuery();
					$duplicatesResult = $adb->query($query);
					$noOfDuplicates = $adb->num_rows($duplicatesResult);

					if ($noOfDuplicates > 0) {
						if ($mergeType == Import_Utils_Helper::$AUTO_MERGE_IGNORE) {
							$entityInfo['status'] = self::$IMPORT_RECORD_SKIPPED;
						} elseif ($mergeType == Import_Utils_Helper::$AUTO_MERGE_OVERWRITE ||
							$mergeType == Import_Utils_Helper::$AUTO_MERGE_MERGEFIELDS) {

							for ($index = 0; $index < $noOfDuplicates - 1; ++$index) {
								$duplicateRecordId = $adb->query_result($duplicatesResult, $index, $fieldColumnMapping['id']);
								$entityId = vtws_getId($moduleObjectId, $duplicateRecordId);
								vtws_delete($entityId, $this->user);
							}
							$baseRecordId = $adb->query_result($duplicatesResult, $noOfDuplicates - 1, $fieldColumnMapping['id']);
							$baseEntityId = vtws_getId($moduleObjectId, $baseRecordId);

							if ($mergeType == Import_Utils_Helper::$AUTO_MERGE_OVERWRITE) {
								$fieldData = $this->transformForImport($fieldData, $moduleMeta);
								$fieldData['id'] = $baseEntityId;
								$entityInfo = vtws_update($fieldData, $this->user);
								$entityInfo['status'] = self::$IMPORT_RECORD_UPDATED;
							}

							if ($mergeType == Import_Utils_Helper::$AUTO_MERGE_MERGEFIELDS) {
								$filteredFieldData = array();
								foreach ($fieldData as $fieldName => $fieldValue) {
									// empty will give false for value = 0
									if (!empty($fieldValue) || $fieldValue != "") {
										$filteredFieldData[$fieldName] = $fieldValue;
									}
								}

								// Custom handling for default values & mandatory fields
								// need to be taken care than normal import as we merge
								// existing record values with newer values.
								$fillDefault = false;
								$mandatoryValueChecks = false;

								$existingFieldValues = vtws_retrieve($baseEntityId, $this->user);
								$defaultFieldValues = $this->getDefaultFieldValues($moduleMeta);

								foreach ($existingFieldValues as $fieldName => $fieldValue) {
									if (empty($fieldValue) && empty($filteredFieldData[$fieldName]) && !empty($defaultFieldValues[$fieldName])) {
										$filteredFieldData[$fieldName] = $defaultFieldValues[$fieldName];
									}
								}

								$filteredFieldData = $this->transformForImport($filteredFieldData, $moduleMeta, $fillDefault, $mandatoryValueChecks);
								$filteredFieldData['id'] = $baseEntityId;
								$entityInfo = vtws_revise($filteredFieldData, $this->user);
								$entityInfo['status'] = self::$IMPORT_RECORD_MERGED;
								$fieldData = $filteredFieldData;
							}
						} else {
							$createRecord = true;
						}
					} else {
						$createRecord = true;
					}
				} else {
					$createRecord = true;
				}
				if ($createRecord) {
					$fieldData = $this->transformForImport($fieldData, $moduleMeta);
					if ($fieldData && $isInventory) {
						$inventoryFieldData = $this->transformInventoryForImport($inventoryFieldData);
						$fieldData['inventoryData'] = $inventoryFieldData;
					}
					if ($fieldData == null) {
						$entityInfo = null;
					} else {
						if ($this->type) {
							$entityInfo = $this->createRecordByModel($moduleName, $fieldData, $this->user);
							$handlerOn = true;
						} else {
							try {
								$entityInfo = vtws_create($moduleName, $fieldData, $this->user);
							} catch (Exception $e) {
								
							}
						}
					}
				}
			}
			if ($entityInfo == null) {
				$entityInfo = array('id' => null, 'status' => self::$IMPORT_RECORD_FAILED);
			} else if ($createRecord) {
				$entityInfo['status'] = self::$IMPORT_RECORD_CREATED;
			}
			if (empty($handlerOn) && ($createRecord || $mergeType == Import_Utils_Helper::$AUTO_MERGE_MERGEFIELDS || $mergeType == Import_Utils_Helper::$AUTO_MERGE_OVERWRITE)) {
				$entityIdComponents = vtws_getIdComponents($entityInfo['id']);
				$recordId = $entityIdComponents[1];
				\includes\Record::updateLabel($this->module, $recordId);
			}

			$this->importedRecordInfo[$rowId] = $entityInfo;
			$this->updateImportStatus($rowId, $entityInfo);
		}
		if (empty($handlerOn) && $this->entityData) {
			$entity = new VTEventsManager($adb);
			$entity->triggerEvent('vtiger.batchevent.save', $this->entityData);
		}
		$this->entityData = null;
		$result = null;
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
						$value = getCurrencyId($entityLabel);
						$currencyParam = $data['currencyparam'];
						$currencyParam = $fieldInstance->getCurrencyParam([], $currencyParam);
						$newCurrencyParam = [];
						foreach ($currencyParam as $key => $currencyData) {
							$valueData = getCurrencyId($entityLabel);
							if ($valueData) {
								$currencyData['value'] = $valueData;
								$newCurrencyParam[$valueData] = $currencyData;
							}
						}
						$data['currencyparam'] = \includes\utils\Json::encode($newCurrencyParam);
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
				$value = getEntityId($referenceModuleName, $entityLabel);
			}
		}
		return $getArray ? [$referenceModuleName, $value] : $value;
	}

	public function transformOwner($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues)
	{
		$ownerId = getUserId_Ol(trim($fieldValue));
		if (empty($ownerId)) {
			$ownerId = getGrpId($fieldValue);
		}
		if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
			$ownerId = $defaultFieldValues[$fieldName];
		}
		if (empty($ownerId) ||
			!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $ownerId)) {
			$ownerId = $this->user->id;
		}
		return $ownerId;
	}

	public function transformSharedOwner($fieldValue, $defaultFieldValues)
	{
		$values = [];
		$owners = explode(',', $fieldValue);
		foreach ($owners as $owner) {
			$ownerId = getUserId_Ol(trim($owner));
			if (empty($ownerId)) {
				$ownerId = getGrpId($owner);
			}
			if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
				$ownerId = $defaultFieldValues[$fieldName];
			}
			if (!empty($ownerId)) {
				$values[] = $ownerId;
			}
		}
		return implode(',', $values);
	}

	public function transformMultipicklist($fieldInstance, $fieldValue, $defaultFieldValues)
	{
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

	public function transformReference($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues)
	{
		$fieldName = $fieldInstance->getFieldName();
		$entityId = false;
		if (!empty($fieldValue)) {
			if (strpos($fieldValue, '::::') > 0) {
				$fieldValueDetails = explode('::::', $fieldValue);
			} else if (strpos($fieldValue, ':::') > 0) {
				$fieldValueDetails = explode(':::', $fieldValue);
			} else {
				$fieldValueDetails = $fieldValue;
			}
			if (count($fieldValueDetails) > 1) {
				$referenceModuleName = trim($fieldValueDetails[0]);
				$entityLabel = trim($fieldValueDetails[1]);
				if (vtlib\Functions::getModuleId($referenceModuleName)) {
					$entityId = getEntityId($referenceModuleName, $entityLabel);
				} else {
					$referencedModules = $fieldInstance->getReferenceList();
					if (isset($defaultFieldValues[$fieldName])) {
						$referenceModuleName = $defaultFieldValues[$fieldName];
						$entityId = getEntityId($referenceModuleName, $entityLabel);
					}
				}
			} else {
				$referencedModules = $fieldInstance->getReferenceList();
				$entityLabel = $fieldValue;
				foreach ($referencedModules as $referenceModule) {
					$referenceModuleName = $referenceModule;
					if ($referenceModule == 'Users') {
						$referenceEntityId = getUserId_Ol($entityLabel);
						if (empty($referenceEntityId) ||
							!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $referenceEntityId)) {
							$referenceEntityId = $this->user->id;
						}
					} elseif ($referenceModule == 'Currency') {
						$referenceEntityId = getCurrencyId($entityLabel);
					} else {
						$referenceEntityId = getEntityId($referenceModule, $entityLabel);
					}
					if ($referenceEntityId != 0) {
						$entityId = $referenceEntityId;
						break;
					}
				}

				if ($entityId == false) {
					$referenceModuleName = AppRequest::get($fieldName . '_defaultvalue');
				}
			}
			if ((empty($entityId) || $entityId == 0) && !empty($referenceModuleName)) {
				if (isPermitted($referenceModuleName, 'CreateView') == 'yes') {
					try {
						$wsEntityIdInfo = $this->createEntityRecord($referenceModuleName, $entityLabel);
						$wsEntityId = $wsEntityIdInfo['id'];
						$entityIdComponents = vtws_getIdComponents($wsEntityId);
						$entityId = $entityIdComponents[1];
					} catch (Exception $e) {
						$entityId = getEntityId($referenceModuleName, $entityLabel);
						if ($entityId == 0)
							$entityId = false;
					}
				}
			}
			$fieldValue = $entityId;
		} else {
			$referencedModules = $fieldInstance->getReferenceList();
			if ($referencedModules[0] == 'Users') {
				if (isset($defaultFieldValues[$fieldName])) {
					$fieldValue = $defaultFieldValues[$fieldName];
				}
				if (empty($fieldValue) ||
					!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $fieldValue)) {
					$fieldValue = $this->user->id;
				}
			} else {
				$fieldValue = '';
			}
		}
		return $fieldValue;
	}

	public function transformPicklist($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues)
	{
		global $default_charset;
		$fieldName = $fieldInstance->getFieldName();
		$fieldValue = trim($fieldValue);

		if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
			$fieldValue = $defaultFieldValues[$fieldName];
		}
		$olderCacheEnable = Vtiger_Cache::$cacheEnable;
		Vtiger_Cache::$cacheEnable = false;
		if (!isset($this->allPicklistValues[$fieldName])) {
			$this->allPicklistValues[$fieldName] = $fieldInstance->getPicklistDetails();
		}
		$allPicklistDetails = $this->allPicklistValues[$fieldName];

		$allPicklistValues = array();
		foreach ($allPicklistDetails as $picklistDetails) {
			$allPicklistValues[] = $picklistDetails['value'];
		}

		$picklistValueInLowerCase = strtolower(htmlentities($fieldValue, ENT_QUOTES, $default_charset));
		$allPicklistValuesInLowerCase = array_map('strtolower', $allPicklistValues);
		$picklistDetails = array_combine($allPicklistValuesInLowerCase, $allPicklistValues);

		if (!in_array($picklistValueInLowerCase, $allPicklistValuesInLowerCase)) {
			$moduleObject = vtlib\Module::getInstance($moduleMeta->getEntityName());
			$fieldObject = vtlib\Field::getInstance($fieldName, $moduleObject);
			$fieldObject->setPicklistValues(array($fieldValue));
			unset($this->allPicklistValues[$fieldName]);
		} else {
			$fieldValue = $picklistDetails[$picklistValueInLowerCase];
		}
		Vtiger_Cache::$cacheEnable = $olderCacheEnable;
		return $fieldValue;
	}

	public function transformTree($fieldInstance, $fieldValue, $defaultFieldValues)
	{
		$fieldName = $fieldInstance->getFieldName();
		if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
			$fieldValue = $defaultFieldValues[$fieldName];
		} else if (!empty($fieldValue)) {
			$fieldValue = trim($fieldValue);
			foreach ($fieldInstance->getTreeDetails() as $id => $tree) {
				if ($tree == $fieldValue) {
					$fieldValue = $id;
				}
			}
		}
		return $fieldValue;
	}

	public function transformForImport($fieldData, $moduleMeta, $fillDefault = true, $checkMandatoryFieldValues = true)
	{
		$moduleFields = $moduleMeta->getModuleFields();
		$defaultFieldValues = $this->getDefaultFieldValues($moduleMeta);
		foreach ($fieldData as $fieldName => $fieldValue) {
			$fieldInstance = $moduleFields[$fieldName];
			if ($fieldInstance->getFieldDataType() == 'owner') {
				$fieldData[$fieldName] = $this->transformOwner($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues);
			} elseif ($fieldInstance->getFieldDataType() == 'sharedOwner') {
				$fieldData[$fieldName] = $this->transformSharedOwner($fieldValue, $defaultFieldValues);
			} elseif ($fieldInstance->getFieldDataType() == 'multipicklist') {
				$fieldData[$fieldName] = $this->transformMultipicklist($fieldInstance, $fieldValue, $defaultFieldValues);
			} elseif (in_array($fieldInstance->getFieldDataType(), Vtiger_Field_Model::$REFERENCE_TYPES)) {
				$fieldData[$fieldName] = $this->transformReference($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues);
			} elseif ($fieldInstance->getFieldDataType() == 'picklist') {
				$fieldData[$fieldName] = $this->transformPicklist($moduleMeta, $fieldInstance, $fieldValue, $defaultFieldValues);
			} else if ($fieldInstance->getFieldDataType() == 'currency') {
				// While exporting we are exporting as user format, we should import as db format while importing
				$fieldData[$fieldName] = CurrencyField::convertToDBFormat($fieldValue, $current_user, false);
			} else if ($fieldInstance->getFieldDataType() == 'tree') {
				$fieldData[$fieldName] = $this->transformTree($fieldInstance, $fieldValue, $defaultFieldValues);
			} else {
				if ($fieldInstance->getFieldDataType() == 'datetime' && !empty($fieldValue)) {
					if ($fieldValue == null || $fieldValue == '0000-00-00 00:00:00') {
						$fieldValue = '';
					}
					$valuesList = explode(' ', $fieldValue);
					if (count($valuesList) == 1)
						$fieldValue = '';
					$fieldValue = getValidDBInsertDateTimeValue($fieldValue);
					if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2} ([0-1][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/", $fieldValue) == 0) {
						$fieldValue = '';
					}
					$fieldData[$fieldName] = $fieldValue;
				}
				if ($fieldInstance->getFieldDataType() == 'date' && !empty($fieldValue)) {
					if ($fieldValue == null || $fieldValue == '0000-00-00') {
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

		// We should sanitizeData before doing final mandatory check below.
		$fieldData = DataTransform::sanitizeData($fieldData, $moduleMeta);

		if ($fieldData != null && $checkMandatoryFieldValues) {
			foreach ($moduleFields as $fieldName => $fieldInstance) {
				if (empty($fieldData[$fieldName]) && $fieldInstance->isMandatory()) {
					return null;
				}
			}
		}

		return $fieldData;
	}

	public function createEntityRecord($moduleName, $entityLabel)
	{
		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $this->user);
		$moduleMeta = $moduleHandler->getMeta();
		$moduleFields = $moduleMeta->getModuleFields();
		$mandatoryFields = $moduleMeta->getMandatoryFields();
		$entityNameFieldsString = $moduleMeta->getNameFields();
		$entityNameFields = explode(',', $entityNameFieldsString);
		$fieldData = array();
		foreach ($entityNameFields as $entityNameField) {
			$entityNameField = trim($entityNameField);
			if (in_array($entityNameField, $mandatoryFields)) {
				$fieldData[$entityNameField] = $entityLabel;
			}
		}
		foreach ($mandatoryFields as $mandatoryField) {
			if (empty($fieldData[$mandatoryField])) {
				$fieldInstance = $moduleFields[$mandatoryField];
				if ($fieldInstance->getFieldDataType() == 'owner') {
					$fieldData[$mandatoryField] = $this->user->id;
				} else if (!in_array($mandatoryField, $entityNameFields) && $fieldInstance->getFieldDataType() != 'reference') {
					$fieldData[$mandatoryField] = '????';
				}
			}
		}

		$fieldData = DataTransform::sanitizeData($fieldData, $moduleMeta);
		$entityIdInfo = vtws_create($moduleName, $fieldData, $this->user);
		$adb = PearDatabase::getInstance();
		$entityIdComponents = vtws_getIdComponents($entityIdInfo['id']);
		$recordId = $entityIdComponents[1];

		\includes\Record::updateLabel($moduleName, $recordId);

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$focus = $recordModel->getEntity();
		$focus->id = $recordId;
		$focus->column_fields = $fieldData;
		$this->entityData[] = VTEntityData::fromCRMEntity($focus);
		$focus->updateMissingSeqNumber($moduleName);
		return $entityIdInfo;
	}

	public function getImportStatusCount()
	{
		$adb = PearDatabase::getInstance();

		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$query = sprintf('SELECT temp_status FROM %s', $tableName);
		$result = $adb->query($query);

		$statusCount = array('TOTAL' => 0, 'IMPORTED' => 0, 'FAILED' => 0, 'PENDING' => 0,
			'CREATED' => 0, 'SKIPPED' => 0, 'UPDATED' => 0, 'MERGED' => 0);

		if ($result) {
			$noOfRows = $adb->num_rows($result);
			$statusCount['TOTAL'] = $noOfRows;
			for ($i = 0; $i < $noOfRows; ++$i) {
				$temp_status = $adb->query_result($result, $i, 'temp_status');
				if (self::$IMPORT_RECORD_NONE == $temp_status) {
					$statusCount['PENDING'] ++;
				} elseif (self::$IMPORT_RECORD_FAILED == $temp_status) {
					$statusCount['FAILED'] ++;
				} else {
					$statusCount['IMPORTED'] ++;
					switch ($temp_status) {
						case self::$IMPORT_RECORD_CREATED : $statusCount['CREATED'] ++;
							break;
						case self::$IMPORT_RECORD_SKIPPED : $statusCount['SKIPPED'] ++;
							break;
						case self::$IMPORT_RECORD_UPDATED : $statusCount['UPDATED'] ++;
							break;
						case self::$IMPORT_RECORD_MERGED : $statusCount['MERGED'] ++;
							break;
					}
				}
			}
		}
		return $statusCount;
	}

	public static function runScheduledImport()
	{
		$current_user = vglobal('current_user');
		$scheduledImports = self::getScheduledImport();
		$vtigerMailer = new vtlib\Mailer();
		$vtigerMailer->IsHTML(true);
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
			$vtigerMailer->to = array(array($userEmail, $userName));
			$vtigerMailer->Subject = $emailSubject;
			$vtigerMailer->Body = $emailData;

			$importDataController->finishImport();
		}
		vtlib\Mailer::dispatchQueue(null);
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
	/*
	 *  Function to get Record details of import
	 *  @parms $user <User Record Model> Current Users
	 * 	@parms $user <String> Imported module
	 *  @returns <Array> Import Records with the list of skipped records and failed records
	 */

	public static function getImportDetails($user, $forModule)
	{
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($user);
		$result = $adb->pquery("SELECT * FROM $tableName where temp_status IN (?,?)", array(self::$IMPORT_RECORD_SKIPPED, self::$IMPORT_RECORD_FAILED));
		$importRecords = array();
		if ($result) {
			$moduleModel = Vtiger_Module_Model::getInstance($forModule);
			$headers = $adb->getColumnNames($tableName);
			$numOfHeaders = count($headers);
			for ($i = 0; $i < 10; $i++) {
				if ($i >= 3 && $i < $numOfHeaders) {
					$fieldModel = Vtiger_Field_Model::getInstance($headers[$i], $moduleModel);
					$importRecords['headers'][] = $fieldModel->getFieldLabel();
				}
			}
			$noOfRows = $adb->num_rows($result);
			for ($i = 0; $i < $noOfRows; ++$i) {
				$row = $adb->fetchByAssoc($result, $i);
				$record = new Vtiger_Base_Model();
				foreach ($importRecords['headers'] as $header) {
					$record->set($header, $row[$header]);
				}
				if ($row['temp_status'] == self::$IMPORT_RECORD_SKIPPED) {
					$importRecords['skipped'][] = $record;
				} else {
					$importRecords['failed'][] = $record;
				}
			}
			return $importRecords;
		}
	}

	public function getImportRecordStatus($value)
	{
		$temp_status = '';
		switch ($value) {
			case 'created': $temp_status = self::$IMPORT_RECORD_CREATED;
				break;
			case 'skipped': $temp_status = self::$IMPORT_RECORD_SKIPPED;
				break;
			case 'updated': $temp_status = self::$IMPORT_RECORD_UPDATED;
				break;
			case 'merged' : $temp_status = self::$IMPORT_RECORD_MERGED;
				break;
			case 'failed' : $temp_status = self::$IMPORT_RECORD_FAILED;
				break;
			case 'none' : $temp_status = self::$IMPORT_RECORD_NONE;
				break;
		}
		return $temp_status;
	}

	public function createRecordByModel($moduleName, $fieldData, $user)
	{
		$previousBulkSaveMode = vglobal('VTIGER_BULK_SAVE_MODE');
		vglobal('VTIGER_BULK_SAVE_MODE', false);
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		if (isset($fieldData['inventoryData'])) {
			$inventoryData = $fieldData['inventoryData'];
			unset($fieldData['inventoryData']);
		}
		$fieldData = $this->parseData($moduleName, $fieldData);
		if ($inventoryData) {
			$fieldData = $this->setInventoryDataToRequest($fieldData, $inventoryData);
		}
		foreach ($fieldData as $fieldName => $value) {
			$recordModel->set($fieldName, $value);
		}

		$recordModel->save();
		vglobal('VTIGER_BULK_SAVE_MODE', $previousBulkSaveMode);
		$ID = $recordModel->getId();
		if (!empty($ID)) {
			$adb = PearDatabase::getInstance();
			$webserviceObject = VtigerWebserviceObject::fromName($adb, $moduleName);
			$vtwsId = vtws_getId($webserviceObject->getEntityId(), $ID);
			return ['id' => $vtwsId, 'status' => isRecordExists($ID)];
		}
		return null;
	}

	public function parseData($elementType, $element)
	{
		$adb = PearDatabase::getInstance();
		$types = vtws_listtypes(null, $this->user);
		$moduleHandler = vtws_getModuleHandlerFromName($elementType, $this->user);
		$meta = $moduleHandler->getMeta();
		if ($meta->hasWriteAccess() !== true) {
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to write is denied");
		}

		$referenceFields = $meta->getReferenceFieldDetails();
		foreach ($referenceFields as $fieldName => $details) {
			if (isset($element[$fieldName]) && strlen($element[$fieldName]) > 0) {
				$ids = vtws_getIdComponents($element[$fieldName]);
				$elemTypeId = $ids[0];
				$elemId = $ids[1];
				$referenceObject = VtigerWebserviceObject::fromId($adb, $elemTypeId);
				if (!in_array($referenceObject->getEntityName(), $details)) {
					throw new WebServiceException(WebServiceErrorCode::$REFERENCEINVALID, "Invalid reference specified for $fieldName");
				}
				if ($referenceObject->getEntityName() == 'Users') {
					if (!$meta->hasAssignPrivilege($element[$fieldName])) {
						throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Cannot assign record to the given user");
					}
				}
				if (!in_array($referenceObject->getEntityName(), $types['types']) && $referenceObject->getEntityName() != 'Users') {
					throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to access reference type is denied" . $referenceObject->getEntityName());
				}
			} else if ($element[$fieldName] !== NULL) {
				unset($element[$fieldName]);
			}
		}

		if ($meta->hasMandatoryFields($element)) {
			$ownerFields = $meta->getOwnerFields();
			if (is_array($ownerFields) && sizeof($ownerFields) > 0) {
				foreach ($ownerFields as $ownerField) {
					if (isset($element[$ownerField]) && $element[$ownerField] !== null &&
						!$meta->hasAssignPrivilege($element[$ownerField])) {
						throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Cannot assign record to the given user");
					}
				}
			}
		}
		$element = DataTransform::sanitizeForInsert($element, $meta);
		$sharedOwners = $meta->getSharedOwnerFields();
		foreach ($sharedOwners as $name) {
			if ($element[$name]) {
				$element[$name] = explode(',', $element[$name]);
			}
		}
		return $element;
	}

	public function setInventoryDataToRequest($fieldData, $inventoryData = [])
	{
		$invDat = [];
		$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->module);
		$jsonFields = $inventoryFieldModel->getJsonFields();
		foreach ($inventoryData as $index => $data) {
			$i = $index + 1;
			$invDat['inventoryItemsNo'] = $i;
			foreach ($data as $name => $value) {
				if (in_array($name, $jsonFields)) {
					$value = \includes\utils\Json::decode($value);
				}
				$invDat[$name . $i] = $value;
			}
		}
		$fieldData['inventoryData'] = new Vtiger_Request($invDat);
		return $fieldData;
	}
}
