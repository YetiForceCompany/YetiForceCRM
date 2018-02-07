<?php

/**
 * OSSMail SaveRcConfig action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_SaveRcConfig_Action extends \App\Controller\Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
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
