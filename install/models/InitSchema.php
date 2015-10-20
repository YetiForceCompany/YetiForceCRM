<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Install_InitSchema_Model {

	protected $sql_directory = 'install/install_schema/';
	protected $migration_schema = 'install/migrate_schema/';
	protected $db = false;

	function __construct($db = '') {
		$this->db = $db;
	}

	/**
	 * Function starts applying schema changes
	 */
	public function initialize() {
		global $YetiForce_current_version;
		$this->initializeDatabase($this->sql_directory, array('scheme', 'data'));
		$this->setDefaultUsersAccess();
		$currencyName = $_SESSION['config_file_info']['currency_name'];
		$currencyCode = $_SESSION['config_file_info']['currency_code'];
		$currencySymbol = $_SESSION['config_file_info']['currency_symbol'];
		$this->db->pquery("UPDATE vtiger_currency_info SET currency_name = ?, currency_code = ?, currency_symbol = ?", [$currencyName, $currencyCode, $currencySymbol]);
		$this->db->pquery("UPDATE vtiger_version SET `current_version` = ?, `old_version` = ? ;", [$YetiForce_current_version, $YetiForce_current_version]);

		// recalculate all sharing rules for users
		vimport('~include/utils/UserInfoUtil.php');
		RecalculateSharingRules();
	}

	public function initializeDatabase($location, $filesName = array()) {
		$this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
		if (!$filesName) {
			echo 'No files';
			return false;
		}
		$splitQueries = '';
		foreach ($filesName AS $name) {
			$sql_file = $location . $name . '.sql';
			$return = true;
			if (!($fileBuffer = file_get_contents($sql_file))) {
				echo 'Invalid file: ' . $sql_file;
				return false;
			}

			$splitQueries .= $fileBuffer;
		}
		$create_query += substr_count($splitQueries, 'CREATE TABLE');
		$insert_query += substr_count($splitQueries, 'INSERT INTO');
		$alter_query += substr_count($splitQueries, 'ALTER TABLE');
		$executed_query = 0;
		$queries = $this->_splitQueries($splitQueries);
		foreach ($queries as $query) {
			// Trim any whitespace.
			$query = trim($query);
			if (!empty($query) && ($query{0} != '#') && ($query{0} != '-')) {
				try {
					$this->db->query($query);
					$executed_query++;
				} catch (RuntimeException $e) {
					echo $e->getMessage();
					$return = false;
				}
			}
		}
		$this->db->query('SET FOREIGN_KEY_CHECKS = 1;');
		return array('status' => $return, 'create' => $create_query, 'insert' => $insert_query, 'alter' => $alter_query, 'executed' => $executed_query);
	}

	/**
	 * Function creates default user's Role, Profiles
	 */
	public function setDefaultUsersAccess() {
		$adminPassword = $_SESSION['config_file_info']['password'];
		$userDateFormat = $_SESSION['config_file_info']['dateformat'];
		$userTimeZone = $_SESSION['config_file_info']['timezone'];
		$userFirstName = $_SESSION['config_file_info']['firstname'];
		$userLastName = $_SESSION['config_file_info']['lastname'];
		$adminEmail = $_SESSION['config_file_info']['admin_email'];

		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_users SET date_format = ?, time_zone = ?, first_name = ?, last_name = ?, email1 = ?, accesskey = ?, language = ?", array($userDateFormat, $userTimeZone, $userFirstName, $userLastName, $adminEmail, vtws_generateRandomAccessKey(16), $_SESSION['default_language']));

		$newUser = new Users();
		$newUser->retrieve_entity_info(1, 'Users');
		$newUser->change_password('admin', $adminPassword, false);
		require_once('modules/Users/CreateUserPrivilegeFile.php');
		createUserPrivilegesfile(1);
	}

	public function _splitQueries($query) {
		$buffer = array();
		$queries = array();
		$in_string = false;

		// Trim any whitespace.
		$query = trim($query);
		// Remove comment lines.
		$query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);
		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);
		// Find function
		$funct = explode('CREATE OR REPLACE FUNCTION', $query);
		// Save sql before function and parse it
		$query = $funct[0];

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($query) - 1; $i++) {
			if ($query[$i] == ";" && !$in_string) {
				$queries[] = substr($query, 0, $i);
				$query = substr($query, $i + 1);
				$i = 0;
			}
			if ($in_string && ($query[$i] == $in_string) && $buffer[1] != "\\") {
				$in_string = false;
			} elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
				$in_string = $query[$i];
			}
			if (isset($buffer[1])) {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $query[$i];
		}
		// If the is anything left over, add it to the queries.
		if (!empty($query)) {
			$queries[] = $query;
		}
		// Add function part as is
		for ($f = 1; $f < count($funct); $f++) {
			$queries[] = 'CREATE OR REPLACE FUNCTION ' . $funct[$f];
		}
		return $queries;
	}

	public function getMigrationSchemaList() {
		$dir = $this->migration_schema;
		$schemaList = array();
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if (strpos($object->getFilename(), '.php') !== false) {
				include_once $this->migration_schema . $object->getFilename();
				$fileName = str_replace('.php', "", $object->getFilename());
				$migrationObject = new $fileName;
				$schemaList[$fileName] = $migrationObject->name;
			}
		}
		return $schemaList;
	}

	public function executeMigrationSchema($system, $userName, $source) {
		include_once $this->migration_schema . $system . '.php';
		$migrationObject = new $system;
		Vtiger_Access::syncSharingAccess();
		$migrationObject->preProcess($userName, $source);
		$migrationObject->process();
		$return = $migrationObject->postProcess();
		Vtiger_Access::syncSharingAccess();
		Vtiger_Deprecated::createModuleMetaFile();
		return $return;
	}

	public function addMigrationLog($text, $type = 'success') {
		$logUrl = 'install/models/logs.txt';
		$logText = "$type - $text";
		file_put_contents($logUrl, $logText . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	public function setProgressBar($num) {
		$logUrl = 'install/models/progressbar.php';
		$content = "<?php $progress = $num;";
		file_put_contents($logUrl, $content);
	}

	public function createConfig($source_directory, $username, $password, $system) {
		if (substr($source_directory, -1) != '/') {
			$source_directory = $source_directory . '/';
		}

		$config_directory = $source_directory . 'config.inc.php';
		if (!file_exists($config_directory)) {
			return array('result' => false, 'text' => 'LBL_ERROR_NO_CONFIG');
		}

		if (!file_exists($source_directory . 'vtigerversion.php')) {
			return array('result' => false, 'text' => 'LBL_ERROR_NO_CONFIG');
		}

		include_once $this->migration_schema . $system . '.php';
		$migrationObject = new $system;
		include_once $source_directory . 'vtigerversion.php';
		if ($vtiger_current_version != $migrationObject->version) {
			return array('result' => false, 'text' => 'LBL_ERROR_WRONG_VERSION');
		}

		include_once $config_directory;
		if (!isset($root_directory)) {
			return array('result' => false, 'text' => 'LBL_ERROR_EMPTY_CONFIG');
		}
		$rootDirectory = getcwd();
		if (substr($rootDirectory, -1) != '/') {
			$rootDirectory = $rootDirectory . '/';
		}
		$webRoot = ($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
		$webRoot .= $_SERVER["REQUEST_URI"];
		$webRoot = str_replace("install/Install.php", "", $webRoot);
		$webRoot = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? "https://" : "http://") . $webRoot;

		$configFileParameters = array();
		$configFileParameters['root_directory'] = $rootDirectory;
		$configFileParameters['site_URL'] = $webRoot;
		$configFileParameters['db_hostname'] = $dbconfig['db_server'] . ':' . $dbconfig['db_port'];
		$configFileParameters['db_username'] = $dbconfig['db_username'];
		$configFileParameters['db_password'] = $dbconfig['db_password'];
		$configFileParameters['db_name'] = $dbconfig['db_name'];
		$configFileParameters['db_type'] = $dbconfig['db_type'];
		$configFileParameters['admin_email'] = $HELPDESK_SUPPORT_EMAIL_ID;
		$configFileParameters['currency_name'] = $currency_name;
		$configFileParameters['vt_charset'] = $default_charset;
		$configFileParameters['default_language'] = $default_language;
		$configFileParameters['timezone'] = $default_timezone;

		$configFile = new Install_ConfigFileUtils_Model($configFileParameters);
		$configFile->createConfigFile();
		return array('result' => true);
	}

	public function copyFiles($source, $dest) {
		mkdir($dest, 0755);
		foreach (
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
		) {
			if ($item->isDir()) {
				mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			} else {
				copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			}
		}
	}

	public function deleteFiles($files = array()) {
		if (!is_array($files)) {
			$files = array($files);
		}
		foreach ($files as $file) {
			$this->deleteDirFile($file);
		}
	}

	public function deleteDirFile($src) {
		global $root_directory, $log;
		if ($root_directory && strpos($src, $root_directory) === FALSE) {
			$src = $root_directory . $src;
		}
		if (!file_exists($src) || !$root_directory)
			return;
		@chmod($src, 0777);
		$log->debug("Exiting VT620_to_YT::testest(" . $src . ") method ...");
		if (is_dir($src)) {
			$dir = new DirectoryIterator($src);
			foreach ($dir as $fileinfo) {
				if (!$fileinfo->isDot()) {
					if ($fileinfo->isDir()) {
						$log->debug("Exiting VT620_to_YT::testest 22(" . $fileinfo->getPathname() . ") method ...");
						$this->deleteDirFile($fileinfo->getPathname());
						rmdir($fileinfo->getPathname());
					} else {
						unlink($fileinfo->getPathname());
					}
				}
			}
		} else {
			unlink($src);
		}
	}

}
