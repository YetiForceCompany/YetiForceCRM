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

class Import_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Import table prefix.
	 *
	 * @var string
	 */
	const IMPORT_TABLE_PREFIX = 'u_yf_import_';

	/**
	 * Auto marge state.
	 *
	 * @var int
	 */
	const AUTO_MERGE_NONE = 0;
	const AUTO_MERGE_IGNORE = 1;
	const AUTO_MERGE_OVERWRITE = 2;
	const AUTO_MERGE_MERGEFIELDS = 3;
	const AUTO_MERGE_EXISTINGISPRIORITY = 4;

	/**
	 * Components name.
	 *
	 * @var array
	 */
	public static $componentReader = [
		'csv' => 'CSVReader',
		'vcf' => 'VCardReader',
		'ics' => 'ICSReader',
		'ical' => 'ICSReader',
		'default' => 'FileReader',
		'xml' => 'XmlReader',
		'zip' => 'ZipReader',
	];
	public static $supportedFileEncoding = [
		'UTF-8' => 'UTF-8',
		'ISO-8859-1' => 'ISO-8859-1',
		'Windows-1250' => 'Windows-1250',
		'Windows-1251' => 'Windows-1251',
		'Windows-1252' => 'Windows-1252',
		'Windows-1253' => 'Windows-1253',
		'Windows-1254' => 'Windows-1254',
		'Windows-1255' => 'Windows-1255',
		'Windows-1256' => 'Windows-1256',
		'Windows-1257' => 'Windows-1257',
		'Windows-1258' => 'Windows-1258',
	];
	public static $supportedDelimiters = [',' => 'comma', ';' => 'semicolon'];
	public static $supportedFileExtensions = ['csv', 'vcf', 'ical', 'xml', 'ics'];
	public static $supportedFileExtensionsByModule = ['Contacts' => ['csv', 'vcf', 'xml', 'zip'], 'Calendar' => ['csv', 'ical', 'ics', 'xml'], 'Default' => ['csv', 'xml', 'zip']];
	public $importModule;
	public $importModuleModel;

	/**
	 * Function returns supported extensions.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getSupportedFileExtensions(?string $moduleName = null)
	{
		if (!$moduleName) {
			return self::$supportedFileExtensions;
		}
		switch ($moduleName) {
				case 'Contacts':
				case 'Calendar':
					return self::$supportedFileExtensionsByModule[$moduleName];
				default:
					return self::$supportedFileExtensionsByModule['Default'];
			}
	}

	/**
	 * Function returns supported extensions.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getSupportedFileExtensionsDescription($moduleName)
	{
		$supportedFileTypes = self::getSupportedFileExtensions($moduleName);
		$description = [];
		foreach ($supportedFileTypes as $fileType) {
			$description[] = '.' . strtoupper($fileType);
		}
		return implode(', ', $description);
	}

	/**
	 * Function returns supported extensions.
	 *
	 * @return type
	 */
	public static function getSupportedFileEncoding()
	{
		return self::$supportedFileEncoding;
	}

	/**
	 * Get supported delimiters.
	 *
	 * @return type
	 */
	public static function getSupportedDelimiters()
	{
		return self::$supportedDelimiters;
	}

	public static function getAutoMergeTypes()
	{
		return [
			self::AUTO_MERGE_IGNORE => 'Skip',
			self::AUTO_MERGE_OVERWRITE => 'Overwrite',
			self::AUTO_MERGE_MERGEFIELDS => 'Merge',
			self::AUTO_MERGE_EXISTINGISPRIORITY => 'LBL_FILL_EMPTY'
		];
	}

	/**
	 * Function returns list of templates to import.
	 *
	 * @param type $moduleName
	 *
	 * @return array
	 */
	public static function getListTplForXmlType($moduleName)
	{
		$output = [];
		$path = 'modules/Import/tpl/';
		if (is_dir($path)) {
			$list = new DirectoryIterator($path);
			foreach ($list as $singleFile) {
				if (!$singleFile->isDot()) {
					$fileName = $singleFile->getFilename();
					if (0 === strpos($fileName, $moduleName)) {
						$output[] = $fileName;
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Get file reader.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 *
	 * @return \Import_FileReader_Reader
	 */
	public static function getFileReader(App\Request $request, App\User $user)
	{
		$type = $request->get('type');
		if ($componentName = static::$componentReader[$type]) {
			$modelClassName = Vtiger_Loader::getComponentClassName('Reader', $componentName, 'Import');
			return new $modelClassName($request, $user);
		}
		return null;
	}

	/**
	 * Function that returns all the fields for the module.
	 *
	 * @param mixed $blockInstance
	 *
	 * @return Vtiger_Field_Model[] - list of field models
	 */
	public function getFields($blockInstance = false)
	{
		if (empty($this->fields)) {
			$moduleModel = $this->getImportModuleModel();
			$this->fields = [];
			foreach ($moduleModel->getFields() as $moduleField) {
				if ($moduleField->isActiveField() &&
					'vtiger_entity_stats' !== $moduleField->getTableName() &&
					!\in_array($moduleField->getColumnName(), ['modifiedby', 'modifiedtime']) &&
					!\in_array($moduleField->getUIType(), [70, 4]) &&
					0 !== strcasecmp($moduleField->getFieldDataType(), 'autogenerated') &&
					0 !== strcasecmp($moduleField->getFieldDataType(), 'id')) {
					$this->fields[$moduleField->get('name')] = $moduleField;
				}
			}
		}
		return $this->fields;
	}

	/**
	 * Set import module name.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public function setImportModule($moduleName)
	{
		$this->importModule = $moduleName;

		return $this;
	}

	/**
	 * Function returns module name where import takes place.
	 *
	 * @return string
	 */
	public function getImportModule()
	{
		return $this->importModule;
	}

	/**
	 * Function returns instance of the module where import takes place.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getImportModuleModel()
	{
		if ($this->importModuleModel) {
			return $this->importModuleModel;
		}
		return $this->importModuleModel = Vtiger_Module_Model::getInstance($this->getImportModule());
	}

	/**
	 * Function returns name of the table to import.
	 *
	 * @param mixed $user
	 *
	 * @return string
	 */
	public static function getDbTableName($user)
	{
		$tableName = self::IMPORT_TABLE_PREFIX;
		if (is_numeric($user)) {
			$tableName .= $user;
		} elseif (method_exists($user, 'getId')) {
			$tableName .= $user->getId();
		} else {
			$tableName .= $user->id;
		}
		return $tableName;
	}

	/**
	 * Function returns name of the table to import for data from advanced block.
	 *
	 * @param mixed $user
	 *
	 * @return string
	 */
	public static function getInventoryDbTableName($user)
	{
		return self::getDbTableName($user) . '_inv';
	}

	/**
	 * Function checks if import is blocked for user.
	 *
	 * @param mixed $user
	 *
	 * @return bool
	 */
	public static function isUserImportBlocked($user)
	{
		$tableName = self::getDbTableName($user);
		if (vtlib\Utils::checkTable($tableName)) {
			return (new \App\Db\Query())->from($tableName)->where(['temp_status' => Import_Data_Action::IMPORT_RECORD_NONE])->exists();
		}
		return false;
	}

	/**
	 * Function clears data related to import of records by user.
	 *
	 * @param mixed $user
	 */
	public static function clearUserImportInfo($user)
	{
		$db = \App\Db::getInstance();
		$tables = [self::getInventoryDbTableName($user), self::getDbTableName($user)];
		foreach ($tables as $table) {
			if (!empty($db->getTableSchema($table, true))) {
				$db->createCommand()->dropTable($table)->execute();
			}
		}
		Import_Lock_Action::unLock($user);
		Import_Queue_Action::removeForUser($user);
	}

	/**
	 * Get callback url.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if ($this->get('relationId') && $this->get('src_record') && $relationModel = Vtiger_Relation_Model::getInstanceById($this->get('relationId'))) {
			$url = $relationModel->getListUrl(Vtiger_Record_Model::getInstanceById($this->get('src_record')));
		} else {
			$url = $this->getImportModuleModel()->getListViewUrl();
		}
		return $url;
	}
}
