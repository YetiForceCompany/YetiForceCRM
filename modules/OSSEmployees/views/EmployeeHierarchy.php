<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSEmployees_EmployeeHierarchy_View extends Vtiger_View_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$hierarchy = $recordModel->getEmployeeHierarchy();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('EMPLOYEES_HIERARCHY', $hierarchy);
		$viewer->view('EmployeeHierarchy.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}
}
