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
require_once 'include/ConfigUtils.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/Loader.php';
vimport('include.runtime.EntryPoint');

class Vtiger_WebUI extends Vtiger_EntryPoint
{

	/**
	 * Function to check if the User has logged in
	 * @param Vtiger_Request $request
	 * @throws \Exception\AppException
	 */
	protected function checkLogin(Vtiger_Request $request)
	{
		if (!$this->hasLogin()) {
			$return_params = $_SERVER['QUERY_STRING'];
			if ($return_params && !$_SESSION['return_params']) {
				//Take the url that user would like to redirect after they have successfully logged in.
				$return_params = urlencode($return_params);
				Vtiger_Session::set('return_params', $return_params);
			}
			header('Location: index.php');
			throw new \Exception\AppException('Login is required');
		}
	}

	/**
	 * Function to get the instance of the logged in User
	 * @return Users object
	 */
	public function getLogin()
	{
		$user = parent::getLogin();
		if (!$user) {
			$userid = Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);
			if ($userid && AppConfig::main('application_unique_key') == Vtiger_Session::get('app_unique_key')) {
				$user = CRMEntity::getInstance('Users');
				$user->retrieveCurrentUserInfoFromFile($userid);
				$this->setLogin($user);
			}
		}
		return $user;
	}

	protected function triggerCheckPermission($handler, $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if (empty($moduleModel)) {
			throw new \Exception\AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_HANDLER_NOT_FOUND'));
		}

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if ($permission) {
			$handler->checkPermission($request);
			return;
		}
		throw new \Exception\NoPermitted('LBL_NOT_ACCESSIBLE');
	}

	protected function triggerPreProcess($handler, $request)
	{
		if ($request->isAjax()) {
			$handler->preProcessAjax($request);
			return true;
		}
		$handler->preProcess($request);
	}

	protected function triggerPostProcess($handler, $request)
	{
		if ($request->isAjax()) {
			return true;
		}
		$handler->postProcess($request);
	}

	public function isInstalled()
	{
		$dbconfig = AppConfig::main('dbconfig');
		if (empty($dbconfig) || empty($dbconfig['db_name']) || $dbconfig['db_name'] == '_DBC_TYPE_') {
			return false;
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$log = LoggerManager::getLogger('System');
		vglobal('log', $log);
		if (AppConfig::main('forceSSL') && !vtlib\Functions::getBrowserInfo()->https) {
			header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", true, 301);
		}
		if ($this->isInstalled() === false) {
			header('Location:install/Install.php');
			exit;
		}
		$request_URL = (vtlib\Functions::getBrowserInfo()->https ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (AppConfig::main('forceRedirect') && stripos($request_URL, AppConfig::main('site_URL')) !== 0) {
			header('Location: ' . AppConfig::main('site_URL'), true, 301);
			throw new \Exception\AppException('Force Redirect');
		}
		Vtiger_Session::init();

		// Better place this here as session get initiated
		//skipping the csrf checking for the forgot(reset) password
		if (AppConfig::main('csrfProtection') && $request->get('mode') != 'reset' && $request->get('action') != 'Login' && AppConfig::main('systemMode') != 'demo') {
			require_once('libraries/csrf-magic/csrf-magic.php');
			require_once('config/csrf_config.php');
		}
		// common utils api called, depend on this variable right now
		$currentUser = $this->getLogin();
		vglobal('current_user', $currentUser);

		$currentLanguage = Vtiger_Language_Handler::getLanguage();
		vglobal('current_language', $currentLanguage);
		$module = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		if ($currentUser && $qualifiedModuleName) {
			$moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage, $qualifiedModuleName);
			vglobal('mod_strings', $moduleLanguageStrings['languageStrings']);
		}

		if ($currentUser) {
			$moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage);
			vglobal('app_strings', $moduleLanguageStrings['languageStrings']);
		}

		$view = $request->get('view');
		$action = $request->get('action');
		$response = false;

		try {
			if (empty($module)) {
				if ($this->hasLogin()) {
					$defaultModule = AppConfig::main('default_module');
					if (!empty($defaultModule) && $defaultModule != 'Home') {
						$module = $defaultModule;
						$qualifiedModuleName = $defaultModule;
						$view = 'List';
						if ($module == 'Calendar') {
							$view = 'Calendar';
						}
					} else {
						$module = 'Home';
						$qualifiedModuleName = 'Home';
						$view = 'DashBoard';
					}
				} else {
					$module = 'Users';
					$qualifiedModuleName = 'Settings:Users';
					$view = 'Login';
				}
				$request->set('module', $module);
				$request->set('view', $view);
			}

			if (!empty($action)) {
				$componentType = 'Action';
				$componentName = $action;
			} else {
				$componentType = 'View';
				if (empty($view)) {
					$view = 'Index';
				}
				$componentName = $view;
			}
			define('_PROCESS_TYPE', $componentType);
			define('_PROCESS_NAME', $componentName);
			$handlerClass = Vtiger_Loader::getComponentClassName($componentType, $componentName, $qualifiedModuleName);
			$handler = new $handlerClass();
			if ($handler) {
				vglobal('currentModule', $module);
				if (AppConfig::main('csrfProtection') && AppConfig::main('systemMode') != 'demo') {
					// Ensure handler validates the request
					$handler->validateRequest($request);
				}
				if ($handler->loginRequired()) {
					$this->checkLogin($request);
				}

				$skipList = ['Users', 'Home', 'CustomView', 'Import', 'Export', 'Inventory', 'Vtiger', 'Migration', 'Install', 'ModTracker', 'CustomerPortal', 'WSAPP'];

				if (!in_array($module, $skipList) && stripos($qualifiedModuleName, 'Settings') === false) {
					$this->triggerCheckPermission($handler, $request);
				}

				// Every settings page handler should implement this method
				if (stripos($qualifiedModuleName, 'Settings') === 0 || ($module == 'Users')) {
					$handler->checkPermission($request);
				}

				$notPermittedModules = array('ModComments', 'Integration', 'DashBoard');

				if (in_array($module, $notPermittedModules) && $view == 'List') {
					header('Location:index.php?module=Home&view=DashBoard');
				}

				$this->triggerPreProcess($handler, $request);
				$response = $handler->process($request);
				$this->triggerPostProcess($handler, $request);
			} else {
				throw new \Exception\AppException(vtranslate('LBL_HANDLER_NOT_FOUND'));
			}
		} catch (Exception $e) {
			$log->error($e->getMessage() . ' => ' . $e->getFile() . ':' . $e->getLine());
			$tpl = 'OperationNotPermitted.tpl';
			if ($e instanceof \Exception\NoPermittedToRecord || $e instanceof WebServiceException) {
				$tpl = 'NoPermissionsForRecord.tpl';
			}

			\vtlib\Functions::throwNewException($e->getMessage(), false, $tpl);
			if (AppConfig::debug('DISPLAY_DEBUG_BACKTRACE')) {
				echo '<pre>' . $e->getTraceAsString() . '</pre>';
				$response = false;
			}
		}

		if ($response) {
			$response->emit();
		}
	}
}

if (AppConfig::debug('EXCEPTION_ERROR_HANDLER')) {

	function exception_error_handler($errno, $errstr, $errfile, $errline)
	{
		$msg = $errno . ': ' . $errstr . ' in ' . $errfile . ', line ' . $errline;
		if (\AppConfig::debug('EXCEPTION_ERROR_TO_FILE')) {
			$file = 'cache/logs/errors.log';
			$content = print_r($msg, true);
			$content .= PHP_EOL . \vtlib\Functions::getBacktrace();
			file_put_contents($file, $content . PHP_EOL, FILE_APPEND);
		}
		if (AppConfig::debug('EXCEPTION_ERROR_TO_SHOW')) {
			\vtlib\Functions::throwNewException($msg, false);
		}
	}
	set_error_handler('exception_error_handler', \AppConfig::debug('EXCEPTION_ERROR_LEVEL'));
}
