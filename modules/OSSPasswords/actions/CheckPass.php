<?php

/**
 * OSSPasswords CheckPass action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_CheckPass_Action extends \App\Controller\Action
{
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());

		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getCleanInstance($request->getModule());
		$passOK = $recordModel->checkPassword($request->getRaw('password'));
		if (true === $passOK['error']) {
			$result = ['success' => false, 'message' => $passOK['message']];
		} else {
			$result = ['success' => true, 'message' => ''];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
