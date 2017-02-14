<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Install_Utils_Model
{

	/**
	 * Function that provides default configuration based on installer setup
	 * @return <Array>
	 */
	public static function getDefaultPreInstallParameters()
	{
		return [
			'db_hostname' => 'localhost',
			'db_username' => '',
			'db_password' => '',
			'db_name' => '',
			'admin_name' => 'admin',
			'admin_lastname' => 'Administrator',
			'admin_password' => '',
			'admin_email' => '',
		];
	}

	/**
	 * Returns list of currencies
	 * @return <Array>
	 */
	public static function getCurrencyList()
	{
		require_once 'install/models/Currencies.php';
		return $currencies;
	}

	/**
	 * Returns list of industry
	 * @return array
	 */
	public static function getIndustryList()
	{
		return require 'install/models/Industry.php';
	}

	/**
	 * Function checks if its mysql type
	 * @param type $dbType
	 * @return type
	 */
	static function isMySQL($dbType)
	{
		return (stripos($dbType, 'mysql') === 0);
	}

	/**
	 * Function checks the database connection
	 * @param string $db_type
	 * @param string $db_hostname
	 * @param string $db_username
	 * @param string $db_password
	 * @param string $db_name
	 * @param string $create_db
	 * @param string $create_utf8_db
	 * @param string $root_user
	 * @param string $root_password
	 * @return <Array>
	 */
	public static function checkDbConnection(Vtiger_Request $request)
	{
		$create_db = false;
		$createDB = $request->get('create_db');
		if ($createDB == 'on') {
			$root_user = $request->get('db_username');
			$root_password = $request->getRaw('db_password');
			$create_db = true;
		}
		$db_type = $request->get('db_type');
		$db_hostname = $request->get('db_hostname');
		$db_username = $request->get('db_username');
		$db_password = $request->getRaw('db_password');
		$db_name = $request->get('db_name');
		$create_utf8_db = true;

		$db_type_status = false; // is there a db type?
		$db_server_status = false; // does the db server connection exist?
		$db_creation_failed = false; // did we try to create a database and fail?
		$db_exist_status = false; // does the database exist?
		$db_utf8_support = false; // does the database support utf8?
		//Checking for database connection parameters
		if ($db_type) {
			$conn = false;
			$pdoException = '';
			try {
				$dsn = $db_type . ':host=' . $db_hostname . ';charset=utf8;port=' . $request->get('db_port');
				$conn = new PDO($dsn, $db_username, $db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
			} catch (PDOException $e) {
				$pdoException = $e->getMessage();
			}
			$db_type_status = true;
			if ($conn) {
				$db_server_status = true;
				if (self::isMySQL($db_type)) {
					$stmt = $conn->query("SHOW VARIABLES LIKE 'version'");
					$res = $stmt->fetch(PDO::FETCH_ASSOC);
					$mysql_server_version = $res['Value'];
				}
				if ($create_db) {
					// drop the current database if it exists
					$stmt = $conn->query("SHOW DATABASES LIKE '$db_name'");
					if ($stmt->rowCount() != 0) {
						$conn->query("DROP DATABASE `$db_name`");
					}

					// create the new database
					$db_creation_failed = true;

					$query = "CREATE DATABASE " . $db_name;
					if ($create_utf8_db == 'true') {
						if (self::isMySQL($db_type))
							$query .= ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci';
						$db_utf8_support = true;
					}
					if ($conn->query($query)) {
						$db_creation_failed = false;
					}
				}
				$stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
				if ($stmt->rowCount() == 1) {
					$db_exist_status = true;
				}
			}
		}
		$dbCheckResult = array();
		$dbCheckResult['db_utf8_support'] = $db_utf8_support;

		$error_msg = '';
		$error_msg_info = '';

		if (!$db_type_status || !$db_server_status) {
			$error_msg = \App\Language::translate('ERR_DATABASE_CONNECTION_FAILED', 'Install') . '. ' . \App\Language::translate('ERR_INVALID_MYSQL_PARAMETERS', 'Install');
			$error_msg_info = \App\Language::translate('MSG_LIST_REASONS', 'Install') . ':<br>
					-  ' . \App\Language::translate('MSG_DB_PARAMETERS_INVALID', 'Install') . '
					<br>-  ' . \App\Language::translate('MSG_DB_USER_NOT_AUTHORIZED', 'Install');
			$error_msg_info .= "<br><br>$pdoException";
		} elseif (self::isMySQL($db_type) && $mysql_server_version < 4.1) {
			$error_msg = $mysql_server_version . ' -> ' . \App\Language::translate('ERR_INVALID_MYSQL_VERSION', 'Install');
		} elseif ($db_creation_failed) {
			$error_msg = \App\Language::translate('ERR_UNABLE_CREATE_DATABASE', 'Install') . ' ' . $db_name;
			$error_msg_info = \App\Language::translate('MSG_DB_ROOT_USER_NOT_AUTHORIZED', 'Install');
		} elseif (!$db_exist_status) {
			$error_msg = $db_name . ' -> ' . \App\Language::translate('ERR_DB_NOT_FOUND', 'Install');
		} else {
			$dbCheckResult['flag'] = true;
			return $dbCheckResult;
		}
		$dbCheckResult['flag'] = false;
		$dbCheckResult['error_msg'] = $error_msg;
		$dbCheckResult['error_msg_info'] = $error_msg_info;
		return $dbCheckResult;
	}

	public static function getLanguages()
	{
		$dir = 'languages/';
		$ffs = scandir($dir);
		$langs = array();
		foreach ($ffs as $ff) {
			if ($ff != '.' && $ff != '..') {
				if (file_exists($dir . $ff . '/Install.php')) {
					$langs[$ff] = \App\Language::translate('LANGNAME', 'Install', $ff);
				}
			}
		}
		return $langs;
	}
}
