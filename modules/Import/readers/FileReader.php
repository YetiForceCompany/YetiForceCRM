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

class Import_FileReader_Reader
{

	var $temp_status = 'success';
	var $numberOfRecordsRead = 0;
	var $errorMessage = '';
	var $user;
	var $request;
	var $moduleModel;

	public function __construct($request, $user)
	{
		$this->request = $request;
		$this->user = $user;
		$this->moduleModel = Vtiger_Module_Model::getInstance($this->request->get('module'));
	}

	public function getStatus()
	{
		return $this->temp_status;
	}

	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	public function getNumberOfRecordsRead()
	{
		return $this->numberOfRecordsRead;
	}

	public function hasHeader()
	{
		if ($this->request->get('has_header') == 'on' || $this->request->get('has_header') == 1 || $this->request->get('has_header') == true) {
			return true;
		}
		return false;
	}

	public function getFirstRowData($hasHeader = true)
	{
		return null;
	}

	public function getFilePath()
	{
		return Import_Utils_Helper::getImportFilePath($this->user);
	}

	public function getFileHandler()
	{
		$filePath = $this->getFilePath();
		if (!file_exists($filePath)) {
			$this->temp_status = 'failed';
			$this->errorMessage = "ERR_FILE_DOESNT_EXIST";
			return false;
		}

		$fileHandler = fopen($filePath, 'r');
		if (!$fileHandler) {
			$this->temp_status = 'failed';
			$this->errorMessage = "ERR_CANT_OPEN_FILE";
			return false;
		}
		return $fileHandler;
	}

	public function convertCharacterEncoding($value, $fromCharset, $toCharset)
	{
		if (function_exists('mb_convert_encoding') && function_exists('mb_list_encodings') && in_array($fromCharset, mb_list_encodings()) && in_array($toCharset, mb_list_encodings())) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}

	public function read()
	{
		// Sub-class need to implement this
	}

	public function deleteFile()
	{
		$filePath = $this->getFilePath();
		@unlink($filePath);
	}

	public function createTable()
	{
		$db = PearDatabase::getInstance();

		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$fieldMapping = $this->request->get('field_mapping');
		$moduleFields = $this->moduleModel->getFields();
		$columnsListQuery = 'id INT PRIMARY KEY AUTO_INCREMENT, temp_status INT DEFAULT 0, recordid INT';
		$fieldTypes = $this->getModuleFieldDBColumnType();
		foreach ($fieldMapping as $fieldName => $index) {
			if (empty($moduleFields[$fieldName])) {
				continue;
			}
			$fieldObject = $moduleFields[$fieldName];
			$columnsListQuery .= $this->getDBColumnType($fieldObject, $fieldTypes);
		}
		$createTableQuery = 'CREATE TABLE ' . $tableName . ' (' . $columnsListQuery . ') ENGINE=InnoDB ';
		$db->query($createTableQuery);

		if ($this->moduleModel->isInventory()) {
			$inventoryTableName = Import_Utils_Helper::getInventoryDbTableName($this->user);
			$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->moduleModel->getName());
			$columnsInventoryListQuery = ' id INT(19)';
			foreach ($inventoryFieldModel->getFields() as $columnName => $fieldObject) {
				$dbType = $fieldObject->getDBType();
				if (in_array($fieldObject->getName(), ['Name', 'Reference'])) {
					$dbType = 'varchar(200)';
				}
				$columnsInventoryListQuery .= ',' . $fieldObject->getColumnName() . ' ' . $dbType;
				foreach ($fieldObject->getCustomColumn() as $name => $dbType) {
					$columnsInventoryListQuery .= ',' . $name . ' ' . $dbType;
				}
			}
			$columnsInventoryListQuery .= ", CONSTRAINT `" . $inventoryTableName . "_ibfk_1` FOREIGN KEY (`id`) REFERENCES `$tableName` (`id`) ON DELETE CASCADE";
			$createTableQuery = 'CREATE TABLE IF NOT EXISTS ' . $inventoryTableName . ' (' . $columnsInventoryListQuery . ') ENGINE=InnoDB ';
			$db->query($createTableQuery);
		}
		return true;
	}

	public function addRecordToDB($columnNames, $fieldValues, $inventoryData = [])
	{
		$db = PearDatabase::getInstance();

		$tableName = Import_Utils_Helper::getDbTableName($this->user);
		$data = array_combine($columnNames, $fieldValues);
		$db->insert($tableName, $data);
		$this->numberOfRecordsRead++;
		if ($inventoryData) {
			$id = $db->getLastInsertID();
			$tableName = Import_Utils_Helper::getInventoryDbTableName($this->user);
			foreach ($inventoryData as $data) {
				$data['id'] = $id;
				$db->insert($tableName, $data);
			}
		}
	}

	/** Function returns the database column type of the field
	 * @param $fieldObject <Vtiger_Field_Model>
	 * @param $fieldTypes <Array> - fieldnames with column type
	 * @return <String> - column name with type for sql creation of table
	 */
	public function getDBColumnType($fieldObject, $fieldTypes)
	{
		$columnsListQuery = '';
		$fieldName = $fieldObject->getName();
		$dataType = $fieldObject->getFieldDataType();
		$skipDataType = array('reference', 'owner', 'currencyList', 'date', 'datetime', 'sharedOwner');
		if (in_array($dataType, $skipDataType)) {
			$columnsListQuery .= ',' . $fieldName . ' varchar(250)';
		} elseif ($dataType == 'inventory') {
//			if( $fieldObject->getName() == 'ItemNumber'){
//				$columnsListQuery .= ',inv_itemnumber '.$fieldObject->getDBType();
//			}else{
			$columnsListQuery .= ',`' . $fieldObject->getColumnName() . '` ' . $fieldObject->getDBType();
//			}
		} else {
			$columnsListQuery .= ',`' . $fieldName . '` ' . $fieldTypes[$fieldObject->get('column')];
		}

		return $columnsListQuery;
	}

	/** Function returns array of columnnames and their column datatype
	 * @return <Array>
	 */
	public function getModuleFieldDBColumnType()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT tablename FROM vtiger_field WHERE tabid=? GROUP BY tablename', array($this->moduleModel->getId()));
		$tables = array();
		if ($result && $db->num_rows($result) > 0) {
			while ($row = $db->fetch_array($result)) {
				$tables[] = $row['tablename'];
			}
		}
		$fieldTypes = array();
		foreach ($tables as $table) {
			$result = $db->pquery("DESC $table", array());
			if ($result && $db->num_rows($result) > 0) {
				while ($row = $db->fetch_array($result)) {
					$fieldTypes[$row['Field']] = htmlspecialchars_decode($row['Type'], ENT_QUOTES);
				}
			}
		}
		return $fieldTypes;
	}
}
