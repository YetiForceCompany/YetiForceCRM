<?php

/**
 * Mass delete action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_MassDelete_Action extends Vtiger_Mass_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request)
	{
		return true;
	}

	public function postProcess(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{

		$moduleName = $request->getModule();
		$recordModel = new OSSMailView_Record_Model();
		$recordModel->setModule($moduleName);

		$recordIds = $this->getRecordsListFromRequest($request);

		$permission = true;
		foreach ($recordIds as $recordId) {
			if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName); // YTfixme: not 100% sure thats whats expected
				$recordModel->delete_rel($recordId);
				$recordModel->delete();
			} else {
				$permission = false;
			}
		}

		if (!$permission) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}

		$cvId = $request->get('viewname');
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
		$response->emit();
	}
}
