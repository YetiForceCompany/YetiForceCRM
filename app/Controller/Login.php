<?php
/**
 * Login action controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Login class.
 */
class Login extends WebUI
{
	/**
	 * Process.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process()
	{
		$this->init();
		\App\Process::$processType = 'Action';
		\App\Process::$processName = 'Login';
		$handlerClass = \Vtiger_Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, 'Users');
		if (!class_exists($handlerClass)) {
			throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND', 405);
		}

		$handler = new $handlerClass($this->request);
		$handler->checkPermission();
		$response = $handler->process();
		$response->setEnv(\App\Config::getJsEnv());
		$response->emit();
	}
}
