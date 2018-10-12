<?php

/**
 * OSSMail SetUser action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$users = OSSMail_Autologin_Model::getAutologinUsers();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule()) || !isset($users[$request->getInteger('user')])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$user = $request->getInteger('user');
		\App\Session::set('AutoLoginUser', $user);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
