<?php
namespace App\Main;

/**
 * Basic class to handle files
 *
 * @package YetiForce.Files
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class File
{

	public function process(\App\Request $request)
	{
		if (\AppConfig::main('forceSSL') && !\App\RequestUtil::getBrowserInfo()->https) {
			header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", true, 301);
		}
		if (\AppConfig::main('forceRedirect')) {
			$requestUrl = (\App\RequestUtil::getBrowserInfo()->https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if (stripos($requestUrl, \AppConfig::main('site_URL')) !== 0) {
				header('Location: ' . \AppConfig::main('site_URL'), true, 301);
			}
		}
		\Vtiger_Session::init();
		$this->getLogin();
		$moduleName = $request->getModule();
		$action = $request->get('action');
		if (!$moduleName || !$action) {
			throw new \Exception\NoPermitted('Method Not Allowed', 405);
		}
		$handlerClass = \Vtiger_Loader::getComponentClassName('File', $action, $moduleName);
		$handler = new $handlerClass();
		if ($handler) {
			$method = $request->getRequestMethod();
			$permissionFunction = $method . 'CheckPermission';
			if (!$handler->$permissionFunction($request)) {
				throw new \Exception\NoPermitted('LBL_NOT_ACCESSIBLE', 403);
			}
			$handler->$method($request);
		}
	}

	/**
	 * Function to get the instance of the logged in User
	 * @return Users object
	 */
	public function getLogin()
	{
		if (\Vtiger_Session::has('authenticated_user_id')) {
			$userid = \Vtiger_Session::get('authenticated_user_id');
			if ($userid && \AppConfig::main('application_unique_key') === \Vtiger_Session::get('app_unique_key')) {
				\App\User::getCurrentUserModel();
				$user = \CRMEntity::getInstance('Users');
				$user->retrieveCurrentUserInfoFromFile($userid);
				return $user;
			}
		}
		throw new \Exception\NoPermitted('Unauthorized', 401);
	}
}
