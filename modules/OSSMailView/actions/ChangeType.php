<?php

/**
 * Change type action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_ChangeType_Action extends Vtiger_Mass_Action
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$selectedIds = $request->get('data');
		$mail_type = $request->get('mail_type');
		if ($selectedIds == 'all') {
			$recordModel->ChangeTypeAllRecords($mail_type);
		} else {
			$recordModel->ChangeTypeSelectedRecords($selectedIds, $mail_type);
		}
		$response = new Vtiger_Response();
		$response->setResult(\App\Language::translate('LBL_ChangeTypeOK', $moduleName));
		$response->emit();
	}
}
