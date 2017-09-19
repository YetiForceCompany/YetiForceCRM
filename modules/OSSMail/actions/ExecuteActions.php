<?php

/**
 * OSSMail execute actions action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMail_ExecuteActions_Action extends Vtiger_Action_Controller
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
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
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
