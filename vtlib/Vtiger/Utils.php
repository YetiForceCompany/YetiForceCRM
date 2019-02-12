<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides few utility functions.
 */
class Utils
{
	/**
	 * Check if given value is a number or not.
	 *
	 * @param mixed String or Integer
	 */
	public static function isNumber($value)
	{
		return is_numeric($value) ? (int) $value == $value : false;
	}

	/**
	 * Implode the prefix and suffix as string for given number of times.
	 *
	 * @param string prefix to use
	 * @param int Number of times
	 * @param string suffix to use (optional)
	 */
	public static function implodestr($prefix, $count, $suffix = false)
	{
		$strvalue = '';
		for ($index = 0; $index < $count; ++$index) {
			$strvalue .= $prefix;
			if ($suffix && $index != ($count - 1)) {
				$strvalue .= $suffix;
			}
		}
		return $strvalue;
	}

	/**
	 * Function to check the file access is made within web root directory as well as is safe for php inclusion.
	 *
	 * @param string File path to check
	 * @param bool False to avoid die() if check fails
	 */
	public static function checkFileAccessForInclusion($filepath, $dieOnFail = true)
	{
		$unsafeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (stripos($realfilepath, $rootdirpath) !== 0 || in_array($filePathParts[0], $unsafeDirectories)) {
			if ($dieOnFail) {
				\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
				throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
			}

			return false;
		}
		return true;
	}

	/**
	 * Function to check the file access is made within web root directory.
	 *
	 * @param string File path to check
	 * @param bool False to avoid die() if check fails
	 */
	public static function checkFileAccess($filepath, $dieOnFail = true)
	{
		// Set the base directory to compare with
		$use_root_directory = \AppConfig::main('root_directory');
		if (empty($use_root_directory)) {
			$use_root_directory = realpath(__DIR__ . '/../../.');
		}

		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', $use_root_directory);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		if (stripos($realfilepath, $rootdirpath) !== 0) {
			if ($dieOnFail) {
				\App\Log::error(__METHOD__ . '(' . $filepath . ') - Sorry! Attempt to access restricted file. realfilepath: ' . print_r($realfilepath, true));
				throw new \App\Exceptions\AppException('Sorry! Attempt to access restricted file.');
			}

			return false;
		}
		return true;
	}

	/**
	 * Check if table is present in database.
	 *
	 * @param string tablename to check
	 */
	public static function checkTable($tableName)
	{
		return \App\Db::getInstance()->isTableExists($tableName);
	}

	/**
	 * Create table (supressing failure).
	 *
	 * @param string tablename to create
	 * @param string table creation criteria like '(columnname columntype, ....)'
	 * @param string Optional suffix to add during table creation
	 * <br />
	 * will be appended to CREATE TABLE $tablename SQL
	 */
	public static function createTable($tablename, $criteria, $suffixTableMeta = false)
	{
		$adb = \PearDatabase::getInstance();

		$org_dieOnError = $adb->dieOnError;
		$adb->dieOnError = false;
		$sql = 'CREATE TABLE ' . $adb->quote($tablename, false) . ' ' . $criteria;
		if ($suffixTableMeta !== false) {
			if ($suffixTableMeta === true) {
				if ($adb->isMySQL()) {
					$suffixTableMeta = ' ENGINE=InnoDB DEFAULT CHARSET=utf8';
				} else {
				}
			}
			$sql .= $suffixTableMeta;
		}
		$adb->query($sql);
		$adb->dieOnError = $org_dieOnError;
	}

	/**
	 * Add column to existing table.
	 *
	 * @param string       $tableName  to alter
	 * @param string       $columnName to add
	 * @param array|string $criteria   ([\yii\db\Schema::TYPE_STRING, 1024] | string(1024))
	 */
	public static function addColumn($tableName, $columnName, $criteria)
	{
		$db = \App\Db::getInstance();
		$tableSchema = $db->getSchema()->getTableSchema($tableName, true);
		if (is_null($tableSchema->getColumn((string) $columnName))) {
			if (is_array($criteria)) {
				$criteria = $db->getSchema()->createColumnSchemaBuilder($criteria[0], $criteria[1]);
			}
			$db->createCommand()->addColumn($tableName, $columnName, $criteria)->execute();
		}
	}

	/**
	 * Get SQL query.
	 *
	 * @param string SQL query statement
	 */
	public static function executeQuery($sqlquery, $supressdie = false)
	{
		$adb = \PearDatabase::getInstance();
		$old_dieOnError = $adb->dieOnError;

		if ($supressdie) {
			$adb->dieOnError = false;
		}

		$adb->pquery($sqlquery, []);

		$adb->dieOnError = $old_dieOnError;
	}

	/**
	 * Get CREATE SQL for given table.
	 *
	 * @param string tablename for which CREATE SQL is requried
	 */
	public static function createTableSql($tablename)
	{
		$adb = \PearDatabase::getInstance();
		$result = $adb->query("SHOW CREATE TABLE $tablename");
		$createTable = $adb->fetchArray($result);
		return \App\Purifier::decodeHtml($createTable['Create Table']);
	}

	/**
	 * Check if the given SQL is a CREATE statement.
	 *
	 * @param string SQL String
	 */
	public static function isCreateSql($sql)
	{
		if (preg_match('/(CREATE TABLE)/', strtoupper($sql))) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the given SQL is destructive (DELETE's DATA).
	 *
	 * @param string SQL String
	 */
	public static function isDestructiveSql($sql)
	{
		if (preg_match('/(DROP TABLE)|(DROP COLUMN)|(DELETE FROM)/', strtoupper($sql))) {
			return true;
		}
		return false;
	}
}
