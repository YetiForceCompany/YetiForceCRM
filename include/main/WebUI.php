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
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/Loader.php';
Vtiger_Loader::includeOnce('include.runtime.EntryPoint');
App\Debuger::init();
App\Cache::init();
App\Db::$connectCache = AppConfig::performance('ENABLE_CACHING_DB_CONNECTION');
App\Log::$logToProfile = Yii::$logToProfile = AppConfig::debug('LOG_TO_PROFILE');
App\Log::$logToConsole = AppConfig::debug('LOG_TO_CONSOLE');
App\Log::$logToFile = AppConfig::debug('LOG_TO_FILE');

class Vtiger_WebUI extends Vtiger_EntryPoint
{
	/**
	 * User privileges model instance.
	 *
	 * @var Users_Privileges_Model
	 */
	protected $userPrivilegesModel;

	/**
	 * Function to check if the User has logged in.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Unauthorized
	 */
	protected function checkLogin(\App\Request $request)
	{
		if (!$this->hasLogin()) {
			$returnUrl = $request->getServer('QUERY_STRING');
			if ($returnUrl && !\App\Session::has('return_params')) {
				//Take the url that user would like to redirect after they have successfully logged in.
				\App\Session::set('return_params', str_replace('&amp;', '&', $returnUrl));
			}
			if (!$request->isAjax()) {
				header('location: index.php');
			}
			throw new \App\Exceptions\Unauthorized('LBL_LOGIN_IS_REQUIRED', 401);
		}
	}

	/**
	 * Function to get the instance of the logged in User.
	 *
	 * @return Users object
	 */
	public function getLogin()
	{
		$user = parent::getLogin();
		if (!$user && App\Session::has('authenticated_user_id')) {
			$userId = App\Session::get('authenticated_user_id');
			if ($userId && AppConfig::main('application_unique_key') === App\Session::get('app_unique_key')) {
				\App\User::setCurrentUserId($userId);
				$this->setLogin();
			}
		}
		return $user;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws Exception
	 * @throws \App\Exceptions\AppException
	 */
	public function process(\App\Request $request)
	{
		if (AppConfig::main('forceSSL') && !\App\RequestUtil::getBrowserInfo()->https) {
			header("location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", true, 301);
		}
		if (AppConfig::main('forceRedirect')) {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $request->getServer('HTTP_HOST') . $request->getServer('REQUEST_URI');
			if (stripos($requestUrl, AppConfig::main('site_URL')) !== 0) {
				header('location: ' . AppConfig::main('site_URL'), true, 301);
			}
		}
		try {
			App\Session::init();
			// Better place this here as session get initiated
			//skipping the csrf checking for the forgot(reset) password
			if (AppConfig::main('csrfProtection') && $request->getMode() !== 'reset' && $request->getByType('action', 1) !== 'Login' && AppConfig::main('systemMode') !== 'demo') {
				require_once 'config/csrf_config.php';
				\CsrfMagic\Csrf::init();
			}
			// common utils api called, depend on this variable right now
			$this->getLogin();
			$moduleName = $request->getModule();
			$qualifiedModuleName = $request->getModule(false);
			$view = $request->getByType('view', 2);
			$action = $request->getByType('action', 2);
			$response = false;
			if (empty($moduleName)) {
				if ($this->hasLogin()) {
					$defaultModule = AppConfig::main('default_module');
					if (!empty($defaultModule) && $defaultModule !== 'Home' && \App\Privilege::isPermitted($defaultModule)) {
						$moduleName = $defaultModule;
						$qualifiedModuleName = $defaultModule;
						$view = 'List';
						if ($moduleName === 'Calendar') {
							$view = 'Calendar';
						}
					} else {
						$moduleName = 'Home';
						$qualifiedModuleName = 'Home';
						$view = 'DashBoard';
					}
				} else {
					$moduleName = 'Users';
					$qualifiedModuleName = $moduleName;
					$view = 'Login';
				}
				$request->set('module', $moduleName);
				$request->set('view', $view);
			}
			if (!empty($action)) {
				$componentType = 'Action';
				$componentName = $action;
				\App\Config::setJsEnv('action', $action);
			} else {
				$componentType = 'View';
				if (empty($view)) {
					$view = 'Index';
				}
				$componentName = $view;
				\App\Config::setJsEnv('view', $view);
			}
			\App\Process::$processName = $componentName;
			\App\Process::$processType = $componentType;
			\App\Config::setJsEnv('module', $moduleName);
			if ($qualifiedModuleName && stripos($qualifiedModuleName, 'Settings') === 0 && empty(\App\User::getCurrentUserId())) {
				header('location: ' . AppConfig::main('site_URL'), true);
			}

			$handlerClass = Vtiger_Loader::getComponentClassName($componentType, $componentName, $qualifiedModuleName);
			$handler = new $handlerClass();
			if (!$handler) {
				\App\Log::error("HandlerClass: $handlerClass", 'Loader');
				throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND', 405);
			}
			if (AppConfig::main('csrfProtection') && AppConfig::main('systemMode') !== 'demo') { // Ensure handler validates the request
				$handler->validateRequest($request);
			}
			if ($handler->loginRequired()) {
				$this->checkLogin($request);
			}
			if ($handler->isSessionExtend()) {
				\App\Session::set('last_activity', \App\Process::$startTime);
			}
			if ($moduleName === 'ModComments' && $view === 'List') {
				header('location: index.php?module=Home&view=DashBoard');
			}
			$skipList = ['Users', 'Home', 'CustomView', 'Import', 'Export', 'Install', 'ModTracker'];
			if (!in_array($moduleName, $skipList) && stripos($qualifiedModuleName, 'Settings') === false) {
				$this->triggerCheckPermission($handler, $request);
			} elseif (stripos($qualifiedModuleName, 'Settings') === 0 || in_array($moduleName, $skipList)) {
				$handler->checkPermission($request);
			}
			$this->triggerPreProcess($handler, $request);
			$response = $handler->process($request);
			$this->triggerPostProcess($handler, $request);
		} catch (Throwable $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			$messageHeader = 'LBL_ERROR';
			if ($e instanceof \App\Exceptions\NoPermittedToRecord || $e instanceof WebServiceException) {
				$messageHeader = 'LBL_PERMISSION_DENIED';
			} elseif ($e instanceof \App\Exceptions\Security) {
				$messageHeader = 'LBL_BAD_REQUEST';
			} elseif ($e instanceof \yii\db\Exception) {
				$messageHeader = 'LBL_ERROR';
			}
			\vtlib\Functions::throwNewException($e, false, $messageHeader);
			if (!$request->isAjax()) {
				if (AppConfig::debug('DISPLAY_EXCEPTION_BACKTRACE')) {
					echo '<pre class="my-5 mx-auto card p-3 u-w-fit shadow">' . App\Purifier::encodeHtml(str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', $e->__toString())) . '</pre>';
					$response = false;
				}
				if (AppConfig::debug('DISPLAY_EXCEPTION_LOGS')) {
					echo '<pre class="my-5 mx-auto card p-3 u-w-fit shadow">' . App\Purifier::encodeHtml(str_replace(ROOT_DIRECTORY . DIRECTORY_SEPARATOR, '', \App\Log::getlastLogs())) . '</pre>';
					$response = false;
				}
			}
			if (AppConfig::main('systemMode') === 'test') {
				file_put_contents('cache/logs/request.log', print_r($request->getAll(), true));
				if (function_exists('apache_request_headers')) {
					file_put_contents('cache/logs/request.log', print_r(apache_request_headers(), true));
				}
				throw $e;
			}
		}
		if (is_object($response)) {
			$response->emit();
		}
	}

	/**
	 * Trigger check permission.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	protected function triggerCheckPermission(\App\Controller\Base $handler, \App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (empty($moduleModel)) {
			\App\Log::error('HandlerModule: ' . $moduleName, 'Loader');
			throw new \App\Exceptions\AppException('ERR_MODULE_DOES_NOT_EXIST||' . $moduleName, 405);
		}
		$this->userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->userPrivilegesModel->hasModulePermission($moduleName)) {
			$handler->checkPermission($request);

			return true;
		}
		\App\Log::error("No permissions to the module: $moduleName", 'NoPermitted');
		throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
	}

	/**
	 * Trigger pre process.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @return bool
	 */
	protected function triggerPreProcess(\App\Controller\Base $handler, \App\Request $request)
	{
		if ($request->isAjax()) {
			$handler->preProcessAjax($request);
			return true;
		}
		$handler->preProcess($request);
	}

	/**
	 * Trigger post process.
	 *
	 * @param \App\Controller\Base $handler
	 * @param \App\Request         $request
	 *
	 * @return bool
	 */
	protected function triggerPostProcess(\App\Controller\Base $handler, \App\Request $request)
	{
		if ($request->isAjax()) {
			$handler->postProcessAjax($request);
			return true;
		}
		$handler->postProcess($request);
	}

	/**
	 * Content Security Policy token.
	 */
	public function cspInitToken()
	{
		if (!App\Session::has('CSP_TOKEN') || App\Session::get('CSP_TOKEN_TIME') < time()) {
			App\Session::set('CSP_TOKEN', sha1(AppConfig::main('application_unique_key') . time()));
			App\Session::set('CSP_TOKEN_TIME', strtotime('+5 minutes'));
		}
	}
}
