<?php

/**
 * XmlReader Class
 * @package YetiForce.Import
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Import_XmlReader_Reader extends Import_FileReader_Reader
{

	protected $moduleName;
	protected $skipField = array('assigned_user_id', 'productid');
	protected $skipRecord = 0;
	protected $importedRecords = 0;
	protected $relatedInventoryField = [];

	public function __construct($request, $user)
	{
		$this->moduleName = $request->get('module');
		parent::__construct($request, $user);
	}

	public function hasHeader()
	{
		return true;
	}

	public function getFirstRowData($hasHeader = true)
	{
		$xmlToImport = new XMLReader();
		$xmlToImport->open($this->getFilePath());
		if ($this->isTemplate()) {
			$recordData = $this->getRecordDataFromXMLTemplate($xmlToImport);
		} else {
			list($recordData, $recordsInventoryData) = $this->getRecordDataFromXML($xmlToImport);
			$recordData = ['LBL_STANDARD_FIELDS' => $recordData, 'LBL_INVENTORY_FIELDS' => $recordsInventoryData];
		}
		if (empty($recordsInventoryData)) {
			unset($recordData['LBL_INVENTORY_FIELDS']);
		}
		return $recordData;
	}

	public function getFilePath()
	{
		if ($this->filePath) {
			return $this->filePath;
		}
		return parent::getFilePath();
	}

	public function createTable()
	{
		$db = PearDatabase::getInstance();
		$return = true;
		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$result = $db->query("SHOW TABLES LIKE '$tableName'");
		if ($result->rowCount() == 0) {
			$return = parent::createTable();
		}
		return $return;
	}

	public function read()
	{
		global $default_charset;
		$temp_status = $this->createTable();
		if (!$temp_status) {
			return false;
		}

		$fieldMapping = $this->request->get('field_mapping');
		$inventoryFieldMapping = $this->request->get('inventory_field_mapping');

		$xmlToImport = new XMLReader();
		$xmlToImport->open($this->getFilePath());

		list($recordData, $recordsInventoryData) = $this->getRecordDataFromXML($xmlToImport, false);
		$columnsName = $this->inventoryData['tags'];
		$mappedData = [];
		$inventoryMappedData = [];
		$allValuesEmpty = true;
		foreach ($fieldMapping as $fieldName => $index) {
			$fieldValue = $recordData[$index];
			$mappedData[$fieldName] = $fieldValue;
			if ($this->request->get('file_encoding') != $default_charset) {
				$mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $default_charset);
			}
			if (!empty($fieldValue))
				$allValuesEmpty = false;
		}
		if ($inventoryFieldMapping && $recordsInventoryData) {
			$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryFieldModel->getFields();
			foreach ($recordsInventoryData as $index => $data) {
				foreach ($inventoryFieldMapping as $fieldName => $key) {
					$fieldValue = $data[$key];
					if (in_array($fieldName, ['qty', 'price', 'gross', 'net', 'discount', 'purchase', 'margin', 'marginp', 'tax', 'total'])) {
						$fieldValue = CurrencyField::convertToDBFormat($fieldValue, null, true);
					}
					$inventoryMappedData[$index][$fieldName] = $fieldValue;
					$fieldModel = $inventoryFields[$fieldName];
					foreach ($fieldModel->getCustomColumn() as $columnParamsName => $dataType) {
						if (in_array($columnParamsName, $columnsName)) {
							$key = array_search($columnParamsName, $columnsName);
							$inventoryMappedData[$index][$columnParamsName] = $data[$key];
						}
					}
//					if ($this->request->get('file_encoding') != $default_charset) {
//						$inventoryMappedData[$index][$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $default_charset);
//					}
				}
			}
		}
		if (!$allValuesEmpty) {
			$fieldNames = array_keys($mappedData);
			$fieldValues = array_values($mappedData);
			$this->addRecordToDB($fieldNames, $fieldValues, $inventoryMappedData);
		}
	}

	public function isTemplate()
	{
		return !empty($this->request->get('xml_import_tpl'));
	}

	public function arrayCombine($key, $value)
	{
		$combine = array();
		$dup = array();
		for ($i = 0; $i < count($key); $i++) {
			if (array_key_exists($key[$i], $combine)) {
				if (!$dup[$key[$i]])
					$dup[$key[$i]] = 1;
				$key[$i] = $key[$i] . ' (' . ++$dup[$key[$i]] . ')';
			}
			$combine[$key[$i]] = $value[$i];
		}
		return $combine;
	}

	public function getRecordDataFromXML(XMLReader $xmlToImport, $keyType = 'label')
	{
		$recordData = [];
		$recordInventoryData = [];
		$isInventory = false;
		if ($this->moduleModel->isInventory()) {
			$isInventory = true;
		}
		$fields = $this->moduleModel->getFields();
		while ($xmlToImport->read()) {
			if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
				if (!in_array($xmlToImport->localName, ['MODULE_FIELDS', 'INVENTORY_ITEM', 'INVENTORY_ITEMS'])) {
					$labels[] = $xmlToImport->getAttribute('label');
					$recordData[] = $xmlToImport->readString();
				} elseif ($isInventory && $xmlToImport->localName == 'INVENTORY_ITEMS') {
					$recordInventoryData = $this->getInventoryData($xmlToImport, $keyType);
					break;
				}
			}
		}
		if ($keyType == 'label') {
			$recordData = $this->arrayCombine($labels, $recordData);
		}
		return [$recordData, $recordInventoryData];
	}

	public function getInventoryData(XMLReader $xmlToImport, $keyType)
	{
		$recordInventoryData = [];
		$labels = [];
		$columnsName = [];
		$index = -1;
		$inc = 0;
		while ($xmlToImport->read()) {
			if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
				if ($xmlToImport->localName == 'INVENTORY_ITEM') {
					++$index;
					continue;
				}
				$label = $xmlToImport->getAttribute('label');
				if (empty($label)) {
					$label = $inc++;
				}
				$labels[$index][] = $label;
				$columnsName[$index][] = $xmlToImport->localName;
				$recordInventoryData[$index][] = $xmlToImport->readString();
			}
		}
		$this->inventoryData = ['labels' => array_combine($labels[0], $recordInventoryData[0]), 'tags' => $columnsName[0], 'data' => $recordInventoryData];
		switch ($keyType) {
			case 'label':
				$recordInventoryData = $this->inventoryData['labels'];
				break;
			default:
				$recordInventoryData = $this->inventoryData['data'];
				break;
		}
		return $recordInventoryData;
	}

	public function getRecordDataFromXMLTemplate(XMLReader $xmlToImport)
	{
		$recordData = [];
		$recordNum = 0;
		$firstElement = '';
		$lineProd = '';

		while ($xmlToImport->read()) {
			if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
				$info = $this->getFieldInfoByTagName($xmlToImport->localName);
				if (0 == $recordNum) {
					$firstElement = $xmlToImport->localName;
					$recordNum++;
				}
				if ($info && 'product' != $info['type']) {
					if ('reference' == $info['crmfieldtype']) {
						$info['val'] = $xmlToImport->readString();
						$recordData[$recordNum][$info['refmoule']][] = $info;
					} else {
						if ('true' == $info['notrepeat']) {

							if ('LineNumber' == $xmlToImport->localName) {
								$lineProd = $xmlToImport->readString();
							}

							$info['val'] = $xmlToImport->readString();
							$recordData[$recordNum][$this->request->get('module')]['prod_line'][$lineProd][] = $info;
						} else {
							$info['val'] = $xmlToImport->readString();
							$recordData[$recordNum][$this->request->get('module')][] = $info;
						}
					}
				}
			}

			if (XMLReader::END_ELEMENT == $xmlToImport->nodeType && $xmlToImport->localName == $firstElement) {
				$recordNum++;
				$lineProd = '';
			}
		}
		return $recordData;
	}
//	protected function getFieldInfoByTagName($tagName)
//	{
//		$fieldInfo = array();
//		$tpl = XmlUtils::readTpl($this->request->get('xml_import_tpl'));
//		while ($tpl->read()) {
//			if (XMLReader::ELEMENT == $tpl->nodeType) {
//				if ($tagName == $tpl->localName) {
//					while ($tpl->moveToNextAttribute()) {
//						$fieldInfo[$tpl->name] = $tpl->value;
//					}
//				}
//			}
//		}
//		return $fieldInfo;
//	}
//	protected function checkMendatoryFieldInTpl()
//	{
//
//		$mendatoryTab = $this->getMandatoryField();
//		if (count($mendatoryTab)) {
//			foreach ($mendatoryTab as $field => $trLabel) {
//				if (!in_array($field, $this->skipField) && !$this->getValueFromTplByField($field)) {
//					//throw new Exception(vtranslate('LACK_OF_VALUE_FOR_ALL_MANDATORY_FIELDS_IN_TPL') . ' - ' . $field);
//				}
//			}
//		}
//
//		return true;
//	}
//	protected function createRecords(array $dataTab)
//	{
//		$recordModel = Vtiger_Record_Model::getCleanInstance($this->moduleName);
//		foreach ($dataTab as $fieldName => $value) {
//			$recordModel->set($fieldName, $value);
//		}
//		$recordModel->save();
//	}
//	protected function createRecords(array $dataTab)
//	{
//
//		$fieldMap = [];
//		$unique = uniqid();
//		for ($i = 1; $i <= count($dataTab); $i++) {
//			if (count($dataTab[$i])) {
//				foreach ($dataTab[$i] as $mod => $fieldTab) {
//					if (count($fieldTab)) {
//						foreach ($fieldTab as $key => $single) {
//							if ($single['crmfield'] && !$fieldMap[$mod][$single['crmfield']]) {
//								if ($single['refkeyfld']) {
//									$fieldMap[$i][$mod][$unique . '_import_rel_module'] = $single['refkeyfld'];
//								}
//								$fieldMap[$i][$mod][$single['crmfield']] = $single['val'];
//							}
//							if ('prod_line' === $key) {
//								unset($fieldTab[$key][""]);
//								$fieldMap[$i][$unique . '_product_line'] = $fieldTab[$key];
//							}
//						}
//					}
//				}
//			}
//		}
//		$recordIdTab = array();
//		if (count($fieldMap)) {
//			foreach ($fieldMap as $key => $singleMap) {
//				$allowToCreateRecord = true;
//				foreach ($singleMap as $singleKey => $listFld) {
//					if ($singleKey == $unique . '_product_line') {
//						if (count($singleMap[$unique . '_product_line'])) {
//							foreach ($singleMap[$unique . '_product_line'] as $prodKey => $singleProd) {
//								foreach ($singleProd as $singleVal) {
//									if ('ean' == $singleVal['crmfield']) {
//										$eanResult = CheckRecord::checkExistByEAN($singleVal['val']);
//										if (!$eanResult) {
//											$allowToCreateRecord = false;
//											$this->skipRecord++;
//										} else {
//											$singleMap[$unique . '_product_line'][$prodKey]['rec_id'] = $eanResult;
//										}
//									}
//								}
//							}
//						} else {
//							$allowToCreateRecord = false;
//							$this->skipRecord++;
//						}
//					}
//				}
//
//				$currCode = $this->findInFieldMap('currency_id', $singleMap);
//				$currId = $this->currencyExist($currCode);
//
//				if (!$currId) {
//					throw new Exception(vtranslate('LBL_NO_CURRENCY', 'Import') . ' - ' . $currCode);
//				}
//
//				$preTaxTotal = 0;
//				if ($allowToCreateRecord) {
//					if (count($singleMap)) {
//						foreach ($singleMap as $singleKey => $listFld) {
//							if ($unique . '_product_line' != $singleKey) {
//								$recordModel = Vtiger_Record_Model::getCleanInstance($singleKey);
//								foreach ($listFld as $listKey => $listVal) {
//									$recordModel->set($listKey, $listVal);
//									if ('pre_tax_total' == $listKey) {
//										$recordModel->set('currency_id', $currId);
//										$preTaxTotal = $listVal;
//									}
//								}
//								$recordModel->save();
//								if ($fieldMap[$key][$singleKey][$unique . '_import_rel_module']) {
//									$recordIdTab[$fieldMap[$key][$singleKey][$unique . '_import_rel_module']] = $recordModel->getId();
//								} else {
//									$recordIdTab['main'] = $recordModel->getId();
//								}
//							}
//						}
//					}
//
//					if (count($recordIdTab)) {
//						$this->updateRecordRel($recordIdTab, $singleMap[$unique . '_product_line'], $preTaxTotal);
//					}
//				}
//			}
//		}
//	}
//
//	protected function findInFieldMap($val, $array)
//	{
//
//		foreach ($array as $key => $singleModule) {
//			foreach ($singleModule as $singleKey => $fldValue) {
//
//				if ($val == $singleKey) {
//					return $fldValue;
//				}
//			}
//		}
//	}
//
//	protected function currencyExist($code)
//	{
//
//		$db = PearDatabase::getInstance();
//
//		$sql = "SELECT id FROM vtiger_currency_info WHERE currency_code = ?";
//
//		$result = $db->pquery($sql, array($code), TRUE);
//
//		return $db->query_result($result, 0, 'id');
//	}
//
//	protected function updateRecordRel(array $recordList, $productLine, $total)
//	{
//
//		$recordModel = Vtiger_Record_Model::getInstanceById($recordList['main']);
//		$recordModel->set('mode', 'edit');
//
//		foreach ($recordList as $fld => $value) {
//			$recordModel->set($fld, $value);
//		}
//
//		$recordModel->save();
//
//		if (array_key_exists(1, $productLine)) {
//			$total = $this->findValueInProdTab('hdnSubTotal', $productLine[1]);
//		}
//		if (!empty($productLine)) {
//			$this->addProduct($recordList['main'], $productLine, $total);
//		}
//	}
//
//	protected function addProduct($id, $productList, $total)
//	{
//
//		$type = Vtiger_Functions::getSingleFieldValue('vtiger_crmentity', 'setype', 'crmid', $id);
//
//		if ($type) {
//
//			require_once "modules/$type/$type.php";
//
//			$focus = new $type();
//			$focus->id = $id;
//			$focus->retrieve_entity_info($id, $type);
//			$focus->mode = 'edit';
//
//			$_REQUEST['totalProductCount'] = 0;
//			$_REQUEST['taxtype'] = 'group';
//
//			for ($i = 1; $i <= count($productList); $i++) {
//
//				$_REQUEST['hdnProductId' . $i] = $productList[$i]['rec_id'];
//				$_REQUEST['qty' . $i] = $this->findValueInProdTab('qty', $productList[$i]);
//				$_REQUEST['listPrice' . $i] = $this->findValueInProdTab('listPrice', $productList[$i]);
//
//				$_REQUEST['totalProductCount'] ++;
//			}
//
//			$_REQUEST['subtotal'] = $total;
//			$_REQUEST['total'] = $total;
//
//			saveInventoryProductDetails($focus, $type);
//			$this->importedRecords++;
//		}
//	}
//
//	protected function findValueInProdTab($val, $array)
//	{
//
//		for ($i = 0; $i < count($array); $i++) {
//			if ($val == $array[$i]['crmfield']) {
//				return $array[$i]['val'];
//			}
//		}
//	}
//
//	public function startImport()
//	{
//
//		vglobal('VTIGER_BULK_SAVE_MODE', false);
//
////		if ($this->checkMendatoryFieldInTpl()) {
//
//		$xmlToImport = new XMLReader();
//		$xmlToImport->open($this->getFilePath());
//
//		if ($this->isTemplate()) {
//			list($recordData, $recordsInventoryData) = $this->getRecordDataFromXMLTemplate($xmlToImport);
//		} else {
//			list($recordData, $recordsInventoryData) = $this->getRecordDataFromXML($xmlToImport);
//		}
//		$this->createRecords([$recordData, $recordsInventoryData[0]]);
////		}
//	}
//
//	public function transformForImport($fieldData, $moduleMeta, $fillDefault = true, $checkMandatoryFieldValues = true)
//	{
//		$moduleFields = $moduleMeta->getModuleFields();
//		$defaultFieldValues = []; //$this->getDefaultFieldValues($moduleMeta);
//		foreach ($fieldData as $fieldName => $fieldValue) {
//			$fieldInstance = $moduleFields[$fieldName];
//			if ($fieldInstance->getFieldDataType() == 'owner') {
//				$ownerId = getUserId_Ol(trim($fieldValue));
//				if (empty($ownerId)) {
//					$ownerId = getGrpId($fieldValue);
//				}
//				if (empty($ownerId) && isset($defaultFieldValues[$fieldName])) {
//					$ownerId = $defaultFieldValues[$fieldName];
//				}
//				if (empty($ownerId) ||
//					!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $ownerId)) {
//					$ownerId = $this->user->id;
//				}
//				$fieldData[$fieldName] = $ownerId;
//			} elseif ($fieldInstance->getFieldDataType() == 'multipicklist') {
//				$trimmedValue = trim($fieldValue);
//
//				if (!$trimmedValue && isset($defaultFieldValues[$fieldName])) {
//					$explodedValue = explode(',', $defaultFieldValues[$fieldName]);
//				} else {
//					$explodedValue = explode(' |##| ', $trimmedValue);
//				}
//
//				foreach ($explodedValue as $key => $value) {
//					$explodedValue[$key] = trim($value);
//				}
//
//				$implodeValue = implode(' |##| ', $explodedValue);
//				$fieldData[$fieldName] = $implodeValue;
//			} elseif ($fieldInstance->getFieldDataType() == 'reference') {
//				$entityId = false;
//				if (!empty($fieldValue)) {
//					if (strpos($fieldValue, '::::') > 0) {
//						$fieldValueDetails = explode('::::', $fieldValue);
//					} else if (strpos($fieldValue, ':::') > 0) {
//						$fieldValueDetails = explode(':::', $fieldValue);
//					} else {
//						$fieldValueDetails = $fieldValue;
//					}
//					if (count($fieldValueDetails) > 1) {
//						$referenceModuleName = trim($fieldValueDetails[0]);
//						$entityLabel = trim($fieldValueDetails[1]);
//						$entityId = getEntityId($referenceModuleName, $entityLabel);
//					} else {
//						$referencedModules = $fieldInstance->getReferenceList();
//						$entityLabel = $fieldValue;
//						foreach ($referencedModules as $referenceModule) {
//							$referenceModuleName = $referenceModule;
//							if ($referenceModule == 'Users') {
//								$referenceEntityId = getUserId_Ol($entityLabel);
//								if (empty($referenceEntityId) ||
//									!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $referenceEntityId)) {
//									$referenceEntityId = $this->user->id;
//								}
//							} elseif ($referenceModule == 'Currency') {
//								$referenceEntityId = getCurrencyId($entityLabel);
//							} else {
//								$referenceEntityId = getEntityId($referenceModule, $entityLabel);
//							}
//							if ($referenceEntityId != 0) {
//								$entityId = $referenceEntityId;
//								break;
//							}
//						}
//
//						if ($entityId == false) {
//							$request = new Vtiger_Request($_REQUEST);
//							$referenceModuleName = $request->get($fieldName . '_defaultvalue');
//						}
//					}
//					if ((empty($entityId) || $entityId == 0) && !empty($referenceModuleName)) {
//						if (isPermitted($referenceModuleName, 'CreateView') == 'yes') {
//							try {
//								$wsEntityIdInfo = $this->createEntityRecord($referenceModuleName, $entityLabel);
//								$wsEntityId = $wsEntityIdInfo['id'];
//								$entityIdComponents = vtws_getIdComponents($wsEntityId);
//								$entityId = $entityIdComponents[1];
//							} catch (Exception $e) {
//								$entityId = getEntityId($referenceModuleName, $entityLabel);
//								if ($entityId == 0)
//									$entityId = false;
//							}
//						}
//					}
//					$fieldData[$fieldName] = $entityId;
//				} else {
//					$referencedModules = $fieldInstance->getReferenceList();
//					if ($referencedModules[0] == 'Users') {
//						if (isset($defaultFieldValues[$fieldName])) {
//							$fieldData[$fieldName] = $defaultFieldValues[$fieldName];
//						}
//						if (empty($fieldData[$fieldName]) ||
//							!Import_Utils_Helper::hasAssignPrivilege($moduleMeta->getEntityName(), $fieldData[$fieldName])) {
//							$fieldData[$fieldName] = $this->user->id;
//						}
//					} else {
//						$fieldData[$fieldName] = '';
//					}
//				}
//			} elseif ($fieldInstance->getFieldDataType() == 'picklist') {
//				$fieldValue = trim($fieldValue);
//				global $default_charset;
//				if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
//					$fieldData[$fieldName] = $fieldValue = $defaultFieldValues[$fieldName];
//				}
//				$olderCacheEnable = Vtiger_Cache::$cacheEnable;
//				Vtiger_Cache::$cacheEnable = false;
//				if (!isset($this->allPicklistValues[$fieldName])) {
//					$this->allPicklistValues[$fieldName] = $fieldInstance->getPicklistDetails();
//				}
//				$allPicklistDetails = $this->allPicklistValues[$fieldName];
//
//				$allPicklistValues = array();
//				foreach ($allPicklistDetails as $picklistDetails) {
//					$allPicklistValues[] = $picklistDetails['value'];
//				}
//
//				$picklistValueInLowerCase = strtolower(htmlentities($fieldValue, ENT_QUOTES, $default_charset));
//				$allPicklistValuesInLowerCase = array_map('strtolower', $allPicklistValues);
//				$picklistDetails = array_combine($allPicklistValuesInLowerCase, $allPicklistValues);
//
//				if (!in_array($picklistValueInLowerCase, $allPicklistValuesInLowerCase)) {
//					$moduleObject = Vtiger_Module::getInstance($moduleMeta->getEntityName());
//					$fieldObject = Vtiger_Field::getInstance($fieldName, $moduleObject);
//					$fieldObject->setPicklistValues(array($fieldValue));
//					unset($this->allPicklistValues[$fieldName]);
//				} else {
//					$fieldData[$fieldName] = $picklistDetails[$picklistValueInLowerCase];
//				}
//				Vtiger_Cache::$cacheEnable = $olderCacheEnable;
//			} else if ($fieldInstance->getFieldDataType() == 'currency') {
//				// While exporting we are exporting as user format, we should import as db format while importing
//				$fieldData[$fieldName] = CurrencyField::convertToDBFormat($fieldValue, $current_user, false);
//			} else if ($fieldInstance->getFieldDataType() == 'tree') {
//				if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
//					$fieldData[$fieldName] = $fieldValue = $defaultFieldValues[$fieldName];
//				} else if (!empty($fieldValue)) {
//					$fieldValue = trim($fieldValue);
//					foreach ($fieldInstance->getTreeDetails() as $id => $tree) {
//						if ($tree == $fieldValue) {
//							$fieldData[$fieldName] = $id;
//						}
//					}
//				}
//			} else {
//				if ($fieldInstance->getFieldDataType() == 'datetime' && !empty($fieldValue)) {
//					if ($fieldValue == null || $fieldValue == '0000-00-00 00:00:00') {
//						$fieldValue = '';
//					}
//					$valuesList = explode(' ', $fieldValue);
//					if (count($valuesList) == 1)
//						$fieldValue = '';
//					$fieldValue = getValidDBInsertDateTimeValue($fieldValue);
//					if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2} ([0-1][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/", $fieldValue) == 0) {
//						$fieldValue = '';
//					}
//					$fieldData[$fieldName] = $fieldValue;
//				}
//				if ($fieldInstance->getFieldDataType() == 'date' && !empty($fieldValue)) {
//					if ($fieldValue == null || $fieldValue == '0000-00-00') {
//						$fieldValue = '';
//					}
//					$fieldValue = getValidDBInsertDateValue($fieldValue);
//					if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/", $fieldValue) == 0) {
//						$fieldValue = '';
//					}
//					$fieldData[$fieldName] = $fieldValue;
//				}
//				if (empty($fieldValue) && isset($defaultFieldValues[$fieldName])) {
//					$fieldData[$fieldName] = $fieldValue = $defaultFieldValues[$fieldName];
//				}
//			}
//		}
//		if ($fillDefault) {
//			foreach ($defaultFieldValues as $fieldName => $fieldValue) {
//				if (!isset($fieldData[$fieldName])) {
//					$fieldData[$fieldName] = $defaultFieldValues[$fieldName];
//				}
//			}
//		}
//
//		// We should sanitizeData before doing final mandatory check below.
////		$fieldData = DataTransform::sanitizeData($fieldData, $moduleMeta);
//
//		if ($fieldData != null && $checkMandatoryFieldValues) {
//			foreach ($moduleFields as $fieldName => $fieldInstance) {
//				if (empty($fieldData[$fieldName]) && $fieldInstance->isMandatory()) {
//					return null;
//				}
//			}
//		}
//		return $fieldData;
//	}
//	protected function getMandatoryField()
//	{
//
//		$moduleMeta = $this->moduleModel->getModuleMeta();
//		return $moduleMeta->getMandatoryFields($this->moduleName);
//	}
//	protected function getValueFromTplByField($fieldName)
//	{
//
//		$tpl = XmlUtils::readTpl($this->request->get('xml_import_tpl'));
//
//		while ($tpl->read()) {
//
//			$crmField = $tpl->getAttribute('crmfield');
//
//			if (in_array($fieldName, explode('|', $crmField))) {
//				return true;
//			} else {
//				$fldType = $tpl->getAttribute('crmfieldtype');
//
//				if ('reference' == $fldType) {
//					$refModule = Vtiger_Record_Model::getCleanInstance($tpl->getAttribute('refmoule'));
//					if ($refModule->getField($fieldName)) {
//						return true;
//					}
//				}
//			}
//		}
//
//		return false;
//	}
//	public function showResults()
//	{
//		header('Location: index.php?module=' . $this->moduleName . '&view=EdiImportResult&ok_rec=' . $this->importedRecords . '&fail=' . $this->skipRecord);
//	}
}
