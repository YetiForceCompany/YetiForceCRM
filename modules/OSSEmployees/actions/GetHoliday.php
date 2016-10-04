<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSEmployees_GetHoliday_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);

		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule();

		$id = $request->get('id');
		$year = $request->get('year');

		$sourceData = array();

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

		$holiday['workDay'] = $recordModel->getHoliday($id, $year);
		$holiday['entitlement'] = $recordModel->getHolidaysEntitlement($id, $year);

		if (!$holiday) {
			$result = array('success' => false, 'message' => vtranslate('LBL_FAILED_TO_IMPORT_INFO', $moduleName));
		} else {
			$result = array('success' => true, 'holiday' => $holiday);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
