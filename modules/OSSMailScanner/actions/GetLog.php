<?php

/**
 * OSSMailScanner GetLog action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_GetLog_Action extends Vtiger_Action_Controller
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

		$startNumber = $request->get('start_number');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$log = $recordModel->get_scan_history($startNumber);

		$response = new Vtiger_Response();
		$response->setResult($log);
		$response->emit();
	}
}
