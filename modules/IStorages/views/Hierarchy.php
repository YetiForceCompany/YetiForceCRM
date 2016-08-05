<?php
/**
 * Class to show hierarchy 
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class IStorages_Hierarchy_View extends Vtiger_View_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}
	
	function preProcess(Vtiger_Request $request, $display = true) {
	}

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$hierarchy = $recordModel->getHierarchy();
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('HIERARCHY', $hierarchy);
		$viewer->view('Hierarchy.tpl', $moduleName);
	}
	
	function postProcess(Vtiger_Request $request) {
	}
}
