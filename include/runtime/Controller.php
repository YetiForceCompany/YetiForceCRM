<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

/**
 * Abstract Controller Class
 */
abstract class Vtiger_Controller
{

	public function __construct()
	{
		self::setHeaders();
	}

	public function loginRequired()
	{
		return true;
	}

	abstract public function getViewer(\App\Request $request);

	abstract public function process(\App\Request $request);

	public function validateRequest(\App\Request $request)
	{
		
	}

	public function preProcessAjax(\App\Request $request)
	{
		
	}

	public function preProcess(\App\Request $request)
	{
		
	}

	public function postProcess(\App\Request $request)
	{
		
	}

	// Control the exposure of methods to be invoked from client (kind-of RPC)
	protected $exposedMethods = [];

	/**
	 * Function that will expose methods for external access
	 * @param string $name - method name
	 */
	protected function exposeMethod($name)
	{
		if (!in_array($name, $this->exposedMethods)) {
			$this->exposedMethods[] = $name;
		}
	}

	/**
	 * Function checks if the method is exposed for client usage
	 * @param string $name - method name
	 * @return boolean
	 */
	public function isMethodExposed($name)
	{
		if (in_array($name, $this->exposedMethods)) {
			return true;
		}
		return false;
	}

	/**
	 * Function invokes exposed methods for this class
	 * @param string $name - method name
	 * @param \App\Request $request
	 * @throws Exception
	 */
	public function invokeExposedMethod()
	{
		$parameters = func_get_args();
		$name = array_shift($parameters);
		if (!empty($name) && $this->isMethodExposed($name)) {
			return call_user_func_array(array($this, $name), $parameters);
		}
		throw new \Exception\AppException('LBL_NOT_ACCESSIBLE');
	}

	/**
	 * Set HTTP Headers
	 */
	public function setHeaders()
	{
		if (headers_sent()) {
			return;
		}
		$browser = \App\RequestUtil::getBrowserInfo();
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		if ($browser->ie && $browser->https) {
			header('Pragma: private');
			header('Cache-Control: private, must-revalidate');
		} else {
			header('Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
		}
		header('X-Frame-Options: SAMEORIGIN');
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');
		header('Referrer-Policy: no-referrer');
		header('Strict-Transport-Security: max-age=15768000; includeSubDomains; preload');
		header('Expect-CT: enforce; max-age=3600');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		header('X-Robots-Tag: none');
		header('X-Permitted-Cross-Domain-Policies: none');
		if (AppConfig::security('CSP_ACTIVE')) {
			// 'nonce-" . App\Session::get('CSP_TOKEN') . "'
			header("Content-Security-Policy: default-src 'self' blob:; img-src 'self' data: a.tile.openstreetmap.org b.tile.openstreetmap.org c.tile.openstreetmap.org; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' ; form-action 'self' ;");
		}
		if ($keys = AppConfig::security('HPKP_KEYS')) {
			header('Public-Key-Pins: pin-sha256="' . implode('"; pin-sha256="', $keys) . '"; max-age=10000;');
		}
		header_remove('X-Powered-By');
		header_remove('Server');
	}
}

/**
 * Abstract Action Controller Class
 */
abstract class Vtiger_Action_Controller extends Vtiger_Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	abstract public function checkPermission(\App\Request $request);

	public function getViewer(\App\Request $request)
	{
		throw new \Exception\AppException('Action - implement getViewer - JSONViewer');
	}

	public function validateRequest(\App\Request $request)
	{
		return $request->validateReadAccess();
	}

	public function preProcess(\App\Request $request)
	{
		return true;
	}

	protected function preProcessDisplay(\App\Request $request)
	{
		
	}

	protected function preProcessTplName(\App\Request $request)
	{
		return false;
	}

	public function postProcess(\App\Request $request)
	{
		return true;
	}
}

/**
 * Abstract View Controller Class
 */
abstract class Vtiger_View_Controller extends Vtiger_Action_Controller
{

	/**
	 * Viewer instance
	 * @var self
	 */
	protected $viewer;

	/**
	 * Page title
	 * @var string
	 */
	protected $pageTitle;

	/**
	 * Breadcrumb title
	 * @var string
	 */
	protected $breadcrumbTitle;

	public function __construct()
	{
		parent::__construct();
	}

	public function getViewer(\App\Request $request)
	{
		if (!isset($this->viewer)) {
			$viewer = Vtiger_Viewer::getInstance();
			$viewer->assign('APPTITLE', \App\Language::translate('APPTITLE'));
			$viewer->assign('YETIFORCE_VERSION', \App\Version::get());
			$viewer->assign('MODULE_NAME', $request->getModule());
			if ($request->isAjax()) {
				$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
				if ($request->get('parent') === 'Settings') {
					$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
				}
			}
			$this->viewer = $viewer;
		}
		return $this->viewer;
	}

	/**
	 * Get page title
	 * @param \App\Request $request
	 * @return string
	 */
	public function getPageTitle(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleNameArray = explode(':', $qualifiedModuleName);
		$moduleName = end($moduleNameArray);
		$prefix = '';
		if ($moduleName !== 'Vtiger') {
			$prefix = App\Language::translate($moduleName, $qualifiedModuleName) . ' ';
		}
		if (isset($this->pageTitle)) {
			$pageTitle = App\Language::translate($this->pageTitle, $qualifiedModuleName);
		} else {
			$pageTitle = $this->getBreadcrumbTitle($request);
		}
		return $prefix . $pageTitle;
	}

	/**
	 * Get breadcrumb title
	 * @param \App\Request $request
	 * @return string
	 */
	public function getBreadcrumbTitle(\App\Request $request)
	{
		if (isset($this->breadcrumbTitle)) {
			return $this->breadcrumbTitle;
		}
		if (isset($this->pageTitle)) {
			return $this->pageTitle;
		}
		return '';
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$pageTitle = $this->getPageTitle($request);
		if (AppConfig::performance('BROWSING_HISTORY_WORKING')) {
			Vtiger_BrowsingHistory_Helper::saveHistory($pageTitle);
		}
		$viewer->assign('PAGETITLE', $pageTitle);
		$viewer->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LAYOUT_PATH', \App\Layout::getPublicUrl('layouts/' . \App\Layout::getActiveLayout()));
		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$viewer->assign('LANGUAGE', \App\Language::getLanguage());
		$viewer->assign('HTMLLANG', \App\Language::getShortLanguageName());
		$viewer->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PARENT_MODULE', $request->get('parent'));
		$companyDetails = App\Company::getInstanceById();
		$viewer->assign('COMPANY_DETAILS', $companyDetails);
		$viewer->assign('COMPANY_LOGO', $companyDetails->getLogo(false, false));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(\App\Request $request)
	{
		return 'Header.tpl';
	}

	protected function showBodyHeader()
	{
		return true;
	}

	protected function preProcessDisplay(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	/**
	 * Post process
	 * @param \App\Request $request
	 */
	public function postProcess(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->view('Footer.tpl');
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param \App\Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = [
			'~libraries/bootstrap3/css/bootstrap.css',
			'~libraries/font-awesome/css/font-awesome.css',
			'skins.icons.userIcons',
			'skins.icons.adminIcons',
			'skins.icons.additionalIcons',
			'~libraries/jquery/chosen/chosen.css',
			'~libraries/jquery/chosen/chosen.bootstrap.css',
			'~libraries/jquery/jquery-ui/jquery-ui.css',
			'~libraries/jquery/selectize/css/selectize.bootstrap3.css',
			'~libraries/jquery/select2/select2.css',
			'~libraries/jquery/perfect-scrollbar/css/perfect-scrollbar.css',
			'~libraries/jquery/select2/select2-bootstrap.css',
			'~libraries/jquery/posabsolute-jQuery-Validation-Engine/css/validationEngine.jquery.css',
			'~libraries/jquery/pnotify/pnotify.custom.css',
			'~libraries/jquery/datepicker/css/bootstrap-datepicker3.css',
			'~libraries/jquery/daterangepicker/daterangepicker.css',
			'~libraries/footable/css/footable.core.css',
			'~libraries/jquery/timepicker/jquery.timepicker.css',
			'~libraries/jquery/clockpicker/bootstrap-clockpicker.css',
		];
		$headerCssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return $headerCssInstances;
	}

	/**
	 * Retrieves headers scripts that need to loaded in the page
	 * @param \App\Request $request - request model
	 * @return <array> - array of Vtiger_JsScript_Model
	 */
	public function getHeaderScripts(\App\Request $request)
	{
		$headerScriptInstances = [
			'libraries.jquery.jquery',
			'libraries.jquery.jquery-migrate'
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($headerScriptInstances);
		return $jsScriptInstances;
	}

	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = [
			'~libraries/jquery/jquery.blockUI.js',
			'~libraries/jquery/chosen/chosen.jquery.js',
			'~libraries/jquery/select2/select2.full.js',
			'~libraries/jquery/jquery-ui/jquery-ui.js',
			'~libraries/jquery/jquery.class.js',
			'~libraries/jquery/jstorage.js',
			'~libraries/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js',
			'~libraries/jquery/rochal-jQuery-slimScroll/jquery.slimscroll.js',
			'~libraries/jquery/pnotify/pnotify.custom.js',
			'~libraries/jquery/jquery.hoverIntent.minified.js',
			'~libraries/bootstrap3/js/bootstrap.js',
			'~libraries/bootstrap3/js/bootstrap-switch.js',
			'~libraries/bootstrap3/js/bootbox.js',
			'~libraries/jquery/selectize/js/selectize.js',
			'~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js',
			'~libraries/jquery/moment.js',
			'~libraries/jquery/datepicker/js/bootstrap-datepicker.js',
			'~libraries/jquery/datepicker/locales/' . \App\Language::getLanguage() . '.min.js',
			'~libraries/jquery/daterangepicker/daterangepicker.js',
			'~libraries/jquery/jquery.ba-outside-events.js',
			'~libraries/jquery/jquery.placeholder.js',
			'~libraries/jquery/dompurify/purify.js',
			'~libraries/footable/js/footable.js',
			'~layouts/resources/jquery.additions.js',
			'~layouts/resources/app.js',
			'~layouts/resources/helper.js',
			'~layouts/resources/Connector.js',
			'~layouts/resources/ProgressIndicator.js',
		];
		$languageHandlerShortName = \App\Language::getShortLanguageName();
		$fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
		if (!file_exists(Vtiger_Loader::resolveNameToPath($fileName, 'js'))) {
			$fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js";
		}
		$jsFileNames[] = $fileName;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

	public function checkAndConvertJsScripts($jsFileNames)
	{
		$fileExtension = 'js';
		$jsScriptInstances = [];
		$prefix = '';
		if (!IS_PUBLIC_DIR && $fileExtension !== 'php') {
			$prefix = 'public_html/';
		}
		foreach ($jsFileNames as $jsFileName) {
			$jsScript = new Vtiger_JsScript_Model();
			if (\App\Cache::has('ConvertJsScripts', $jsFileName)) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', \App\Cache::get('ConvertJsScripts', $jsFileName));
				continue;
			}
			// external javascript source file handling
			if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
				continue;
			}
			$completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);
			if (is_file($completeFilePath)) {
				$jsScript->set('base', $completeFilePath);
				if (strpos($jsFileName, '~') === 0) {
					$filePath = $prefix . ltrim(ltrim($jsFileName, '~'), '/');
				} else {
					$filePath = $prefix . str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.js', '.min.js', $filePath);
				if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
				\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
				continue;
			} else {
				$preLayoutPath = '';
				if (strpos($jsFileName, '~') === 0) {
					$jsFile = ltrim(ltrim($jsFileName, '~'), '/');
					$preLayoutPath = '~';
				} else {
					$jsFile = $jsFileName;
				}

				// Checking if file exists in selected layout
				$layoutPath = 'layouts' . '/' . \App\Layout::getActiveLayout();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$jsScript->set('base', $fallBackFilePath);
					$filePath = $jsFile;
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $filePath) . '.js';
					}
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
					continue;
				}
				// Checking if file exists in default layout
				$layoutPath = 'layouts' . '/' . Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$jsScript->set('base', $fallBackFilePath);
					$filePath = $jsFile;
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $jsFile) . '.js';
					}
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertJsScripts', $jsFileName, $filePath, \App\Cache::LONG);
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
					continue;
				}
			}
		}
		return $jsScriptInstances;
	}

	/**
	 * Function returns the css files
	 * @param <Array> $cssFileNames
	 * @param string $fileExtension
	 * @return <Array of Vtiger_CssScript_Model>
	 *
	 * First check if $cssFileName exists
	 * if not, check under layout folder $cssFileName eg:layouts/basic/$cssFileName
	 */
	public function checkAndConvertCssStyles($cssFileNames, $fileExtension = 'css')
	{
		$prefix = '';
		if (!IS_PUBLIC_DIR && $fileExtension !== 'php') {
			$prefix = 'public_html/';
		}
		$cssStyleInstances = [];
		foreach ($cssFileNames as $cssFileName) {
			$cssScriptModel = new Vtiger_CssScript_Model();
			if (\App\Cache::has('ConvertCssStyles', $cssFileName)) {
				$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', \App\Cache::get('ConvertCssStyles', $cssFileName));
				continue;
			}
			if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
				$cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
				continue;
			}
			$completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
			if (file_exists($completeFilePath)) {
				$cssScriptModel->set('base', $completeFilePath);
				if (strpos($cssFileName, '~') === 0) {
					$filePath = $prefix . ltrim(ltrim($cssFileName, '~'), '/');
				} else {
					$filePath = $prefix . str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.css', '.min.css', $filePath);
				if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
				\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
				$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
				continue;
			} else {
				$preLayoutPath = '';
				if (strpos($cssFileName, '~') === 0) {
					$cssFile = ltrim(ltrim($cssFileName, '~'), '/');
					$preLayoutPath = '~';
				} else {
					$cssFile = $cssFileName;
				}
				// Checking if file exists in selected layout
				$layoutPath = 'layouts' . '/' . \App\Layout::getActiveLayout();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$cssScriptModel->set('base', $fallBackFilePath);
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					}
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
					continue;
				}

				// Checking if file exists in default layout
				$layoutPath = 'layouts' . '/' . Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$cssScriptModel->set('base', $fallBackFilePath);
					if (empty($preLayoutPath)) {
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					}
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$filePath = "{$prefix}{$layoutPath}/{$filePath}";
					\App\Cache::save('ConvertCssStyles', $cssFileName, $filePath, \App\Cache::LONG);
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $filePath);
					continue;
				}
			}
		}
		return $cssStyleInstances;
	}

	/**
	 * Function returns the Client side language string
	 * @param \App\Request $request
	 */
	public function getJSLanguageStrings(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if ($moduleName === 'Settings:Users') {
			$moduleName = 'Users';
		}
		return Vtiger_Language_Handler::export($moduleName, 'jsLanguageStrings');
	}
}
