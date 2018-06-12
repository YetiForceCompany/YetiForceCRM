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

class Install_Index_View extends \App\Controller\View
{
	use \App\Controller\ExposeMethod;
	/**
	 * @var bool
	 */
	protected $debug = false;
	/**
	 * @var Vtiger_Viewer
	 */
	protected $viewer;

	public function checkPermission(\App\Request $request)
	{
	}

	public function loginRequired()
	{
		return false;
	}

	/**
	 * Set language.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Request
	 */
	public function setLanguage(\App\Request $request)
	{
		if (!$request->getByType('lang', 1)) {
			switch (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)) {
				case 'pl':
					$request->set('lang', 'pl_pl');
					break;
				case 'es':
					$request->set('lang', 'es_es');
					break;
				case 'de':
					$request->set('lang', 'de_de');
					break;
				case 'it':
					$request->set('lang', 'it_it');
					break;
				case 'pt':
					$request->set('lang', 'pt_br');
					break;
				case 'ru':
					$request->set('lang', 'ru_ru');
					break;
				case 'tr':
					$request->set('lang', 'tr_tr');
					break;
				case 'fr':
					$request->set('lang', 'fr_fr');
					break;
				default:
					$request->set('lang', 'en_us');
					break;
			}
			return $request;
		}
		return $request;
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('step3');
		$this->exposeMethod('step4');
		$this->exposeMethod('step5');
		$this->exposeMethod('step6');
		$this->exposeMethod('step7');
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		if ($request->getMode() !== 'step5') {
			date_default_timezone_set('UTC'); // to overcome the pre configuration settings
		}
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
		App\Language::setTemporaryLanguage($defaultLanguage);
		$this->loadJsConfig($request);
		$this->viewer = new Vtiger_Viewer();
		$this->viewer->setTemplateDir('install/tpl/');
		$this->viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$this->viewer->assign('LANG', $request->getByType('lang', 1));
		$this->viewer->assign('HTMLLANG', substr($defaultLanguage, 0, 2));
		$this->viewer->assign('LANGUAGE', $defaultLanguage);
		$this->viewer->assign('STYLES', $this->getHeaderCss($request));
		$this->viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$this->viewer->assign('MODE', $request->getMode());
		$this->viewer->assign('YETIFORCE_VERSION', \App\Version::get());
		$this->viewer->error_reporting = E_ALL & ~E_NOTICE;
		$this->viewer->display('InstallPreProcess.tpl');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->$mode($request);
		}
		$this->step1($request);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$this->viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$this->viewer->display('InstallPostProcess.tpl');
	}

	public function step1(\App\Request $request)
	{
		$isMigrate = false;
		if (is_dir(ROOT_DIRECTORY . '/install/migrate_schema/')) {
			$filesInDir = scandir(ROOT_DIRECTORY . '/install/migrate_schema/');
			if (count($filesInDir) > 2) {
				$isMigrate = true;
			}
		}
		$this->viewer->assign('LANGUAGES', Install_Utils_Model::getLanguages());
		$this->viewer->assign('IS_MIGRATE', $isMigrate);
		$this->viewer->display('Step1.tpl');
	}

	public function step2(\App\Request $request)
	{
		if ($_SESSION['default_language'] === 'pl_pl') {
			$license = file_get_contents('licenses/LicensePL.txt');
		} else {
			$license = file_get_contents('licenses/LicenseEN.txt');
		}
		$this->viewer->assign('LICENSE', nl2br($license));
		$this->viewer->display('Step2.tpl');
	}

	public function step3(\App\Request $request)
	{
		$this->viewer->assign('CURRENCIES', Install_Utils_Model::getCurrencyList());
		require_once ROOT_DIRECTORY . '/modules/Users/UserTimeZonesArray.php';
		$this->viewer->assign('TIMEZONES', UserTimeZones::getTimeZones());

		$defaultParameters = Install_Utils_Model::getDefaultPreInstallParameters();
		$this->viewer->assign('USERNAME_BLACKLIST', require ROOT_DIRECTORY . '/config/username_blacklist.php');
		$this->viewer->assign('DB_HOSTNAME', $defaultParameters['db_hostname']);
		$this->viewer->assign('DB_USERNAME', $defaultParameters['db_username']);
		$this->viewer->assign('DB_PASSWORD', $defaultParameters['db_password']);
		$this->viewer->assign('DB_NAME', $defaultParameters['db_name']);
		$this->viewer->assign('ADMIN_NAME', $defaultParameters['admin_name']);
		$this->viewer->assign('ADMIN_FIRSTNAME', $defaultParameters['admin_firstname']);
		$this->viewer->assign('ADMIN_LASTNAME', $defaultParameters['admin_lastname']);
		$this->viewer->assign('ADMIN_PASSWORD', $defaultParameters['admin_password']);
		$this->viewer->assign('ADMIN_EMAIL', $defaultParameters['admin_email']);
		$this->viewer->display('Step3.tpl');
	}

	public function step4(\App\Request $request)
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

		$webRoot = ($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
		$webRoot .= $_SERVER['REQUEST_URI'];

		$webRoot = str_replace('index.php', '', $webRoot);
		$webRoot = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $webRoot;
		$tabUrl = explode('/', $webRoot);
		unset($tabUrl[count($tabUrl) - 1], $tabUrl[count($tabUrl) - 1]);

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
		$this->viewer->display('Step4.tpl');
	}

	public function step5(\App\Request $request)
	{
		if (isset($_SESSION['config_file_info']['db_hostname'])) {
			\App\Db::setConfig([
				'dsn' => $_SESSION['config_file_info']['db_type'] . ':host=' . $_SESSION['config_file_info']['db_hostname'] . ';dbname=' . $_SESSION['config_file_info']['db_name'] . ';port=' . $_SESSION['config_file_info']['db_port'],
				'host' => $_SESSION['config_file_info']['db_hostname'],
				'port' => $_SESSION['config_file_info']['db_port'],
				'username' => $_SESSION['config_file_info']['db_username'],
				'password' => $_SESSION['config_file_info']['db_password'],
				'dbName' => $_SESSION['config_file_info']['db_name'],
				'tablePrefix' => 'yf_',
				'charset' => 'utf8',
			]);
			$this->viewer->assign('DB_CONF', Settings_ConfReport_Module_Model::getDbConf());
		}
		$this->viewer->assign('FAILED_FILE_PERMISSIONS', Settings_ConfReport_Module_Model::getPermissionsFiles(true));
		$this->viewer->assign('SECURITY_CONF', Settings_ConfReport_Module_Model::getSecurityConf(true));
		$this->viewer->assign('STABILITY_CONF', Settings_ConfReport_Module_Model::getStabilityConf(true));
		$this->viewer->display('Step5.tpl');
	}

	public function step6(\App\Request $request)
	{
		// Create configuration file
		$configFile = new Install_ConfigFileUtils_Model($_SESSION['config_file_info']);
		$configFile->createConfigFile();
		$this->viewer->assign('AUTH_KEY', $_SESSION['config_file_info']['authentication_key']);
		$this->viewer->display('Step6.tpl');
	}

	public function step7(\App\Request $request)
	{
		set_time_limit(0);
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
			$this->viewer->assign('INSTALATION_SUCCESS', $_SESSION['instalation_success'] ?? false);
			$this->viewer->display('Step7.tpl');
		}
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

	/**
	 * Retrieves css styles that need to loaded in the page.
	 *
	 * @param \App\Request $request - request model
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~install/tpl/resources/css/style.css',
			'~install/tpl/resources/css/mkCheckbox.css',
			'~libraries/fontawesome-web/css/fontawesome-all.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);

		return array_merge($headerCssInstances, $cssInstances);
	}

	public function getHeaderScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'libraries.jquery.dist.jquery'
		]);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		if ($request->getMode() === 'step7') {
			return [];
		}
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~install/tpl/resources/Index.js',
		]));
	}
}
