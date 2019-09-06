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
		$this->db = \App\Db::getInstance();
		$this->initializeDatabase($this->sql_directory, ['scheme', 'data']);
		if ($_SESSION['installation_success'] ?? false) {
			$this->createConfigFiles();
			$this->setDefaultUsersAccess();
			$this->db->createCommand()
				->update('vtiger_currency_info', [
					'currency_name' => $_SESSION['config_file_info']['currency_name'],
					'currency_code' => $_SESSION['config_file_info']['currency_code'],
					'currency_symbol' => $_SESSION['config_file_info']['currency_symbol']
				])->execute();
			$this->db->createCommand()
				->update('vtiger_version', [
					'current_version' => \App\Version::get(),
					'old_version' => \App\Version::get()
				])->execute();
			// recalculate all sharing rules for users
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
	}

	public function initializeDatabase($location, $filesName = [])
	{
		try {
			$return = false;
			$this->db->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
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
			$createQuery = substr_count($splitQueries, 'CREATE TABLE');
			$insertQuery = substr_count($splitQueries, 'INSERT INTO');
			$alterQuery = substr_count($splitQueries, 'ALTER TABLE');
			$executedQuery = 0;
			$queries = $this->splitQueries($splitQueries);
			foreach ($queries as $query) {
				// Trim any whitespace.
				$query = trim($query);
				if (!empty($query) && ('#' != $query[0]) && ('-' != $query[0])) {
					$this->db->createCommand($query)->execute();
					++$executedQuery;
				}
			}
			\App\Log::info("create_query: $createQuery | insert_query: $insertQuery | alter_query: $alterQuery | executed_query: $executedQuery");
			$_SESSION['installation_success'] = $createQuery && $executedQuery;
		} catch (Throwable $e) {
			$return = false;
			\App\Log::error($e->__toString());
			$_SESSION['installation_success'] = false;
		} finally {
			$this->db->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();
		}
		return ['status' => $return, 'create' => $createQuery, 'insert' => $insertQuery, 'alter' => $alterQuery, 'executed' => $executedQuery];
	}

	/**
	 * Function creates default user's Role, Profiles.
	 */
	public function setDefaultUsersAccess()
	{
		if (empty($_SESSION['config_file_info']['user_name'])) {
			return false;
		}
		$this->db->createCommand()
			->update('vtiger_users', [
				'user_name' => $_SESSION['config_file_info']['user_name'],
				'date_format' => $_SESSION['config_file_info']['dateformat'],
				'time_zone' => $_SESSION['config_file_info']['default_timezone'],
				'first_name' => $_SESSION['config_file_info']['firstname'],
				'last_name' => $_SESSION['config_file_info']['lastname'],
				'email1' => $_SESSION['config_file_info']['admin_email'],
				'accesskey' => \App\Encryption::generatePassword(20, 'lbn'),
				'language' => \App\Language::DEFAULT_LANG,
			])->execute();
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
		$query = preg_replace("/\n\\#[^\n]*/", '', "\n" . $query);
		// Remove PostgreSQL comment lines.
		$query = preg_replace("/\n\\--[^\n]*/", '', "\n" . $query);
		// Find function
		$funct = explode('CREATE || REPLACE FUNCTION', $query);
		// Save sql before function and parse it
		$query = $funct[0];

		// Parse the schema file to break up queries.
		for ($i = 0; $i < \strlen($query) - 1; ++$i) {
			if (';' == $query[$i] && !$in_string) {
				$queries[] = substr($query, 0, $i);
				$query = substr($query, $i + 1);
				$i = 0;
			}
			if ($in_string && ($query[$i] == $in_string) && '\\' != $buffer[1]) {
				$in_string = false;
			} elseif (!$in_string && ('"' == $query[$i] || "'" == $query[$i]) && (!isset($buffer[0]) || '\\' != $buffer[0])) {
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
		$countFunct = \count($funct);
		for ($f = 1; $f < $countFunct; ++$f) {
			$queries[] = 'CREATE || REPLACE FUNCTION ' . $funct[$f];
		}
		return $queries;
	}

	/**
	 * Set company details.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function setCompanyDetails(App\Request $request)
	{
		if (!($_SESSION['installation_success'] ?? false)) {
			return;
		}
		$details = [];
		foreach (Settings_Companies_Module_Model::getColumnNames() as $name) {
			if ($request->has("company_{$name}")) {
				$details[$name] = $request->getByType("company_{$name}", 'Text');
			}
		}
		$companies = $this->db->createCommand()->update('s_#__companies', $details);
		$multiCompany = $this->db->createCommand()->update('u_#__multicompany', ['company_name' => $details['name']]);
		if (!$details || !$companies->execute() || !$multiCompany->execute()) {
			throw new \App\Exceptions\AppException('No company data', 406);
		}
	}

	/**
	 * Create config files.
	 */
	private function createConfigFiles()
	{
		$skip = ['main', 'db', 'performance', 'debug', 'security', 'module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}
		$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
		if (!is_dir($dirPath)) {
			mkdir($dirPath);
		}
		$dataReader = (new \App\Db\Query())->select(['name'])->from('vtiger_tab')->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			if (file_exists($filePath)) {
				(new \App\ConfigFile('module', $moduleName))->create();
			}
		}
		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		foreach ($componentsData as $component => $data) {
			(new \App\ConfigFile('component', $component))->create();
		}
	}
}
