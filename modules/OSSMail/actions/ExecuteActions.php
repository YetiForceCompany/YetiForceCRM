<?php

/**
 * OSSMail execute actions action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMail_ExecuteActions_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$params = $request->getArray('params');
		if (isset($params['newModule']) && !\App\Privilege::isPermitted($params['newModule'], 'DetailView', $params['newCrmId'])) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (isset($params['crmid']) && !\App\Privilege::isPermitted(\App\Record::getType($params['crmid']), 'DetailView', $params['crmid'])) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		$params = $request->getArray('params');
		$instance = OSSMailView_Record_Model::getCleanInstance('OSSMailView');

		if ('addRelated' === $mode) {
			if (!\App\Privilege::isPermitted($params['newModule'], 'DetailView', $params['newCrmId'])) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$data = $instance->addRelated($params);
		} elseif ('removeRelated' === $mode) {
			$data = $instance->removeRelated($params);
		}

		$result = ['success' => true, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
