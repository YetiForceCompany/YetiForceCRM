<?php
/**
 * Api base controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * WebApi class.
 */
class WebApi extends WebUI
{
	/**
	 * Process.
	 */
	public function process()
	{
		$response = new \App\Response();
		if (!\App\User::isLoggedIn()) {
			throw new \App\Exceptions\Unauthorized('ERR_LOGIN_IS_REQUIRED', 401);
		}
		if (\App\Config::main('csrfProtection')) {
			require_once 'config/csrf_config.php';
			\CsrfMagic\Csrf::init();
		}
		\App\Process::$processType = 'Action';
		\App\Process::$processName = $this->request->getByType('action', \App\Purifier::ALNUM);

		$handlerClass = \Vtiger_Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, $this->request->getModule(false));
		if (!class_exists($handlerClass)) {
			throw new \App\Exceptions\AppException('ERR_HANDLER_NOT_FOUND', 405);
		}
		if (!\App\Privilege::isPermitted($this->request->getModule())) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
		}
		$handler = new $handlerClass($this->request);
		if (\App\Config::main('csrfProtection') && 'demo' !== \App\Config::main('systemMode')) {
			$handler->validateRequest();
		}
		$handler->checkPermission();
		$handler->process();
		$response->setEnv(\App\Config::getJsEnv());
		$response->setResult($handler->process());
		$response->emit();
	}
}
