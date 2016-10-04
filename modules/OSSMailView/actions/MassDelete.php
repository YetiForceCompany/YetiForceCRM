<?php

/**
 * Mass delete action class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_MassDelete_Action extends Vtiger_Mass_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function postProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$recordModel = new OSSMailView_Record_Model();
		$recordModel->setModule($moduleName);

		$recordIds = $this->getRecordsListFromRequest($request);

		foreach ($recordIds as $recordId) {
			if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
				$recordModel->delete_rel($recordId);
				$recordModel->delete();
			} else {
				$permission = 'No';
			}
		}

		if ($permission === 'No') {
			throw new \Exception\AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		$cvId = $request->get('viewname');
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
		$response->emit();
	}
}
