<?php

namespace App\Main;

/**
 * Basic class to handle files.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class File
{
	public function process(\App\Request $request)
	{
		if (\AppConfig::main('forceSSL') && !\App\RequestUtil::getBrowserInfo()->https) {
			header("location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", true, 301);
		}
		if (\AppConfig::main('forceRedirect')) {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if (stripos($requestUrl, \AppConfig::main('site_URL')) !== 0) {
				header('location: ' . \AppConfig::main('site_URL'), true, 301);
			}
		}
		\App\Session::init();
		$this->getLogin();
		$moduleName = $request->getModule();
		$action = $request->getByType('action', 1);
		if (!$moduleName || !$action) {
			throw new \App\Exceptions\NoPermitted('Method Not Allowed', 405);
		}
		\App\Process::$processName = $action;
		\App\Process::$processType = 'File';
		$handlerClass = \Vtiger_Loader::getComponentClassName('File', $action, $moduleName);
		$handler = new $handlerClass();
		if ($handler) {
			$method = $request->getRequestMethod();
			$permissionFunction = $method . 'CheckPermission';
			if (!$handler->$permissionFunction($request)) {
				throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 403);
			}
			$handler->$method($request);
		}
	}

	/**
	 * Function to get the instance of the logged in User.
	 *
	 * @return Users object
	 */
	public function getLogin()
	{
		if (\App\Session::has('authenticated_user_id')) {
			$userid = \App\Session::get('authenticated_user_id');
			if ($userid && \AppConfig::main('application_unique_key') === \App\Session::get('app_unique_key')) {
				return \App\User::getCurrentUserModel();
			}
		}
		throw new \App\Exceptions\NoPermitted('Unauthorized', 401);
	}
}
