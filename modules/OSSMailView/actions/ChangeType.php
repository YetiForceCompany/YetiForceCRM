<?php

/**
 * Change type action class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_ChangeType_Action extends Vtiger_Mass_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
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
		$response->setResult(vtranslate('LBL_ChangeTypeOK', $moduleName));
		$response->emit();
	}
}
