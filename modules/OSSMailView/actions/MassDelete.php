<?php

/**
 * Mass delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_MassDelete_Action extends Vtiger_Mass_Action
{
	use App\Controller\ClearProcess;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'MassDelete')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = new OSSMailView_Record_Model();
		$recordModel->setModule($moduleName);

		$recordIds = self::getRecordsListFromRequest($request);

		$permission = true;
		foreach ($recordIds as $recordId) {
			if (\App\Privilege::isPermitted($moduleName, 'Delete', $recordId)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$recordModel->deleteRel($recordId);
				$recordModel->delete();
			} else {
				$permission = false;
			}
		}

		if (!$permission) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}

		$cvId = $request->getByType('viewname', 2);
		$response = new Vtiger_Response();
		$response->setResult(['viewname' => $cvId, 'module' => $moduleName]);
		$response->emit();
	}
}
