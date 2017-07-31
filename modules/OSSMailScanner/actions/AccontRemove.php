<?php

/**
 * OSSMailScanner AccontRemove action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_AccontRemove_Action extends Vtiger_Action_Controller
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
		$id = $request->get('id');
		$recordModel_OSSMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModel_OSSMailScanner->AccontDelete($id);
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true, 'data' => \App\Language::translate('AccontDeleteOK', 'OSSMailScanner')));
		$response->emit();
	}
}
