<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Install_Index_view extends Vtiger_View_Controller
{

	protected $debug = false;

	public function loginRequired()
	{
		return false;
	}

	public function setLanguage($request)
	{
		if (!$request->get('lang')) {

			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

			if ('pl' === $lang) {
				$request->set('lang', 'pl_pl');
			} else {
				$request->set('lang', 'en_us');
			}

			return $request;
		}

		return $request;
	}

	public function __construct()
	{
		//Install
		$this->exposeMethod('Step1');
		$this->exposeMethod('Step2');
		$this->exposeMethod('Step3');
		$this->exposeMethod('Step4');
		$this->exposeMethod('Step5');
		$this->exposeMethod('Step6');
		$this->exposeMethod('Step7');
		//Migrate
		$this->exposeMethod('mStep0');
		$this->exposeMethod('mStep1');
		$this->exposeMethod('mStep2');
		$this->exposeMethod('mStep3');
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		date_default_timezone_set('UTC'); // to overcome the pre configuration settings
		// Added to redirect to default module if already installed

		$request->set('module', 'Install');

		$configFileName = 'config/config.inc.php';
		if (is_file($configFileName) && filesize($configFileName) > 10) {
			$defaultModule = vglobal('default_module');
			$defaultModuleInstance = Vtiger_Module_Model::getInstance($defaultModule);
			$defaultView = $defaultModuleInstance->getDefaultViewName();
			header('Location:../index.php?module=' . $defaultModule . '&view=' . $defaultView);
			exit;
		}

		$request = $this->setLanguage($request);

		$_SESSION['default_language'] = $defaultLanguage = ($request->get('lang')) ? $request->get('lang') : 'en_us';
		vglobal('default_language', $defaultLanguage);

		$viewer = new Vtiger_Viewer();
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$viewer->assign('HTMLLANG', Vtiger_Language_Handler::getShortLanguageName());
		define('INSTALLATION_MODE', true);
		define('INSTALLATION_MODE_DEBUG', $this->debug);
		echo $viewer->fetch('InstallPreProcess.tpl');
	}

	public function process(Vtiger_Request $request)
	{
		$default_charset = AppConfig::main('default_charset');
		if (empty($default_charset))
			$default_charset = 'UTF-8';
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->$mode($request);
		}
		$this->Step1($request);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->setTemplateDir('install/tpl/');
		$mode = $request->getMode();
		echo $viewer->fetch('InstallPostProcess.tpl');
		if ($mode == 'Step7') {
			$this->cleanInstallationFiles();
		}
	}

	public function Step1(Vtiger_Request $request)
	{
		$isMigrate = false;
		if (is_dir('install/migrate_schema/')) {
			$filesInDir = scandir('install/migrate_schema/');
			if (count($filesInDir) > 2) {
				$isMigrate = true;
			}
		}
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANGUAGES', Install_Utils_Model::getLanguages());

		$request = $this->setLanguage($request);
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('IS_MIGRATE', $isMigrate);
		echo $viewer->fetch('Step1.tpl');
	}

	public function Step2(Vtiger_Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		echo $viewer->fetch('Step2.tpl');
	}

	public function Step3(Vtiger_Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');

		$viewer->assign('FAILED_FILE_PERMISSIONS', Settings_ConfReport_Module_Model::getPermissionsFiles(true));
		echo $viewer->fetch('Step3.tpl');
	}

	public function Step4(Vtiger_Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('CURRENCIES', Install_Utils_Model::getCurrencyList());

		require_once 'modules/Users/UserTimeZonesArray.php';
		$timeZone = new UserTimeZones();
		$viewer->assign('TIMEZONES', $timeZone->userTimeZones());

		$defaultParameters = Install_Utils_Model::getDefaultPreInstallParameters();
		$viewer->assign('DB_HOSTNAME', $defaultParameters['db_hostname']);
		$viewer->assign('DB_USERNAME', $defaultParameters['db_username']);
		$viewer->assign('DB_PASSWORD', $defaultParameters['db_password']);
		$viewer->assign('DB_NAME', $defaultParameters['db_name']);
		$viewer->assign('ADMIN_NAME', $defaultParameters['admin_name']);
		$viewer->assign('ADMIN_LASTNAME', $defaultParameters['admin_lastname']);
		$viewer->assign('ADMIN_PASSWORD', $defaultParameters['admin_password']);
		$viewer->assign('ADMIN_EMAIL', $defaultParameters['admin_email']);

		echo $viewer->fetch('Step4.tpl');
	}

	public function Step5(Vtiger_Request $request)
	{
		set_time_limit(0); // Override default limit to let install complete.
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$requestData = $request->getAll();

		foreach ($requestData as $name => $value) {
			$_SESSION['config_file_info'][$name] = $value;
		}
		$_SESSION['default_language'] = $request->get('lang');
		$_SESSION['timezone'] = $request->get('timezone');
		$createDataBase = false;
		$createDB = $request->get('create_db');
		if ($createDB == 'on') {
			$rootUser = $request->get('db_username');
			$rootPassword = $request->getRaw('db_password');
			$createDataBase = true;
		}
		$authKey = $_SESSION['config_file_info']['authentication_key'] = md5(microtime());

		//PHP 5.5+ mysqli is favourable.
		$dbConnection = Install_Utils_Model::checkDbConnection('mysql', $request->get('db_hostname'), $request->get('db_username'), $request->getRaw('db_password'), $request->get('db_name'), $createDataBase, true, $rootUser, $rootPassword);

		$webRoot = ($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
		$webRoot .= $_SERVER["REQUEST_URI"];

		$webRoot = str_replace("index.php", "", $webRoot);
		$webRoot = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? "https://" : "http://") . $webRoot;
		$tabUrl = explode('/', $webRoot);
		unset($tabUrl[count($tabUrl) - 1]);
		unset($tabUrl[count($tabUrl) - 1]);
		$webRoot = implode('/', $tabUrl) . '/';
		$_SESSION['config_file_info']['site_URL'] = $webRoot;
		$viewer->assign('SITE_URL', $webRoot);

		$currencies = Install_Utils_Model::getCurrencyList();
		$currencyName = $request->get('currency_name');
		if (isset($currencyName)) {
			$_SESSION['config_file_info']['currency_code'] = $currencies[$currencyName][0];
			$_SESSION['config_file_info']['currency_symbol'] = $currencies[$currencyName][1];
		}
		$viewer->assign('DB_CONNECTION_INFO', $dbConnection);
		$viewer->assign('INFORMATION', $requestData);
		$viewer->assign('AUTH_KEY', $authKey);
		echo $viewer->fetch('Step5.tpl');
	}

	public function Step6(Vtiger_Request $request)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');

		$viewer->assign('AUTH_KEY', $_SESSION['config_file_info']['authentication_key']);
		echo $viewer->fetch('Step6.tpl');
	}

	public function Step7(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$webuiInstance = new Vtiger_WebUI();
		$isInstalled = $webuiInstance->isInstalled();
		if (!$isInstalled) {
			if ($_SESSION['config_file_info']['authentication_key'] != $request->get('auth_key')) {
				die(vtranslate('ERR_NOT_AUTHORIZED_TO_PERFORM_THE_OPERATION', $moduleName));
			}

			// Create configuration file
			$configParams = $_SESSION['config_file_info'];

			$configFile = new Install_ConfigFileUtils_Model($configParams);
			$configFile->createConfigFile();

			$db = new PearDatabase($configParams['db_type'], $configParams['db_hostname'], $configParams['db_name'], $configParams['db_username'], $configParams['db_password']);
			$db->setDBCache();

			// Initialize and set up tables
			$initSchema = new Install_InitSchema_Model($db);
			$initSchema->initialize();

			$viewer = new Vtiger_Viewer();
			$viewer->assign('LANG', $request->get('lang'));
			$viewer->setTemplateDir('install/tpl/');
			$viewer->assign('PASSWORD', $_SESSION['config_file_info']['password']);
			$viewer->assign('APPUNIQUEKEY', $this->retrieveConfiguredAppUniqueKey());
			$viewer->assign('CURRENT_VERSION', $_SESSION['yetiforce_version']);
			$viewer->assign('INDUSTRY', $request->get('industry'));
			echo $viewer->fetch('Step7.tpl');
		} else {
			$response = new Vtiger_Response();
			$response->setResult(vtranslate('THIS_INSTANCE_IS_ALREADY_INSTALLED', $moduleName));
			return $response;
		}
	}

	public function mStep0(Vtiger_Request $request)
	{
		$initSchema = new Install_InitSchema_Model();
		$schemaLists = $initSchema->getMigrationSchemaList();
		$rootDirectory = getcwd();
		if (substr($rootDirectory, -1) != '/') {
			$rootDirectory = $rootDirectory . '/';
		}
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('EXAMPLE_DIRECTORY', $rootDirectory);
		$viewer->assign('SCHEMALISTS', $schemaLists);
		echo $viewer->fetch('mStep0.tpl');
	}

	public function mStep1(Vtiger_Request $request)
	{
		$initSchema = new Install_InitSchema_Model();
		$schemaLists = $initSchema->getMigrationSchemaList();
		$rootDirectory = getcwd();
		if (substr($rootDirectory, -1) != '/') {
			$rootDirectory = $rootDirectory . '/';
		}
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('EXAMPLE_DIRECTORY', $rootDirectory);
		$viewer->assign('SCHEMALISTS', $schemaLists);
		echo $viewer->fetch('mStep1.tpl');
	}

	public function mStep2(Vtiger_Request $request)
	{
		$initSchema = new Install_InitSchema_Model();
		$schemaLists = $initSchema->getMigrationSchemaList();
		$rootDirectory = getcwd();
		if (substr($rootDirectory, -1) != '/') {
			$rootDirectory = $rootDirectory . '/';
		}
		$viewer = new Vtiger_Viewer();
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('EXAMPLE_DIRECTORY', $rootDirectory);
		$viewer->assign('SCHEMALISTS', $schemaLists);
		echo $viewer->fetch('mStep2.tpl');
	}

	public function mStep3(Vtiger_Request $request)
	{
		$system = $request->get('system');
		$source_directory = $request->get('source_directory');
		$username = $request->get('username');
		$password = $request->get('password');
		$errorText = '';
		$loginStatus = false;

		$migrationURL = 'Install.php?mode=execute&ajax=true&system=' . $system . '&user=' . $username;
		$viewer = new Vtiger_Viewer();
		$initSchema = new Install_InitSchema_Model();
		$createConfig = $initSchema->createConfig($source_directory, $username, $password, $system);
		if ($createConfig['result']) {
			include('config/config.inc.php');
			$adb = new PearDatabase($dbconfig['db_type'], $dbconfig['db_hostname'], $dbconfig['db_name'], $dbconfig['db_username'], $dbconfig['db_password']);
			vglobal('adb', $adb);
			$query = "SELECT crypt_type, user_name FROM vtiger_users WHERE user_name=?";
			$result = $adb->requirePsSingleResult($query, array($username), true);
			if ($adb->num_rows($result) > 0) {
				$crypt_type = $adb->query_result($result, 0, 'crypt_type');
				$salt = substr($username, 0, 2);
				if ($crypt_type == 'MD5') {
					$salt = '$1$' . $salt . '$';
				} elseif ($crypt_type == 'BLOWFISH') {
					$salt = '$2$' . $salt . '$';
				} elseif ($crypt_type == 'PHP5.3MD5') {
					$salt = '$1$' . str_pad($salt, 9, '0');
				}
				$encrypted_password = crypt($password, $salt);
				$query = "SELECT 1 from vtiger_users where user_name=? && user_password=? && status = ?";
				$result = $adb->requirePsSingleResult($query, array($username, $encrypted_password, 'Active'), true);
				if ($adb->num_rows($result) > 0) {
					$loginStatus = true;
				}
			}
			if (!$loginStatus) {
				$errorText = 'LBL_WRONG_USERNAME_OR_PASSWORD';
				file_put_contents('config/config.inc.php', '');
			}
		} else {
			$errorText = $createConfig['text'];
		}
		$viewer->setTemplateDir('install/tpl/');
		$viewer->assign('LANG', $request->get('lang'));
		$viewer->assign('MIGRATIONURL', $migrationURL);
		$viewer->assign('ERRORTEXT', $errorText);
		$viewer->assign('MIGRATIONRESULT', $migrationResult);
		echo $viewer->fetch('mStep3.tpl');
		if ($loginStatus) {
			echo $viewer->fetch('mStep3Pre.tpl');
			$migrationResult = $initSchema->executeMigrationSchema($system, $username, $source_directory);
			echo $viewer->fetch('mStep3Post.tpl');
		}
	}

	// Helper function as configuration file is still not loaded.
	protected function retrieveConfiguredAppUniqueKey()
	{
		include_once 'config/config.php';
		return $application_unique_key;
	}

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateWriteAccess(true);
	}

	public function cleanInstallationFiles()
	{
		$languagesList = Users_Module_Model::getLanguagesList();
		foreach ($languagesList as $key => $value) {
			$langPath = "languages/$key/Install.php";
			if (file_exists($langPath)) {
				unlink($langPath);
			}
		}
		\vtlib\Functions::recurseDelete('install');
		\vtlib\Functions::recurseDelete('tests');
		\vtlib\Functions::recurseDelete('config/config.template.php');
		\vtlib\Functions::recurseDelete('.github');
		\vtlib\Functions::recurseDelete('.gitattributes');
		\vtlib\Functions::recurseDelete('.gitignore');
		\vtlib\Functions::recurseDelete('.travis.yml');
	}
}
