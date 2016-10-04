<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSPasswords_GetPass_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$record = $request->get('record');
		if ($record) {
			$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $record);
			if (!$recordPermission) {
				throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
			}
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if ($record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}

		$pass = $recordModel->getPassword($record);
		if ($pass === false) {
			$result = array('success' => false);
		} else {
			$result = array('success' => true, 'password' => $pass);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
