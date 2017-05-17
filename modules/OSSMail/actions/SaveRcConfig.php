<?php

/**
 * OSSMail SaveRcConfig action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_SaveRcConfig_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$param = $request->get('updatedFields');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$result = ['success' => true, 'data' => $recordModel->setConfigData($param)];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
