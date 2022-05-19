<?php

/**
 * OSSMail SetUser action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_SetUser_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$users = OSSMail_Autologin_Model::getAutologinUsers();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule()) || !isset($users[$request->getInteger('user')])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$user = $request->getInteger('user');
		\App\Session::set('AutoLoginUser', $user);
		OSSMail_Autologin_Model::updateActive($user);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
