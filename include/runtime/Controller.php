<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * Abstract Controller Class
 */
abstract class Vtiger_Controller
{

	function __construct()
	{
		self::setHeaders();
	}

	function loginRequired()
	{
		return true;
	}

	abstract function getViewer(Vtiger_Request $request);

	abstract function process(Vtiger_Request $request);

	function validateRequest(Vtiger_Request $request)
	{
		
	}

	function preProcess(Vtiger_Request $request)
	{
		
	}

	function postProcess(Vtiger_Request $request)
	{
		
	}

	// Control the exposure of methods to be invoked from client (kind-of RPC)
	protected $exposedMethods = array();

	/**
	 * Function that will expose methods for external access
	 * @param <String> $name - method name
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
	function isMethodExposed($name)
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
	function invokeExposedMethod()
	{
		$parameters = func_get_args();
		$name = array_shift($parameters);
		if (!empty($name) && $this->isMethodExposed($name)) {
			return call_user_func_array(array($this, $name), $parameters);
		}
		throw new AppException(vtranslate('LBL_NOT_ACCESSIBLE'));
	}

	function setHeaders()
	{
		$browser = Vtiger_Functions::getBrowserInfo();
		header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

		if ($browser->ie && $browser->https) {
			header('Pragma: private');
			header("Cache-Control: private, must-revalidate");
		} else {
			header("Cache-Control: private, no-cache, no-store, must-revalidate, post-check=0, pre-check=0");
			header("Pragma: no-cache");
		}
	}
}

/**
 * Abstract Action Controller Class
 */
abstract class Vtiger_Action_Controller extends Vtiger_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	function getViewer(Vtiger_Request $request)
	{
		throw new AppException('Action - implement getViewer - JSONViewer');
	}

	function validateRequest(Vtiger_Request $request)
	{
		return $request->validateReadAccess();
	}

	function preProcess(Vtiger_Request $request)
	{
		return true;
	}

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		
	}

	protected function preProcessTplName()
	{
		return false;
	}

	//TODO: need to revisit on this as we are not sure if this is helpful
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return false;
	  } */

	function postProcess(Vtiger_Request $request)
	{
		return true;
	}
}

/**
 * Abstract View Controller Class
 */
abstract class Vtiger_View_Controller extends Vtiger_Action_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	function getViewer(Vtiger_Request $request)
	{
		if (!$this->viewer) {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('APPTITLE', getTranslatedString('APPTITLE'));
			$viewer->assign('YETIFORCE_VERSION', vglobal('YetiForce_current_version'));
			$this->viewer = $viewer;
		}
		return $this->viewer;
	}

	function getPageTitle(Vtiger_Request $request)
	{
		$title = $request->getModule();
		return $title == 'Vtiger' ? 'YetiForce' : $title;
	}

	function preProcess(Vtiger_Request $request, $display = true)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$viewer->assign('PAGETITLE', $this->getPageTitle($request));
		$viewer->assign('HEADER_SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
		$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
		$viewer->assign('HTMLLANG', Vtiger_Language_Handler::getShortLanguageName());
		$viewer->assign('LANGUAGE', Vtiger_Language_Handler::getLanguage());
		$viewer->assign('SHOW_BODY_HEADER', $this->showBodyHeader());
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
	
	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	//TODO: Need to revisit this.
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$displayed = $viewer->view($this->preProcessTplName($request), $request->getModule());
		/* if(!$displayed) {
		  $tplName = $this->preProcessParentTplName($request);
		  if($tplName) {
		  $viewer->view($tplName, $request->getModule());
		  }
		  } */
	}

	function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());
		$viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
		$viewer->view('Footer.tpl');
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	function getHeaderCss(Vtiger_Request $request)
	{
		$cssFileNames = array(
			'~libraries/bootstrap3/css/bootstrap.css',
			'~libraries/jquery/chosen/chosen.css',
			'~libraries/jquery/chosen/chosen.bootstrap.css',
			'~libraries/jquery/jquery-ui/jquery-ui.css',
			'~libraries/jquery/selectize/css/selectize.bootstrap3.css',
			'~libraries/jquery/select2/select2.css',
			'~libraries/jquery/select2/select2-bootstrap.css',
			'~libraries/jquery/posabsolute-jQuery-Validation-Engine/css/validationEngine.jquery.css',
			'~libraries/jquery/pnotify/pnotify.custom.css',
			'~libraries/jquery/datepicker/css/datepicker.css',
			'~layouts/vlayout/skins/icons/userIcons.css',
			'~layouts/vlayout/skins/icons/adminIcons.css',
			'~layouts/vlayout/resources/styles.css',
			'~libraries/jquery/timepicker/jquery.timepicker.css',
			'~layouts/vlayout/modules/OSSMail/resources/OSSMailBoxInfo.css',
			'~libraries/footable/css/footable.core.css',
		);
		$headerCssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return $headerCssInstances;
	}

	/**
	 * Retrieves headers scripts that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_JsScript_Model
	 */
	function getHeaderScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = [
			'libraries.jquery.jquery',
			'libraries.jquery.jquery-migrate'
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($headerScriptInstances);
		return $jsScriptInstances;
	}

	function getFooterScripts(Vtiger_Request $request)
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
			'~libraries/jquery/rochal-jQuery-slimScroll/jquery.slimscroll.js',
			'~libraries/jquery/pnotify/pnotify.custom.js',
			'~libraries/jquery/jquery.hoverIntent.minified.js',
			'~libraries/bootstrap3/js/bootstrap.js',
			'~libraries/bootstrap3/js/bootstrap-switch.js',
			'~libraries/bootstrap3/js/bootbox.js',
			'~libraries/jquery/selectize/js/selectize.js',
			'~layouts/vlayout/resources/jquery.additions.js',
			'~layouts/vlayout/resources/app.js',
			'~layouts/vlayout/resources/helper.js',
			'~layouts/vlayout/resources/Connector.js',
			'~layouts/vlayout/resources/ProgressIndicator.js',
			'~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js',
			'~libraries/jquery/datepicker/js/datepicker.js',
			'~libraries/jquery/dangrossman-bootstrap-daterangepicker/date.js',
			'~libraries/jquery/jquery.ba-outside-events.js',
			'~libraries/jquery/jquery.placeholder.js',
			'~libraries/footable/js/footable.js',
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

	function checkAndConvertJsScripts($jsFileNames)
	{
		$fileExtension = 'js';

		$jsScriptInstances = array();
		foreach ($jsFileNames as $jsFileName) {
			// TODO Handle absolute inclusions (...) like in checkAndConvertCssStyles
			$jsScript = new Vtiger_JsScript_Model();

			// external javascript source file handling
			if (strpos($jsFileName, 'http://') === 0 || strpos($jsFileName, 'https://') === 0) {
				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
				continue;
			}

			$completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);
			$minFilePath = str_replace('.js', '.min.js', $completeFilePath);

			if (Vtiger_Functions::getMinimizationOptions($fileExtension) && file_exists($minFilePath)) {
				$minjsFileName = str_replace('.js', '.min.js', $jsFileName);
				if (strpos($minjsFileName, '~') === 0) {
					$minjsFileName = ltrim(ltrim($minjsFileName, '~'), '/');
				} else {
					$minjsFileName = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}

				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $minjsFileName);
			} else if (file_exists($completeFilePath)) {
				if (strpos($jsFileName, '~') === 0) {
					$filePath = ltrim(ltrim($jsFileName, '~'), '/');
				} else {
					$filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
				}

				$jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
			} else {
				if (Vtiger_Functions::getMinimizationOptions($fileExtension)) {
					$fallBackFilePath = Vtiger_Loader::resolveNameToPath(Vtiger_JavaScript::getBaseJavaScriptPath() . '/' . $jsFileName . $min, 'js');
					if (file_exists($fallBackFilePath)) {
						$filePath = str_replace('.', '/', $jsFileName) . $min . '.js';
						$jsScriptInstances[$jsFileName] = $jsScript->set('src', Vtiger_JavaScript::getFilePath($filePath));
					}
				}
				$fallBackFilePath = Vtiger_Loader::resolveNameToPath(Vtiger_JavaScript::getBaseJavaScriptPath() . '/' . $jsFileName, 'js');
				if (file_exists($fallBackFilePath)) {
					$filePath = str_replace('.', '/', $jsFileName) . '.js';
					$jsScriptInstances[$jsFileName] = $jsScript->set('src', Vtiger_JavaScript::getFilePath($filePath));
				}
			}
		}
		return $jsScriptInstances;
	}

	/**
	 * Function returns the css files
	 * @param <Array> $cssFileNames
	 * @param <String> $fileExtension
	 * @return <Array of Vtiger_CssScript_Model>
	 *
	 * First check if $cssFileName exists
	 * if not, check under layout folder $cssFileName eg:layouts/vlayout/$cssFileName
	 */
	function checkAndConvertCssStyles($cssFileNames, $fileExtension = 'css')
	{
		$cssStyleInstances = array();
		foreach ($cssFileNames as $cssFileName) {
			$cssScriptModel = new Vtiger_CssScript_Model();

			if (strpos($cssFileName, 'http://') === 0 || strpos($cssFileName, 'https://') === 0) {
				$cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
				continue;
			}
			$completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);
			$filePath = NULL;
			$minFilePath = str_replace('.css', '.min.css', $completeFilePath);
			if (Vtiger_Functions::getMinimizationOptions($fileExtension) && file_exists($minFilePath)) {
				if (strpos($cssFileName, '~') === 0) {
					$minFilePath = str_replace('.css', '.min.css', $cssFileName);
					$filePath = ltrim(ltrim($minFilePath, '~'), '/');
					// if ~~ (reference is outside vtiger6 folder)
					if (substr_count($minFilePath, "~") == 2) {
						$filePath = "../" . $filePath;
					}
				} else {
					$filePath = str_replace('.', '/', $cssFileName) . '.min.' . $fileExtension;
					$filePath = Vtiger_Theme::getStylePath($filePath);
				}
				$cssStyleInstances[] = $cssScriptModel->set('href', $filePath);
			} else if (file_exists($completeFilePath)) {
				if (strpos($cssFileName, '~') === 0) {
					$filePath = ltrim(ltrim($cssFileName, '~'), '/');
					// if ~~ (reference is outside vtiger6 folder)
					if (substr_count($cssFileName, "~") == 2) {
						$filePath = "../" . $filePath;
					}
				} else {
					$filePath = str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
					$filePath = Vtiger_Theme::getStylePath($filePath);
				}
				$cssStyleInstances[] = $cssScriptModel->set('href', $filePath);
			}
		}
		return $cssStyleInstances;
	}

	/**
	 * Function returns the Client side language string
	 * @param Vtiger_Request $request
	 */
	function getJSLanguageStrings(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		return Vtiger_Language_Handler::export($moduleName, 'jsLanguageStrings');
	}
}
