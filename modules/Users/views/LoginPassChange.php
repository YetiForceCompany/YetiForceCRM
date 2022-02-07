<?php
/**
 * Login password change view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Login password change view class.
 */
class Users_LoginPassChange_View extends Users_Login_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$bruteForceInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bruteForceInstance->isActive() && $bruteForceInstance->isBlockedIp()) {
			$viewer->assign('MESSAGE', 'LBL_IP_IS_BLOCKED');
		} else {
			try {
				if ($request->isEmpty('token')) {
					throw new \App\Exceptions\AppException('ERR_NO_TOKEN', 405);
				}
				$token = $request->getByType('token', \App\Purifier::ALNUM);
				$tokenData = \App\Utils\Tokens::get($token, false);
				if (empty($tokenData)) {
					throw new \App\Exceptions\AppException('ERR_TOKEN_DOES_NOT_EXIST', 405);
				}
				$viewer->assign('TOKEN', $token);
			} catch (\App\Exceptions\AppException $th) {
				if ($bruteForceInstance->isActive()) {
					$bruteForceInstance->incAttempts();
				}
				$viewer->assign('MESSAGE', $th->getDisplayMessage());
			}
		}
		$viewer->view('LoginPassChange.tpl', 'Users');
	}
}
