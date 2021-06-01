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
	 * Function that provides default configuration based on installer setup.
	 *
	 * @return array
	 */
	public static function getDefaultPreInstallParameters(): array
	{
		return [
			'db_server' => 'localhost',
			'db_username' => '',
			'db_password' => '',
			'db_name' => 'yetiforce',
			'admin_name' => 'admin' . random_int(10000, 99999),
			'admin_firstname' => 'Yeti',
			'admin_lastname' => 'Administrator',
			'admin_password' => '',
			'admin_email' => '',
		];
	}

	/**
	 * Returns list of currencies.
	 *
	 * @return <Array>
	 */
	public static function getCurrencyList()
	{
		require_once 'install/models/Currencies.php';

		return $currencies;
	}

	/**
	 * Returns list of industry.
	 *
	 * @return array
	 */
	public static function getIndustryList()
	{
		return require 'install/models/Industry.php';
	}

	/**
	 * Returns list of countries.
	 *
	 * @return array
	 */
	public static function getCountryList()
	{
		return require 'install/models/Country.php';
	}

	/**
	 * Function checks the database connection.
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public static function checkDbConnection(array $config)
	{
		$db_type = $config['db_type'];
		$db_server = $config['db_server'];
		$db_username = $config['db_username'];
		$db_password = $config['db_password'];
		$db_name = $config['db_name'];
		$dbPort = $config['db_port'];

		$db_type_status = false; // is there a db type?
		$db_server_status = false; // does the db server connection exist?
		$db_exist_status = false; // does the database exist?
		$db_utf8_support = false; // does the database support utf8?
		//Checking for database connection parameters
		if ($db_type) {
			$conn = false;
			$pdoException = '';
			try {
				\App\Db::setConfig([
					'dsn' => $db_type . ':host=' . $db_server . ';charset=utf8;port=' . $dbPort,
					'host' => $db_server,
					'port' => $dbPort,
					'dbName' => $db_name,
					'tablePrefix' => 'yf_',
					'username' => $db_username,
					'password' => $db_password,
					'charset' => 'utf8',
					'attributes' => [
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
					],
				]);
				$db = \App\Db::getInstance();
				$conn = $db->getMasterPdo();
			} catch (\Throwable $e) {
				$pdoException = $e->getMessage();
			}
			$db_type_status = true;
			if ($conn) {
				$db_server_status = true;
				if (\App\Validator::isMySQL($db_type)) {
					$stmt = $conn->query("SHOW VARIABLES LIKE 'version'");
					$res = $stmt->fetch(PDO::FETCH_ASSOC);
					$mysql_server_version = $res['Value'];
				}
				$stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
				if (1 == $stmt->rowCount()) {
					$db_exist_status = true;
				}
			}
		}
		$dbCheckResult = [];
		$dbCheckResult['db_utf8_support'] = $db_utf8_support;

		$error_msg_info = '';

		if (!$db_type_status || !$db_server_status) {
			$error_msg = \App\Language::translate('ERR_DATABASE_CONNECTION_FAILED', 'Install') . '. ' . \App\Language::translate('ERR_INVALID_MYSQL_PARAMETERS', 'Install');
			$error_msg_info = \App\Language::translate('MSG_LIST_REASONS', 'Install') . ':<br />
					-  ' . \App\Language::translate('MSG_DB_PARAMETERS_INVALID', 'Install') . '
					<br />-  ' . \App\Language::translate('MSG_DB_USER_NOT_AUTHORIZED', 'Install');
			$error_msg_info .= "<br /><br />$pdoException";
		} elseif (\App\Validator::isMySQL($db_type) && version_compare($mysql_server_version, '5.1', '<')) {
			$error_msg = $mysql_server_version . ' -> ' . \App\Language::translate('ERR_INVALID_MYSQL_VERSION', 'Install');
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

	/**
	 * Gets languages for install.
	 *
	 * @return array
	 */
	public static function getLanguages()
	{
		$languages = [];
		foreach ((new \DirectoryIterator('install/languages/')) as $item) {
			if ($item->isDir() && !$item->isDot() && file_exists($item->getPathname() . DIRECTORY_SEPARATOR . 'Install.json')) {
				$languages[$item->getBasename()] = [
					'displayName' => \App\Language::getDisplayName($item->getBasename()),
					'region' => strtolower(\App\Language::getRegion($item->getBasename())),
				];
			}
		}
		return $languages;
	}

	/**
	 * Clean data configuration.
	 */
	public static function cleanConfiguration()
	{
		\vtlib\Functions::recurseDelete('config/Db.php');
		\vtlib\Functions::recurseDelete('config/Main.php');
		if (isset($_SESSION['config_file_info'])) {
			unset($_SESSION['config_file_info']);
		}
		$className = '\Config\Main';
		if (\class_exists($className)) {
			foreach ((new \ReflectionClass($className))->getStaticProperties() as $name => $value) {
				$className::${$name} = null;
			}
		}
	}
}
