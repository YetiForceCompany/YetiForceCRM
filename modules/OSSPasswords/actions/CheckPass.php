<?php

/**
 * OSSPasswords CheckPass action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSPasswords_CheckPass_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
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
