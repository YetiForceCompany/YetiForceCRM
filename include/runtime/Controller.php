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

	abstract function getViewer(Vtiger_Request $request);

	abstract function process(Vtiger_Request $request);

	public function validateRequest(Vtiger_Request $request)
	{
		
	}

	public function preProcessAjax(Vtiger_Request $request)
	{
		
	}

	public function preProcess(Vtiger_Request $request)
	{
		
	}

	public function postProcess(Vtiger_Request $request)
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
	 * @param Vtiger_Request $request
	 * @throws Exception
	 */
	public function invokeExposedMethod()
	{
		$parameters = func_get_args();
		$name = array_shift($parameters);
		if (!empty($name) && $this->isMethodExposed($name)) {
			return call_user_func_array(array($this, $name), $parameters);
		}
		throw new \Exception\AppException(vtranslate('LBL_NOT_ACCESSIBLE'));
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
		header_remove('X-Powered-By');
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

	public function getViewer(Vtiger_Request $request)
	{
		throw new \Exception\AppException('Action - implement getViewer - JSONViewer');
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateReadAccess();
	}

	public function preProcess(Vtiger_Request $request)
	{
		return true;
	}

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		
	}

	protected function preProcessTplName(Vtiger_Request $request)
	{
		return false;
	}

	public function postProcess(Vtiger_Request $request)
	{
		return true;
	}
}

/**
 * Abstract View Controller Class
 */
abstract class Vtiger_View_Controller extends Vtiger_Action_Controller
{

	protected $viewer;

	public function __construct()
	{
		parent::__construct();
	}

	public function getViewer(Vtiger_Request $request)
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

	public function getPageTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$moduleNameArray = explode(':', $moduleName);
		$moduleLabel = end($moduleNameArray) == 'Vtiger' ? 'YetiForce' : end($moduleNameArray);
		$title = App\Language::translate($moduleLabel, $moduleName);
		$pageTitle = $this->getBreadcrumbTitle($request);
		if ($pageTitle) {
			$title .= ' - ' . $pageTitle;
		}
		return $title;
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		if (!empty($this->pageTitle)) {
			return $this->pageTitle;
		}
		return 0;
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('PAGETITLE', $this->getPageTitle($request));
		$viewer->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LAYOUT_PATH', 'layouts' . '/' . Yeti_Layout::getActiveLayout());
		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$viewer->assign('HTMLLANG', Vtiger_Language_Handler::getShortLanguageName());
		$viewer->assign('LANGUAGE', Vtiger_Language_Handler::getLanguage());
		$viewer->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PARENT_MODULE', $request->get('parent'));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Vtiger_Request $request)
	{
		return 'Header.tpl';
	}

	protected function showBodyHeader()
	{
		return true;
	}

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$displayed = $viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	/**
	 * Post process
	 * @param Vtiger_Request $request
	 */
	public function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
		$viewer->assign('COMPANY_LOGO', \App\Company::getInstanceById()->getLogo());
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->view('Footer.tpl');
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(Vtiger_Request $request)
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
			'~libraries/jquery/datepicker/css/datepicker.css',
			'~libraries/footable/css/footable.core.css',
			'~libraries/jquery/timepicker/jquery.timepicker.css',
			'~libraries/jquery/clockpicker/bootstrap-clockpicker.css',
			'libraries.resources.styles',
		];
		$headerCssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return $headerCssInstances;
	}

	/**
	 * Retrieves headers scripts that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_JsScript_Model
	 */
	public function getHeaderScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = [
			'libraries.jquery.jquery',
			'libraries.jquery.jquery-migrate'
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($headerScriptInstances);
		return $jsScriptInstances;
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$jsFileNames = [
			'~libraries/jquery/jquery.blockUI.js',
			'~libraries/jquery/chosen/chosen.jquery.js',
			'~libraries/jquery/select2/select2.full.js',
			'~libraries/jquery/jquery-ui/jquery-ui.js',
			'~libraries/jquery/jquery.class.js',
			'~libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js',
			'~libraries/jquery/jstorage.js',
			'~libraries/jquery/autosize/jquery.autosize-min.js',
			'~libraries/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js',
			'~libraries/jquery/rochal-jQuery-slimScroll/jquery.slimscroll.js',
			'~libraries/jquery/pnotify/pnotify.custom.js',
			'~libraries/jquery/jquery.hoverIntent.minified.js',
			'~libraries/bootstrap3/js/bootstrap.js',
			'~libraries/bootstrap3/js/bootstrap-switch.js',
			'~libraries/bootstrap3/js/bootbox.js',
			'~libraries/jquery/selectize/js/selectize.js',
			'~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js',
			'~libraries/jquery/datepicker/js/datepicker.js',
			'~libraries/jquery/dangrossman-bootstrap-daterangepicker/date.js',
			'~libraries/jquery/jquery.ba-outside-events.js',
			'~libraries/jquery/jquery.placeholder.js',
			'~libraries/footable/js/footable.js',
			'~libraries/resources/jquery.additions.js',
			'libraries.resources.app',
			'libraries.resources.helper',
			'libraries.resources.Connector',
			'libraries.resources.ProgressIndicator',
		];

		$languageHandlerShortName = Vtiger_Language_Handler::getShortLanguageName();
		$fileName = "libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
		if (!file_exists($fileName)) {
			$fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js";
		} else {
			$fileName = "~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js";
		}
		$jsFileNames[] = $fileName;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

	public function checkAndConvertJsScripts($jsFileNames)
	{
		$fileExtension = 'js';
		$jsScriptInstances = [];

		foreach ($jsFileNames as $jsFileName) {
			$jsScript = new Vtiger_JsScript_Model();

			// external javascript source file handling
			if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
				continue;
			}
			$completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);
			if (is_file($completeFilePath)) {
				if (strpos($jsFileName, '~') === 0) {
					$filePath = ltrim(ltrim($jsFileName, '~'), '/');
				} else {
					$filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.js', '.min.js', $filePath);
				if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
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
				$layoutPath = 'layouts' . '/' . Yeti_Layout::getActiveLayout();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$filePath = $jsFile;
					if (empty($preLayoutPath))
						$filePath = str_replace('.', '/', $filePath) . '.js';
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $layoutPath . '/' . $filePath);
					continue;
				}

				// Checking if file exists in default layout
				$layoutPath = 'layouts' . '/' . Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $jsFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					$filePath = $jsFile;
					if (empty($preLayoutPath))
						$filePath = str_replace('.', '/', $jsFile) . '.js';
					$minFilePath = str_replace('.js', '.min.js', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', $layoutPath . '/' . $filePath);
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
		$cssStyleInstances = [];
		foreach ($cssFileNames as $cssFileName) {
			$cssScriptModel = new Vtiger_CssScript_Model();
			if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
				$cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
				continue;
			}
			$completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
			if (file_exists($completeFilePath)) {
				if (strpos($cssFileName, '~') === 0) {
					$filePath = ltrim(ltrim($cssFileName, '~'), '/');
				} else {
					$filePath = str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
				}
				$minFilePath = str_replace('.css', '.min.css', $filePath);
				if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $minFilePath, $fileExtension))) {
					$filePath = $minFilePath;
				}
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
				$layoutPath = 'layouts' . '/' . Yeti_Layout::getActiveLayout();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					if (empty($preLayoutPath))
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $layoutPath . '/' . $filePath);
					continue;
				}

				// Checking if file exists in default layout
				$layoutPath = 'layouts' . '/' . Vtiger_Viewer::getDefaultLayoutName();
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath($preLayoutPath . $layoutPath . '/' . $cssFile, $fileExtension);
				if (is_file($fallBackFilePath)) {
					if (empty($preLayoutPath))
						$filePath = str_replace('.', '/', $cssFile) . '.css';
					$minFilePath = str_replace('.css', '.min.css', $filePath);
					if (vtlib\Functions::getMinimizationOptions($fileExtension) && is_file(Vtiger_Loader::resolveNameToPath('~' . $layoutPath . '/' . $minFilePath, $fileExtension))) {
						$filePath = $minFilePath;
					}
					$cssStyleInstances[$cssFileName] = $cssScriptModel->set('href', $layoutPath . '/' . $filePath);
					continue;
				}
			}
		}
		return $cssStyleInstances;
	}

	/**
	 * Function returns the Client side language string
	 * @param Vtiger_Request $request
	 */
	public function getJSLanguageStrings(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		if ($moduleName === 'Settings:Users') {
			$moduleName = 'Users';
		}
		return Vtiger_Language_Handler::export($moduleName, 'jsLanguageStrings');
	}
}
