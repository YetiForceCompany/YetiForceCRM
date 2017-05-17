<?php

/**
 * OSSMail executeActions action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_executeActions_Action extends Vtiger_Action_Controller
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
		$mode = $request->get('mode');
		$params = $request->get('params');
		$instance = Vtiger_Record_Model::getCleanInstance('OSSMailView');

		if ($mode == 'addRelated')
			$data = $instance->addRelated($params);

		if ($mode == 'removeRelated')
			$data = $instance->removeRelated($params);

		$result = array('success' => true, 'data' => $data);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
