<?php

/**
 * OSSEmployees EmployeeHierarchy view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSEmployees_EmployeeHierarchy_View extends Vtiger_View_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		
	}

	public function process(\App\Request $request)
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

	public function postProcess(\App\Request $request)
	{
		
	}
}
