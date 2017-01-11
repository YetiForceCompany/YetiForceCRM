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
AppConfig::iniSet("auto_detect_line_endings", true);

class Import_CSVReader_Reader extends Import_FileReader_Reader
{

	/**
	 * Creates a table using one table's values as keys, and the other's as values.
	 * @param array $keys
	 * @param array $values
	 * @return array
	 */
	public function arrayCombine($keys, $values)
	{
		$combine = $dup = [];
		foreach ($keys as $key => $keyData) {
			if (isset($combine[$keyData])) {
				if (!$dup[$keyData]) {
					$dup[$keyData] = 1;
				}
				$keyData = $keyData . "(" . ++$dup[$keyData] . ")";
			}
			$combine[$keyData] = $values[$key];
		}
		return $combine;
	}

	/**
	 * Function gets data of the first record to import
	 * @param bool $hasHeader
	 * @return array
	 */
	public function getFirstRowData($hasHeader = true)
	{
		$defaultCharset = \AppConfig::main('default_charset', 'UTF-8');
		$fileHandler = $this->getFileHandler();
		if ($this->moduleModel->isInventory()) {
			$isInventory = true;
		}

		$rowData = $headers = $standardData = $inventoryData = [];
		$currentRow = 0;
		while ($data = fgetcsv($fileHandler, 0, $this->request->get('delimiter'))) {
			if ($currentRow === 0 || ($currentRow === 1 && $hasHeader)) {
				if ($hasHeader && $currentRow === 0) {
					foreach ($data as $key => $value) {
						if ($isInventory && strpos($value, 'Inventory::') === 0) {
							$value = substr($value, 11);
							$inventoryHeaders[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $defaultCharset);
						} else {
							$headers[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $defaultCharset);
						}
					}
				} else {
					foreach ($data as $key => $value) {
						if ($isInventory && isset($inventoryHeaders[$key])) {
							$inventoryData[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $defaultCharset);
						} else {
							$standardData[$key] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $defaultCharset);
						}
					}
					break;
				}
			}
			$currentRow++;
		}

		if ($hasHeader) {
			$standardData = $this->syncRowData($headers, $standardData);
			$rowData['LBL_STANDARD_FIELDS'] = $this->arrayCombine($headers, $standardData);
			if ($inventoryData) {
				$standardData = $this->syncRowData($inventoryHeaders, $inventoryData);
				$rowData['LBL_INVENTORY_FIELDS'] = $this->arrayCombine($inventoryHeaders, $inventoryData);
			}
		} else {
			$rowData = $standardData;
		}
		unset($fileHandler);
		return $rowData;
	}

	/**
	 * Adjust first row data to get in sync with the number of headers
	 * @param array $keys
	 * @param array $values
	 * @return array
	 */
	public function syncRowData($keys, $values)
	{
		$noOfHeaders = count($keys);
		$noOfFirstRowData = count($values);
		if ($noOfHeaders > $noOfFirstRowData) {
			$values = array_merge($values, array_fill($noOfFirstRowData, $noOfHeaders - $noOfFirstRowData, ''));
		} elseif ($noOfHeaders < $noOfFirstRowData) {
			$values = array_slice($values, 0, count($keys), true);
		}
		return $values;
	}

	/**
	 * Function creates tables for import in database
	 */
	public function read()
	{
		$defaultCharset = AppConfig::main('default_charset');
		$fileHandler = $this->getFileHandler();
		$this->createTable();

		$fieldMapping = $this->request->get('field_mapping');
		$inventoryFieldMapping = $this->request->get('inventory_field_mapping');
		if ($this->moduleModel->isInventory()) {
			$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->moduleModel->getName());
			$inventoryFields = $inventoryFieldModel->getFields();
		}
		$i = -1;
		$skip = $importId = $skipData = false;
		while ($data = fgetcsv($fileHandler, 0, $this->request->get('delimiter'))) {
			$i++;
			if ($this->request->get('has_header') && $i === 0) {
				foreach ($data as $index => $fullName) {
					if ($this->moduleModel->isInventory() && strpos($fullName, 'Inventory::') === 0) {
						$name = substr($fullName, 11);
						if ($name !== 'recordIteration') {
							$inventoryNames[$index] = $name;
						} else {
							$skip = $index;
						}
					}
				}
				continue;
			}
			$mappedData = $inventoryMappedData = [];
			$allValuesEmpty = true;
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$mappedData[$fieldName] = $fieldValue;
				if ($this->request->get('file_encoding') !== $defaultCharset) {
					$mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $defaultCharset);
				}
				if (!empty($fieldValue))
					$allValuesEmpty = false;
			}
			foreach ($inventoryFieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$inventoryMappedData[$i][$fieldName] = $fieldValue;
				$fieldModel = $inventoryFields[$fieldName];
				foreach ($fieldModel->getCustomColumn() as $columnParamsName => $dataType) {
					if (in_array($columnParamsName, $inventoryNames)) {
						$key = array_search($columnParamsName, $inventoryNames);
						$inventoryMappedData[$i][$columnParamsName] = $data[$key];
					}
				}
			}
			if (!$allValuesEmpty) {
				if (!$skip || !$importId || ($skip && $skipData !== $data[$skip])) {
					$importId = $this->addRecordToDB($mappedData);
				}
				if ($importId && $inventoryMappedData) {
					$this->addInventoryToDB($inventoryMappedData, $importId);
				}
				if ($skip) {
					$skipData = $data[$skip];
				}
			}
		}
		unset($fileHandler);
	}
}
