<?php
/**
 * Login base file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/ConfigUtils.php';
/**
 * Login class.
 */
class Login extends \App\WebUi
{
	/**
	 * Process.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function process()
	{
		parent::process();
		if ($this->isLoggedIn()) {
			header('location: ' . \App\Config::main('site_URL'), true, 301);
		}
		\App\Process::$processType = 'Action';
		\App\Process::$processName = 'Login';
		$handlerClass = \Vtiger_Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, 'Users');
		if (!class_exists($handlerClass)) {
			throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND', 405);
		}
		$request = \App\Request::init();
		$handler = new $handlerClass();
		$handler->checkPermission($request);
		$response = $handler->process($request);
		if (is_object($response)) {
			$response->emit();
		}
	}
}

$login = new Login();
$login->process();
