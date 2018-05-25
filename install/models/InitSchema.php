<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Install_InitSchema_Model
{
	protected $sql_directory = 'install/install_schema/';
	protected $migration_schema = 'install/migrate_schema/';

	/**
	 * Function starts applying schema changes.
	 */
	public function initialize()
	{
		$this->db = PearDatabase::getInstance();
		$this->initializeDatabase($this->sql_directory, ['scheme', 'data']);
		$this->setDefaultUsersAccess();
		$currencyName = $_SESSION['config_file_info']['currency_name'];
		$currencyCode = $_SESSION['config_file_info']['currency_code'];
		$currencySymbol = $_SESSION['config_file_info']['currency_symbol'];
		$this->db->pquery('UPDATE vtiger_currency_info SET currency_name = ?, currency_code = ?, currency_symbol = ?', [$currencyName, $currencyCode, $currencySymbol]);
		$this->db->pquery('UPDATE vtiger_version SET `current_version` = ?, `old_version` = ? ;', [\App\Version::get(), \App\Version::get()]);

		// recalculate all sharing rules for users
		require_once 'include/database/PearDatabase.php';
		require_once 'include/utils/CommonUtils.php';
		require_once 'include/fields/DateTimeField.php';
		require_once 'include/fields/DateTimeRange.php';
		require_once 'include/fields/CurrencyField.php';
		require_once 'include/CRMEntity.php';
		require_once 'modules/Vtiger/CRMEntity.php';
		require_once 'include/runtime/Cache.php';
		require_once 'modules/Vtiger/helpers/Util.php';
		require_once 'modules/PickList/DependentPickListUtils.php';
		require_once 'modules/Users/Users.php';
		require_once 'include/Webservices/Utils.php';
		\App\UserPrivilegesFile::recalculateAll();
		\App\Cache::clear();
		\App\Cache::clearOpcache();
	}

	public function initializeDatabase($location, $filesName = [])
	{
		try {
			$return = false;
			$this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
			if (!$filesName) {
				throw new \App\Exceptions\AppException('No files', 405);
			}
			$splitQueries = '';
			foreach ($filesName as $name) {
				$sql_file = $location . $name . '.sql';
				$return = true;
				if (!($fileBuffer = file_get_contents($sql_file))) {
					throw new \App\Exceptions\AppException('Invalid file: ' . $sql_file, 405);
				}
				$splitQueries .= $fileBuffer;
			}
			$create_query = substr_count($splitQueries, 'CREATE TABLE');
			$insert_query = substr_count($splitQueries, 'INSERT INTO');
			$alter_query = substr_count($splitQueries, 'ALTER TABLE');
			$executed_query = 0;
			$queries = $this->splitQueries($splitQueries);
			foreach ($queries as $query) {
				// Trim any whitespace.
				$query = trim($query);
				if (!empty($query) && ($query[0] != '#') && ($query[0] != '-')) {
					try {
						$this->db->query($query);
						++$executed_query;
					} catch (RuntimeException $e) {
						throw $e;
					}
				}
			}
			$this->db->query('SET FOREIGN_KEY_CHECKS = 1;');
			\App\Log::info("create_query: $create_query | insert_query: $insert_query | alter_query: $alter_query | executed_query: $executed_query");
			$_SESSION['instalation_success'] = $create_query && $executed_query;
		} catch (Throwable $e) {
			$return = false;
			\App\Log::error($e->__toString());
			$_SESSION['instalation_success'] = false;
		}
		return ['status' => $return, 'create' => $create_query, 'insert' => $insert_query, 'alter' => $alter_query, 'executed' => $executed_query];
	}

	/**
	 * Function creates default user's Role, Profiles.
	 */
	public function setDefaultUsersAccess()
	{
		$this->db->update('vtiger_users', [
				'user_name' => $_SESSION['config_file_info']['user_name'],
				'date_format' => $_SESSION['config_file_info']['dateformat'],
				'time_zone' => $_SESSION['config_file_info']['timezone'],
				'first_name' => $_SESSION['config_file_info']['firstname'],
				'last_name' => $_SESSION['config_file_info']['lastname'],
				'email1' => $_SESSION['config_file_info']['admin_email'],
				'accesskey' => \App\Encryption::generatePassword(20, 'lbn'),
				'language' => $_SESSION['default_language'],
			]
		);
		$userRecordModel = Users_Record_Model::getInstanceById(1, 'Users');
		$userRecordModel->set('user_password', $_SESSION['config_file_info']['password']);
		$userRecordModel->save();
		require_once 'app/UserPrivilegesFile.php';
		\App\UserPrivilegesFile::createUserPrivilegesfile(1);
	}

	public function splitQueries($query)
	{
		$buffer = [];
		$queries = [];
		$in_string = false;

		// Trim any whitespace.
		$query = trim($query);
		// Remove comment lines.
		$query = preg_replace("/\n\#[^\n]*/", '', "\n" . $query);
		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\--[^\n]*/", '', "\n" . $query);
		// Find function
		$funct = explode('CREATE || REPLACE FUNCTION', $query);
		// Save sql before function and parse it
		$query = $funct[0];

		// Parse the schema file to break up queries.
		for ($i = 0; $i < strlen($query) - 1; ++$i) {
			if ($query[$i] == ';' && !$in_string) {
				$queries[] = substr($query, 0, $i);
				$query = substr($query, $i + 1);
				$i = 0;
			}
			if ($in_string && ($query[$i] == $in_string) && $buffer[1] != '\\') {
				$in_string = false;
			} elseif (!$in_string && ($query[$i] == '"' || $query[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != '\\')) {
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
		$countFunct = count($funct);
		for ($f = 1; $f < $countFunct; ++$f) {
			$queries[] = 'CREATE || REPLACE FUNCTION ' . $funct[$f];
		}
		return $queries;
	}

	public function getMigrationSchemaList()
	{
		$dir = $this->migration_schema;
		$schemaList = [];
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if (strpos($object->getFilename(), '.php') !== false) {
				include_once $this->migration_schema . $object->getFilename();
				$fileName = str_replace('.php', '', $object->getFilename());
				$migrationObject = new $fileName();
				$schemaList[$fileName] = $migrationObject->name;
			}
		}
		return $schemaList;
	}

	/**
	 * Set company details.
	 *
	 * @param \App\Request $request
	 */
	public function setCompanyDetails(\App\Request $request)
	{
		$details = [];
		foreach ($request->getAll() as $key => $value) {
			if (strpos($key, 'company_') === 0) {
				$details[str_replace('company_', '', $key)] = $value;
			}
		}
		$this->db->update('s_yf_companies', $details);
	}
}
