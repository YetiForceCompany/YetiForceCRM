<?php

/**
 * OSSMail checkMails action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_checkMails_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$users = $request->get('users');
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
