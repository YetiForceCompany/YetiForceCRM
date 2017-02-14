<?php

/**
 * Variable panel view class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_VariablePanel_View extends Vtiger_View_Controller
{

	/**
	 * Checking permissions
	 * @param Vtiger_Request $request
	 * @throws \Exception\AppException
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($moduleName) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if ($recordId && !\App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	/**
	 * Process function
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SELECTED_MODULE', $request->get('selectedModule'));
		$viewer->assign('PARSER_TYPE', $request->get('type'));
		$viewer->assign('GRAY', true);
		$viewer->view('VariablePanel.tpl', $moduleName);
	}
}
