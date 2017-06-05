<?php

/**
 * OSSMailScanner SaveRcConfig action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_SaveRcConfig_Action extends Vtiger_Action_Controller
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
		$conf_type = $request->get('ct');
		$type = $request->get('type');
		$vale = $request->get('vale');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$result = array('success' => true, 'data' => $recordModel->setConfigWidget($conf_type, $type, $vale));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
