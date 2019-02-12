<?php

/**
 * OSSMail check mails action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_CheckMails_Action extends \App\Controller\Action
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
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule()) || array_diff($request->getArray('users', 'Integer'), array_keys(OSSMail_Autologin_Model::getAutologinUsers()))) {
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
		$users = $request->getArray('users', 'Integer');
		$output = [];
		if (count($users) > 0) {
			OSSMail_Record_Model::updateMailBoxmsgInfo($users);
			$output = OSSMail_Record_Model::getMailBoxmsgInfo($users);
		}
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
