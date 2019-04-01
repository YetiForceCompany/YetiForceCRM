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
	 * Process function.
	 *
	 * @return void
	 */
	public function process()
	{
		$this->requirementsValidation();
		if (\App\Config::main('csrfProtection')) {
			require_once 'config/csrf_config.php';
			\CsrfMagic\Csrf::init();
		}
		\App\Process::$processType = 'Actions';
		\App\Process::$processName = $this->request->getByType('action', \App\Purifier::ALNUM);

		$handlerClass = \App\Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, $this->request->getModule(false));
		$handler = new $handlerClass($this->request, new \App\Response());
		if ('http' !== $handler->protocol && 'mix' !== $handler->protocol) {
			throw new \App\Exceptions\InvalidProtocol('ERR_INVALID_PROTOCOL', 400);
		}
		if (\App\Config::main('csrfProtection') && 'demo' !== \App\Config::main('systemMode')) {
			$handler->validateRequest();
		}
		if ($handler->loginRequired && !\App\User::isLoggedIn()) {
			throw new \App\Exceptions\Unauthorized('ERR_LOGIN_IS_REQUIRED', 401);
		}
		$handler->preProcess();
		if ($handler->checkPermission()) {
			$handler->process();
		}
		$handler->postProcess();
		$handler->emit();
	}
}
