<?php
/**
 * Login action controller class.
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
 * Login class.
 */
class Login extends WebUI
{
	/**
	 * Process function.
	 *
	 * @return void
	 */
	public function process()
	{
		$this->requirementsValidation();
		\App\Process::$processType = 'Actions';
		\App\Process::$processName = 'Login';

		$handlerClass = \App\Loader::getComponentClassName(\App\Process::$processType, \App\Process::$processName, 'Users');
		$handler = new $handlerClass($this->request, new \App\Response());
		if (\App\Config::main('csrfProtection') && 'demo' !== \App\Config::main('systemMode')) {
			$handler->validateRequest();
		}
		$handler->preProcess();
		if ($handler->checkPermission()) {
			$handler->process();
		}
		$handler->postProcess();
		$handler->response->emit();
	}
}
