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

class Import_FileReader_Reader
{
	public $temp_status = 'success';
	public $numberOfRecordsRead = 0;
	public $errorMessage = '';
	public $user;
	public $request;
	public $moduleModel;

	/**
	 * Constructor.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 */
	public function __construct(App\Request $request, App\User $user)
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
		if ('on' == $this->request->get('has_header') || 1 == $this->request->get('has_header') || true === $this->request->get('has_header')) {
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
			$this->errorMessage = 'ERR_FILE_DOESNT_EXIST';

			return false;
		}

		$fileHandler = fopen($filePath, 'r');
		if (!$fileHandler) {
			$this->temp_status = 'failed';
			$this->errorMessage = 'ERR_CANT_OPEN_FILE';

			return false;
		}
		return $fileHandler;
	}

	/**
	 * @deprecated Use \App\Utils::convertCharacterEncoding()
	 *
	 * @param mixed $value
	 * @param mixed $fromCharset
	 * @param mixed $toCharset
	 */
	public function convertCharacterEncoding($value, $fromCharset, $toCharset)
	{
		return \App\Utils::convertCharacterEncoding($value, $fromCharset, $toCharset);
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

	/**
	 * Function creates tables for import in database.
	 */
	public function createTable()
	{
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$tableName = Import_Module_Model::getDbTableName($this->user);
		$fieldMapping = $this->request->get('field_mapping');
		$moduleFields = $this->moduleModel->getFields();
		$columns = [
			'id' => 'pk',
			'temp_status' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_SMALLINT, 1)->defaultValue(0),
			'recordid' => 'integer',
		];
		if ($this->request->getInteger('src_record') && $this->request->getInteger('relationId')) {
			$columns['src_record'] = 'integer';
			$columns['relation_id'] = $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_SMALLINT, 5)->defaultValue(0);
		}
		foreach ($fieldMapping as $fieldName => $index) {
			if ($field = $moduleFields[$fieldName]) {
				$stringTypes = array_merge(Vtiger_Field_Model::$referenceTypes, ['owner', 'currencyList', 'sharedOwner']);
				if (\in_array($field->getFieldDataType(), $stringTypes)) {
					$columns[$fieldName] = $schema->createColumnSchemaBuilder('string', 255);
				} else {
					$columns[$fieldName] = $field->getDBColumnType();
				}
			}
		}
		$db->createTable($tableName, $columns);

		if ($this->moduleModel->isInventory()) {
			$inventoryTableName = Import_Module_Model::getInventoryDbTableName($this->user);
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleModel->getName());
			$columns = [
				'id' => $schema->createColumnSchemaBuilder('integer', 19),
			];
			foreach ($inventoryModel->getFields() as $fieldObject) {
				$dbType = $fieldObject->getDBType();
				if (\in_array($fieldObject->getType(), ['Name', 'Reference', 'Currency'])) {
					$dbType = $schema->createColumnSchemaBuilder('string', 200);
				} elseif (\is_array($dbType)) {
					$dbType = $schema->createColumnSchemaBuilder($dbType[0], $dbType[1]);
				}
				$columns[$fieldObject->getColumnName()] = $dbType;
				foreach ($fieldObject->getCustomColumn() as $name => $dbType) {
					if (\is_array($dbType)) {
						$dbType = $schema->createColumnSchemaBuilder($dbType[0], $dbType[1]);
					}
					$columns[$name] = $dbType;
				}
			}
			$db->createTable($inventoryTableName, $columns);
			$db->createCommand()->createIndex('id_idx', $inventoryTableName, 'id')->execute();
			$db->createCommand()->addForeignKey('fk_1_' . $inventoryTableName, $inventoryTableName, 'id', $tableName, 'id', 'CASCADE', 'RESTRICT')->execute();
		}
	}

	/**
	 * Function adds imported data to database.
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function addRecordToDB($data)
	{
		$db = \App\Db::getInstance();
		$tableName = Import_Module_Model::getDbTableName($this->user);
		$db->createCommand()->insert($tableName, $data)->execute();
		++$this->numberOfRecordsRead;

		return $db->getLastInsertID($tableName . '_id_seq');
	}

	/**
	 * Function adds imported inventory data to database.
	 *
	 * @param array $inventoryData
	 * @param int   $importId
	 */
	public function addInventoryToDB($inventoryData, $importId)
	{
		$db = \App\Db::getInstance();
		$tableName = Import_Module_Model::getInventoryDbTableName($this->user);
		foreach ($inventoryData as $data) {
			$data['id'] = $importId;
			$db->createCommand()->insert($tableName, $data)->execute();
		}
	}
}
