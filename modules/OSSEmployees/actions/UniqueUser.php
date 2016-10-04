<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSEmployees_UniqueUser_Action extends Vtiger_Action_Controller
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
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule();

		$userId = $request->get('userId');

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

		$userExists = $recordModel->checkUser($userId);

		if (!$userExists) {
			$result = array('success' => false, 'message' => vtranslate('LBL_USER_EXISTS', $moduleName));
		} else {
			$result = array('success' => true);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
