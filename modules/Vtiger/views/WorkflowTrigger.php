<?php

/**
 * Vtiger WorkflowTrigger view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_WorkflowTrigger_View extends Vtiger_IndexAjax_View
{

	public function checkPermission(\App\Request $request)
	{
		if (!(Users_Privileges_Model::isPermitted($request->getModule(), 'WorkflowTrigger', $request->get('record')))) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		$workflows = (new VTWorkflowManager())->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
		foreach ($workflows as $id => $workflow) {
			if (!$workflow->evaluate(Vtiger_Record_Model::getInstanceById($record))) {
				unset($workflows[$id]);
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('WORKFLOWS', $workflows);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('WorkflowTrigger.tpl', $moduleName);
	}
}
