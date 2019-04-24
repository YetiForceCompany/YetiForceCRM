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
	 * @param mixed $value
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
	 * @param mixed $prefix
	 * @param mixed $count
	 * @param mixed $suffix
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
	 * @param mixed $filepath
	 * @param mixed $dieOnFail
	 */
	public static function checkFileAccessForInclusion($filepath, $dieOnFail = true)
	{
		$unsafeDirectories = ['storage', 'cache', 'test'];
		$realfilepath = realpath($filepath);

		/** Replace all \\ with \ first */
		$realfilepath = str_replace('\\\\', '\\', $realfilepath);
		$rootdirpath = str_replace('\\\\', '\\', ROOT_DIRECTORY . \DIRECTORY_SEPARATOR);

		/** Replace all \ with / now */
		$realfilepath = str_replace('\\', '/', $realfilepath);
		$rootdirpath = str_replace('\\', '/', $rootdirpath);

		$relativeFilePath = str_replace($rootdirpath, '', $realfilepath);
		$filePathParts = explode('/', $relativeFilePath);

		if (0 !== stripos($realfilepath, $rootdirpath) || in_array($filePathParts[0], $unsafeDirectories)) {
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
	 * @param mixed $filepath
	 * @param mixed $dieOnFail
	 */
	public static function checkFileAccess($filepath, $dieOnFail = true)
	{
		// Set the base directory to compare with
		$use_root_directory = \App\Config::main('root_directory');
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

		if (0 !== stripos($realfilepath, $rootdirpath)) {
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
	 * @param mixed $tableName
	 */
	public static function checkTable($tableName)
	{
		return \App\Db::getInstance()->isTableExists($tableName);
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
		if (null === $tableSchema->getColumn((string) $columnName)) {
			if (is_array($criteria)) {
				$criteria = $db->getSchema()->createColumnSchemaBuilder($criteria[0], $criteria[1]);
			}
			$db->createCommand()->addColumn($tableName, $columnName, $criteria)->execute();
		}
	}

	/**
	 * Check if the given SQL is a CREATE statement.
	 *
	 * @param string SQL String
	 * @param mixed $sql
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
	 * @param mixed $sql
	 */
	public static function isDestructiveSql($sql)
	{
		if (preg_match('/(DROP TABLE)|(DROP COLUMN)|(DELETE FROM)/', strtoupper($sql))) {
			return true;
		}
		return false;
	}
}
