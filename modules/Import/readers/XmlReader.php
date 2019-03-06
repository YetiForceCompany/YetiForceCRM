<?php

/**
 * XmlReader Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Import_XmlReader_Reader extends Import_FileReader_Reader
{
	protected $moduleName;
	protected $skipField = ['assigned_user_id', 'productid'];
	protected $skipRecord = 0;
	protected $importedRecords = 0;
	protected $relatedInventoryField = [];

	/**
	 * Constructor.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 */
	public function __construct(\App\Request $request, \App\User $user)
	{
		$this->moduleName = $request->getModule();
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
		if (isset($this->filePath)) {
			return $this->filePath;
		}
		return parent::getFilePath();
	}

	/**
	 * Function creates tables for import in database.
	 */
	public function createTable()
	{
		$tableName = Import_Module_Model::getDbTableName($this->user);
		if (!\vtlib\Utils::checkTable($tableName)) {
			parent::createTable();
		}
	}

	/**
	 * Function reads data from file and adds to database.
	 */
	public function read()
	{
		$defaultCharset = AppConfig::main('default_charset');
		$this->createTable();

		$fieldMapping = $this->request->get('field_mapping');
		$inventoryFieldMapping = $this->request->get('inventory_field_mapping');

		$xmlToImport = new XMLReader();
		$xmlToImport->open($this->getFilePath());

		list($recordData, $recordsInventoryData) = $this->getRecordDataFromXML($xmlToImport, false);
		$columnsName = $this->inventoryData['tags'] ?? [];
		$mappedData = [];
		$inventoryMappedData = [];
		$allValuesEmpty = true;
		$moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);
		$fields = $moduleModel->getFields();
		foreach ($fieldMapping as $fieldName => $index) {
			$fieldValue = $recordData[$index];
			if ($this->request->get('file_encoding') !== $defaultCharset) {
				$fieldValue = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $defaultCharset);
			}
			$mappedData[$fieldName] = isset($fields[$fieldName]) && $fields[$fieldName]->getFieldDataType() === 'text' ? \App\Purifier::purifyHtml($fieldValue) : \App\Purifier::purify($fieldValue);
			if (!empty($fieldValue)) {
				$allValuesEmpty = false;
			}
		}
		if ($inventoryFieldMapping && $recordsInventoryData) {
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryModel->getFields();
			foreach ($recordsInventoryData as $index => $data) {
				foreach ($data as $key => $fieldValue) {
					$fieldName = array_search($key, $inventoryFieldMapping);
					$fieldModel = $inventoryFields[$fieldName];
					if ($fieldModel) {
						$inventoryMappedData[$index][$fieldName] = $fieldValue;
						foreach ($fieldModel->getCustomColumn() as $columnParamsName => $dataType) {
							if (in_array($columnParamsName, $columnsName)) {
								$key = array_search($columnParamsName, $columnsName);
								$inventoryMappedData[$index][$columnParamsName] = $data[$key];
							}
						}
					}
				}
			}
		}
		if (!$allValuesEmpty) {
			$importId = $this->addRecordToDB($mappedData);
			if ($importId && $inventoryMappedData) {
				$this->addInventoryToDB($inventoryMappedData, $importId);
			}
		}
	}

	public function isTemplate()
	{
		return !empty($this->request->get('xml_import_tpl'));
	}

	public function arrayCombine($key, $value)
	{
		$combine = [];
		$dup = [];
		$countKey = count($key);
		for ($i = 0; $i < $countKey; ++$i) {
			if (array_key_exists($key[$i], $combine)) {
				if (!isset($dup[$key[$i]])) {
					$dup[$key[$i]] = 1;
				}
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
		while ($xmlToImport->read()) {
			if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
				if (!in_array($xmlToImport->localName, ['MODULE_FIELDS', 'INVENTORY_ITEM', 'INVENTORY_ITEMS'])) {
					$labels[] = \App\Purifier::purify($xmlToImport->getAttribute('label'));
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
		while ($xmlToImport->read()) {
			if ($xmlToImport->nodeType == XMLReader::ELEMENT) {
				if ($xmlToImport->localName == 'INVENTORY_ITEM') {
					++$index;
					continue;
				}
				$label = \App\Purifier::purify($xmlToImport->getAttribute('label'));
				if (empty($label)) {
					$label = $xmlToImport->localName;
				}
				$labels[$index][] = $label;
				$columnsName[$index][] = $xmlToImport->localName;
				$recordInventoryData[$index][] = $xmlToImport->readString();
			}
		}
		$this->inventoryData = ['labels' => array_combine($labels[0], $recordInventoryData[0]), 'tags' => $columnsName[0], 'data' => $recordInventoryData];
		if ($keyType === 'label') {
			$recordInventoryData = $this->inventoryData['labels'];
		} else {
			$recordInventoryData = $this->inventoryData['data'];
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
					++$recordNum;
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
				++$recordNum;
				$lineProd = '';
			}
		}
		return $recordData;
	}
}
