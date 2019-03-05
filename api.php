<?php
/**
 * Api base file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require __DIR__ . '/include/ConfigUtils.php';
/**
 * Api class.
 */
class Api extends \App\WebUi
{
	/**
	 * Process.
	 */
	public function process()
	{
		parent::process();
		try {
			$response = new \App\Response();
			\App\Session::init();
			if (!$this->isLoggedIn()) {
				throw new \App\Exceptions\Unauthorized('LBL_LOGIN_IS_REQUIRED', 401);
			}
			if (\App\Config::main('csrfProtection')) {
				require_once 'config/csrf_config.php';
				\CsrfMagic\Csrf::init();
			}
			$request = \App\Request::init();
			$moduleName = $request->getModule();
			$qualifiedModuleName = $request->getModule(false);
			$action = $request->getByType('action', \App\Purifier::ALNUM);
			\App\Process::$processType = 'Action';
			\App\Process::$processName = $action;

			$handlerClass = \Vtiger_Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, $qualifiedModuleName);
			if (!class_exists($handlerClass)) {
				throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND', 405);
			}
			if (!\App\Privilege::isPermitted($moduleName)) {
				throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
			}
			$handler = new $handlerClass();
			if (\App\Config::main('csrfProtection') && \App\Config::main('systemMode') !== 'demo') {
				$handler->validateRequest($request);
			}
			$handler->checkPermission($request);
			$result = $handler->process($request);
			$response->setEnv(\App\Config::getJsEnv());
			$response->setResult($result);
			$response->emit();
		} catch (Throwable $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			$response->setError($e->getCode(), $e->getMessage(), $e->getTraceAsString());
			$response->emit();
		}
	}
}

$api = new Api();
$api->process();
