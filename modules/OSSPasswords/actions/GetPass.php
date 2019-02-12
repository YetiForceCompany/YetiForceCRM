<?php

/**
 * OSSPasswords GetPass action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_GetPass_Action extends \App\Controller\Action
{
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$record = $request->getInteger('record');
		if ($record) {
			$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $record);
			if (!$recordPermission) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');

		if ($record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}

		$pass = $recordModel->getPassword($record);
		if ($pass === false) {
			$result = ['success' => false];
		} else {
			$result = ['success' => true, 'password' => $pass];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
