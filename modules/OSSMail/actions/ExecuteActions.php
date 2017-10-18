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
		$params = $request->getArray('params');
		if (!\App\Privilege::isPermitted(\App\Record::getType($params['crmid']), 'DetailView', $params['crmid'])) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		$params = $request->getArray('params');
		$instance = Vtiger_Record_Model::getCleanInstance('OSSMailView');

		if ($mode == 'addRelated') {
			if (!\App\Privilege::isPermitted($params['newModule'], 'DetailView', $params['newCrmId'])) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
			}
			$data = $instance->addRelated($params);
		} elseif ($mode == 'removeRelated') {
			$data = $instance->removeRelated($params);
		}

		$result = ['success' => true, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
