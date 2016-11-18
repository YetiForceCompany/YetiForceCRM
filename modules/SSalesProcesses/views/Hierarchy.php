<?php

/**
 * Class to show hierarchy 
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_Hierarchy_View extends Vtiger_View_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		if (!App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->get('record'))) {
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
		$hierarchy = $recordModel->getHierarchy();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('HIERARCHY', $hierarchy);
		$viewer->view('Hierarchy.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}
}
