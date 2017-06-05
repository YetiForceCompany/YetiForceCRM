<?php

/**
 * OSSMailScanner IdentitiesDel action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_IdentitiesDel_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->get('id');
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->deleteIdentities($id);

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
