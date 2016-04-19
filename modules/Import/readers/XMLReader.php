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
}
