<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSPasswords_CheckPass_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$password = $request->get('password');

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

		$passOK = $recordModel->checkPassword($password);

		if ($passOK['error'] === true) {
			$result = array('success' => false, 'message' => $passOK['message']);
		} else {
			$result = array('success' => true, 'message' => '');
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
