<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMail_checkMails_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
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
