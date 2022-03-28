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

class Import_CSVReader_Reader extends Import_FileReader_Reader
{
	/**
	 * Parsed file data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Import_CSVReader_Reader constructor.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 */
	public function __construct(App\Request $request, App\User $user)
	{
		parent::__construct($request, $user);
		$csv = new \ParseCsv\Csv();
		$csv->delimiter = $this->request->get('delimiter');
		$csv->heading = false;
		$csv->use_mb_convert_encoding = true;
		$csv->encoding($this->request->get('file_encoding'), \App\Config::main('default_charset', 'UTF-8'));
		$csv->parseFile($this->getFilePath());
		$this->data = $csv->data;
	}

	/**
	 * Creates a table using one table's values as keys, and the other's as values.
	 *
	 * @param array $keys
	 * @param array $values
	 *
	 * @return array
	 */
	public function arrayCombine($keys, $values)
	{
		$combine = $dup = [];
		foreach ($keys as $key => $keyData) {
			if (isset($combine[$keyData])) {
				if (!isset($dup[$keyData])) {
					$dup[$keyData] = 1;
				}
				$keyData = $keyData . '(' . ++$dup[$keyData] . ')';
			}
			$combine[$keyData] = $values[$key];
		}
		return $combine;
	}

	/**
	 * Function gets data of the first record to import.
	 *
	 * @param bool $hasHeader
	 *
	 * @return array
	 */
	public function getFirstRowData($hasHeader = true)
	{
		$isInventory = $this->moduleModel->isInventory();
		$rowData = $headers = $standardData = $inventoryData = [];
		foreach ($this->data as $currentRow => $data) {
			if (0 === $currentRow || (1 === $currentRow && $hasHeader)) {
				if ($hasHeader && 0 === $currentRow) {
					foreach ($data as $key => $value) {
						if (!empty($isInventory) && 0 === strpos($value, 'Inventory::')) {
							$value = substr($value, 11);
							$inventoryHeaders[$key] = \App\Purifier::purify($value);
						} else {
							$headers[$key] = \App\Purifier::purify($value);
						}
					}
				} else {
					foreach ($data as $key => $value) {
						if (!empty($isInventory) && isset($inventoryHeaders[$key])) {
							$inventoryData[$key] = $value;
						} else {
							$standardData[$key] = $value;
						}
					}
					break;
				}
			}
		}
		if ($hasHeader) {
			$standardData = $this->syncRowData($headers, $standardData);
			$rowData['LBL_STANDARD_FIELDS'] = $this->arrayCombine($headers, $standardData);
			if ($inventoryData) {
				$rowData['LBL_INVENTORY_FIELDS'] = $this->arrayCombine($inventoryHeaders, $inventoryData);
			}
		} else {
			$rowData = $standardData;
		}
		return $rowData;
	}

	/**
	 * Adjust first row data to get in sync with the number of headers.
	 *
	 * @param array $keys
	 * @param array $values
	 *
	 * @return array
	 */
	public function syncRowData($keys, $values)
	{
		$noOfHeaders = \count($keys);
		$noOfFirstRowData = \count($values);
		if ($noOfHeaders > $noOfFirstRowData) {
			$values = array_merge($values, array_fill($noOfFirstRowData, $noOfHeaders - $noOfFirstRowData, ''));
		} elseif ($noOfHeaders < $noOfFirstRowData) {
			$values = \array_slice($values, 0, \count($keys), true);
		}
		return $values;
	}

	/**
	 * Function creates tables for import in database.
	 */
	public function read()
	{
		$this->createTable();
		$fieldMapping = $this->request->get('field_mapping');
		$inventoryFieldMapping = $this->request->get('inventory_field_mapping');
		if ($this->moduleModel->isInventory()) {
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleModel->getName());
			$inventoryFields = $inventoryModel->getFields();
		}
		$sourceId = $this->request->getInteger('src_record');
		$relationId = $this->request->getInteger('relationId');
		$skip = $importId = $skipData = false;
		foreach ($this->data as $i => $data) {
			if ($this->request->get('has_header') && 0 === $i) {
				foreach ($data as $index => $fullName) {
					if ($this->moduleModel->isInventory() && 0 === strpos($fullName, 'Inventory::')) {
						$name = substr($fullName, 11);
						if ('recordIteration' !== $name) {
							$inventoryNames[$index] = $name;
						} else {
							$skip = $index;
						}
					}
				}
				continue;
			}
			$mappedData = $inventoryMappedData = [];
			if ($sourceId && $relationId) {
				$mappedData['src_record'] = $sourceId;
				$mappedData['relation_id'] = $relationId;
			}
			$allValuesEmpty = true;
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$fieldValueTemp = $fieldValue;
				$fieldValueTemp = str_replace(',', '.', $fieldValueTemp);
				if (is_numeric($fieldValueTemp)) {
					$fieldValue = $fieldValueTemp;
				}
				$mappedData[$fieldName] = $fieldValue;
				if (!empty($fieldValue)) {
					$allValuesEmpty = false;
				}
			}
			foreach ($inventoryFieldMapping as $fieldName => $index) {
				$fieldValue = $data[$index];
				$inventoryMappedData[$i][$fieldName] = $fieldValue;
				$fieldModel = $inventoryFields[$fieldName];
				foreach ($fieldModel->getCustomColumn() as $columnParamsName => $dataType) {
					if (\in_array($columnParamsName, $inventoryNames)) {
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
	}
}
