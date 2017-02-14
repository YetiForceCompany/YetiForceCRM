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
 * Provides few utility functions
 * @package vtlib
 */
class Utils
{

	protected static $logFileName = 'module.log';

	/**
	 * Check if given value is a number or not
	 * @param mixed String or Integer
	 */
	static function isNumber($value)
	{
		return is_numeric($value) ? intval($value) == $value : false;
	}

	/**
	 * Implode the prefix and suffix as string for given number of times
	 * @param String prefix to use
	 * @param Integer Number of times 
	 * @param String suffix to use (optional)
	 */
	static function implodestr($prefix, $count, $suffix = false)
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
	 * Function to check the file access is made within web root directory as well as is safe for php inclusion
	 * @param String File path to check
	 * @param Boolean False to avoid die() if check fails
	 */
	static function checkFileAccessForInclusion($filepath, $dieOnFail = true)
	{
		$unsafeDirectories = array('storage', 'cache', 'test');
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
				throw new \Exception\AppException('Sorry! Attempt to access restricted file.');
			}
			return false;
		}
		return true;
	}

	/**
	 * Function to check the file access is made within web root directory. 
	 * @param String File path to check
	 * @param Boolean False to avoid die() if check fails
	 */
	static function checkFileAccess($filepath, $dieOnFail = true)
	{
		// Set the base directory to compare with
		$use_root_directory = \AppConfig::main('root_directory');
		if (empty($use_root_directory)) {
			$use_root_directory = realpath(dirname(__FILE__) . '/../../.');
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
				throw new \Exception\AppException('Sorry! Attempt to access restricted file.');
			}
			return false;
		}
		return true;
	}

	/**
	 * Log the debug message 
	 * @param String Log message
	 * @param Boolean true to append end-of-line, false otherwise
	 */
	static function Log($message, $delimit = true)
	{
		$utilsLog = vglobal('tiger_Utils_Log');

		\App\Log::trace($message);
		if (!isset($utilsLog) || $utilsLog === false)
			return;

		echo $message;
		if ($delimit) {
			if (isset($_REQUEST))
				echo "<BR>";
			else
				echo "\n";
		}
	}

	/**
	 * Escape the string to avoid SQL Injection attacks.
	 * @param String Sql statement string
	 */
	static function SQLEscape($value)
	{
		if ($value === null)
			return $value;
		$adb = \PearDatabase::getInstance();
		return $adb->sql_escape_string($value);
	}

	/**
	 * Check if table is present in database
	 * @param String tablename to check
	 */
	static function CheckTable($tableName)
	{
		return \App\Db::getInstance()->isTableExists($tableName);
	}

	/**
	 * Create table (supressing failure)
	 * @param String tablename to create
	 * @param String table creation criteria like '(columnname columntype, ....)' 
	 * @param String Optional suffix to add during table creation
	 * <br>
	 * will be appended to CREATE TABLE $tablename SQL
	 */
	static function CreateTable($tablename, $criteria, $suffixTableMeta = false)
	{
		$adb = \PearDatabase::getInstance();

		$org_dieOnError = $adb->dieOnError;
		$adb->dieOnError = false;
		$sql = "CREATE TABLE " . $adb->quote($tablename, false) . ' ' . $criteria;
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
	 * Add column to existing table
	 * @param string $tableName to alter
	 * @param string $columnName to add
	 * @param array|string $criteria ([\yii\db\Schema::TYPE_STRING, 1024] | string(1024)) 
	 */
	public static function AddColumn($tableName, $columnName, $criteria)
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
	 * Get SQL query
	 * @param String SQL query statement
	 */
	static function ExecuteQuery($sqlquery, $supressdie = false)
	{
		$adb = \PearDatabase::getInstance();
		$old_dieOnError = $adb->dieOnError;

		if ($supressdie)
			$adb->dieOnError = false;

		$adb->pquery($sqlquery, []);

		$adb->dieOnError = $old_dieOnError;
	}

	/**
	 * Get CREATE SQL for given table
	 * @param String tablename for which CREATE SQL is requried
	 */
	static function CreateTableSql($tablename)
	{
		$adb = \PearDatabase::getInstance();

		$result = $adb->query("SHOW CREATE TABLE $tablename");
		$createTable = $adb->fetch_array($result);
		$sql = decode_html($createTable['Create Table']);
		return $sql;
	}

	/**
	 * Check if the given SQL is a CREATE statement
	 * @param String SQL String
	 */
	static function IsCreateSql($sql)
	{
		if (preg_match('/(CREATE TABLE)/', strtoupper($sql))) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the given SQL is destructive (DELETE's DATA)
	 * @param String SQL String
	 */
	static function IsDestructiveSql($sql)
	{
		if (preg_match('/(DROP TABLE)|(DROP COLUMN)|(DELETE FROM)/', strtoupper($sql))) {
			return true;
		}
		return false;
	}

	/**
	 * funtion to log the exception messge to module.log file
	 * @global type $site_URL
	 * @param string $module name of the log file and It should be a alphanumeric string
	 * @param <Exception>/string $exception Massage show in the log ,It should be a string or Exception object 
	 * @param <array> $extra extra massages need to be displayed
	 * @param <boolean> $backtrace flag to enable or disable backtrace in log  
	 * @param <boolean> $request flag to enable or disable request in log
	 */
	static function ModuleLog($module, $mixed, $extra = [])
	{
		if (ALLOW_MODULE_LOGGING) {
			$date = date('Y-m-d H:i:s');
			$log = array(\AppConfig::main('site_URL'), $module, $date);
			if ($mixed instanceof Exception) {
				array_push($log, $mixed->getMessage());
				array_push($log, $mixed->getTraceAsString());
			} else {
				array_push($log, $mixed);
				array_push($log, "");
			}
			if (isset($_REQUEST)) {
				array_push($log, json_encode($_REQUEST));
			} else {
				array_push($log, "");
			};

			if ($extra) {
				if (is_array($extra))
					$extra = json_encode($extra);
				array_push($log, $extra);
			} else {
				array_push($log, "");
			}
			$fileName = self::$logFileName;
			$fp = fopen("cache/logs/$fileName", 'a+');
			fputcsv($fp, $log);
			fclose($fp);
		}
	}
}
