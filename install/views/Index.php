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

class Install_Index_View extends Vtiger_View_Controller
{

	protected $debug = false;
	protected $viewer;

	public function checkPermission(\App\Request $request)
	{

	}

	public function loginRequired()
	{
		return false;
	}

	/**
	 * Set language
	 * @param \App\Request $request
	 * @return \App\Request
	 */
	public function setLanguage(\App\Request $request)
	{
		if (!$request->getByType('lang', 1)) {

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
		parent::__construct();
		//Install
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('step3');
		$this->exposeMethod('step4');
		$this->exposeMethod('step5');
		$this->exposeMethod('step6');
		$this->exposeMethod('step7');
		//Migrate
		$this->exposeMethod('mStep0');
		$this->exposeMethod('mStep1');
		$this->exposeMethod('mStep2');
		$this->exposeMethod('mStep3');
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		date_default_timezone_set('UTC'); // to overcome the pre configuration settings
		// Added to redirect to default module if already installed
		$request->set('module', 'Install');
		$request = $this->setLanguage($request);

		$configFileName = 'config/config.inc.php';
		if ($request->getMode() !== 'step7' && is_file($configFileName) && filesize($configFileName) > 10) {
			$defaultModule = \AppConfig::main('default_module');
			$defaultModuleInstance = Vtiger_Module_Model::getInstance($defaultModule);
			$defaultView = $defaultModuleInstance->getDefaultViewName();
			header('Location:../index.php?module=' . $defaultModule . '&view=' . $defaultView);
		}
		$_SESSION['default_language'] = $defaultLanguage = ($request->getByType('lang', 1)) ? $request->getByType('lang', 1) : 'en_us';
		vglobal('default_language', $defaultLanguage);

		$this->viewer = new Vtiger_Viewer();
		$this->viewer->setTemplateDir('install/tpl/');
		$this->viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$this->viewer->assign('LANG', $request->getByType('lang', 1));
		$this->viewer->assign('HTMLLANG', substr($defaultLanguage, 0, 2));
		$this->viewer->assign('LANGUAGE', $defaultLanguage);
		$this->viewer->assign('STYLES', $this->getHeaderCss($request));
		$this->viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$this->viewer->assign('MODE', $request->getMode());

		$this->viewer->error_reporting = E_ALL & ~E_NOTICE;
		echo $this->viewer->fetch('InstallPreProcess.tpl');
	}

	public function process(\App\Request $request)
	{
		$default_charset = AppConfig::main('default_charset');
		if (empty($default_charset)) {
			$default_charset = 'UTF-8';
		}
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->$mode($request);
		}
		$this->step1($request);
	}

	public function postProcess(\App\Request $request)
	{
		$this->viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		echo $this->viewer->fetch('InstallPostProcess.tpl');
		if ($request->getMode() === 'step7') {
			$this->cleanInstallationFiles();
		}
	}

	public function step1(\App\Request $request)
	{
		$isMigrate = false;
		if (is_dir('install/migrate_schema/')) {
			$filesInDir = scandir('install/migrate_schema/');
			if (count($filesInDir) > 2) {
				$isMigrate = true;
			}
		}
		$this->viewer->assign('LANGUAGES', Install_Utils_Model::getLanguages());
		$this->viewer->assign('IS_MIGRATE', $isMigrate);
		echo $this->viewer->fetch('Step1.tpl');
	}

	public function step2(\App\Request $request)
	{
		if ($_SESSION['default_language'] === 'pl_pl') {
			$license = file_get_contents('licenses/LicensePL.txt');
		} else {
			$license = file_get_contents('licenses/LicenseEN.txt');
		}
		$this->viewer->assign('LICENSE', nl2br($license));
		echo $this->viewer->fetch('Step2.tpl');
	}

	public function step3(\App\Request $request)
	{
		$this->viewer->assign('FAILED_FILE_PERMISSIONS', Settings_ConfReport_Module_Model::getPermissionsFiles(true));
		echo $this->viewer->fetch('Step3.tpl');
	}

	public function step4(\App\Request $request)
	{
		$this->viewer->assign('CURRENCIES', Install_Utils_Model::getCurrencyList());
		require_once 'modules/Users/UserTimeZonesArray.php';
		$this->viewer->assign('TIMEZONES', UserTimeZones::getTimeZones());

		$defaultParameters = Install_Utils_Model::getDefaultPreInstallParameters();
		$this->viewer->assign('USERNAME_BLACKLIST', require 'config/username_blacklist.php');
		$this->viewer->assign('DB_HOSTNAME', $defaultParameters['db_hostname']);
		$this->viewer->assign('DB_USERNAME', $defaultParameters['db_username']);
		$this->viewer->assign('DB_PASSWORD', $defaultParameters['db_password']);
		$this->viewer->assign('DB_NAME', $defaultParameters['db_name']);
		$this->viewer->assign('ADMIN_NAME', $defaultParameters['admin_name']);
		$this->viewer->assign('ADMIN_LASTNAME', $defaultParameters['admin_lastname']);
		$this->viewer->assign('ADMIN_PASSWORD', $defaultParameters['admin_password']);
		$this->viewer->assign('ADMIN_EMAIL', $defaultParameters['admin_email']);
		echo $this->viewer->fetch('Step4.tpl');
	}

	public function step5(\App\Request $request)
	{
		set_time_limit(60); // Override default limit to let install complete.
		$requestData = $request->getAll();
		foreach ($requestData as $name => $value) {
			$_SESSION['config_file_info'][$name] = $value;
		}
		$_SESSION['default_language'] = $request->getByType('lang', 1);
		$_SESSION['timezone'] = $request->get('timezone');
		$authKey = $_SESSION['config_file_info']['authentication_key'] = sha1(microtime());

		//PHP 5.5+ mysqli is favourable.
		$dbConnection = Install_Utils_Model::checkDbConnection($request);

		$webRoot = ($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
		$webRoot .= $_SERVER["REQUEST_URI"];

		$webRoot = str_replace("index.php", "", $webRoot);
		$webRoot = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? "https://" : "http://") . $webRoot;
		$tabUrl = explode('/', $webRoot);
		unset($tabUrl[count($tabUrl) - 1]);
		unset($tabUrl[count($tabUrl) - 1]);
		$webRoot = implode('/', $tabUrl) . '/';
		$_SESSION['config_file_info']['site_URL'] = $webRoot;
		$this->viewer->assign('SITE_URL', $webRoot);

		$currencies = Install_Utils_Model::getCurrencyList();
		$currencyName = $request->get('currency_name');
		if (isset($currencyName)) {
			$_SESSION['config_file_info']['currency_code'] = $currencies[$currencyName][0];
			$_SESSION['config_file_info']['currency_symbol'] = $currencies[$currencyName][1];
		}
		$this->viewer->assign('DB_CONNECTION_INFO', $dbConnection);
		$this->viewer->assign('INFORMATION', $requestData);
		$this->viewer->assign('AUTH_KEY', $authKey);
		echo $this->viewer->fetch('Step5.tpl');
	}

	public function step6(\App\Request $request)
	{
		// Create configuration file
		$configFile = new Install_ConfigFileUtils_Model($_SESSION['config_file_info']);
		$configFile->createConfigFile();
		$this->viewer->assign('AUTH_KEY', $_SESSION['config_file_info']['authentication_key']);
		echo $this->viewer->fetch('Step6.tpl');
	}

	public function step7(\App\Request $request)
	{
		AppConfig::iniSet('display_errors', 'On');
		AppConfig::iniSet('max_execution_time', 0);
		AppConfig::iniSet('max_input_time', 0);
		$dbconfig = AppConfig::main('dbconfig');
		if (!(empty($dbconfig) || empty($dbconfig['db_name']) || $dbconfig['db_name'] == '_DBC_TYPE_')) {
			if ($_SESSION['config_file_info']['authentication_key'] !== $request->get('auth_key')) {
				throw new \App\Exceptions\AppException('ERR_NOT_AUTHORIZED_TO_PERFORM_THE_OPERATION');
			}
			// Initialize and set up tables
			$initSchema = new Install_InitSchema_Model();
			$initSchema->initialize();
			$initSchema->setCompanyDetails($request);

			$this->viewer->assign('USER_NAME', $_SESSION['config_file_info']['user_name']);
			$this->viewer->assign('PASSWORD', $_SESSION['config_file_info']['password']);
			$this->viewer->assign('APPUNIQUEKEY', $this->retrieveConfiguredAppUniqueKey());
			$this->viewer->assign('CURRENT_VERSION', \App\Version::get());
			echo $this->viewer->fetch('Step7.tpl');
		}
	}

	public function mStep0(\App\Request $request)
	{
		$initSchema = new Install_InitSchema_Model();
		$schemaLists = $initSchema->getMigrationSchemaList();
		$rootDirectory = getcwd();
		if (substr($rootDirectory, -1) != '/') {
			$rootDirectory = $rootDirectory . '/';
		}
		$this->viewer->assign('EXAMPLE_DIRECTORY', $rootDirectory);
		$this->viewer->assign('SCHEMALISTS', $schemaLists);
		echo $this->viewer->fetch('mStep0.tpl');
	}

	// Helper function as configuration file is still not loaded.
	protected function retrieveConfiguredAppUniqueKey()
	{
		include_once 'config/config.php';
		return $application_unique_key;
	}

	protected function preProcessDisplay(\App\Request $request)
	{

	}

	public function validateRequest(\App\Request $request)
	{
		return $request->validateWriteAccess(true);
	}

	public function cleanInstallationFiles()
	{
		foreach (glob('languages/*/Install.php') as $path) {
			unlink($path);
		}
		\vtlib\Functions::recurseDelete('install');
		\vtlib\Functions::recurseDelete('public_html/install');
		\vtlib\Functions::recurseDelete('tests');
		\vtlib\Functions::recurseDelete('config/config.template.php');
		\vtlib\Functions::recurseDelete('.github');
		\vtlib\Functions::recurseDelete('.gitattributes');
		\vtlib\Functions::recurseDelete('.gitignore');
		\vtlib\Functions::recurseDelete('.travis.yml');
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param \App\Request $request - request model
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~install/tpl/resources/css/style.css',
			'~install/tpl/resources/css/mkCheckbox.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($headerCssInstances, $cssInstances);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		if ($request->getMode() === 'step7') {
			return [];
		}
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'~install/tpl/resources/Index.js',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return array_merge($headerScriptInstances, $jsScriptInstances);
	}
}
