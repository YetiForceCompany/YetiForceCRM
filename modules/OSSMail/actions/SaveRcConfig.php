<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMail_SaveRcConfig_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$param = $request->get('updatedFields');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$result = ['success' => true, 'data' => $recordModel->setConfigData($param)];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
