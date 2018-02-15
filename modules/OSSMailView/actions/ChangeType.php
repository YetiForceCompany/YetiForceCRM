<?php

/**
 * Change type action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_ChangeType_Action extends Vtiger_Mass_Action
{
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
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$selectedIds = $request->get('data');
		$mailType = $request->get('mail_type');
		if ($selectedIds == 'all') {
			$recordModel->changeTypeAllRecords($mailType);
		} else {
			$recordModel->changeTypeSelectedRecords($selectedIds, $mailType);
		}
		$response = new Vtiger_Response();
		$response->setResult(\App\Language::translate('LBL_ChangeTypeOK', $moduleName));
		$response->emit();
	}
}
