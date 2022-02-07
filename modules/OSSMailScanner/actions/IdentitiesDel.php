<?php

/**
 * OSSMailScanner IdentitiesDel action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_IdentitiesDel_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->getInteger('id');
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->deleteIdentities($id);

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
