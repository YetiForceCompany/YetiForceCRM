<?php

/**
 * Vtiger WorkflowTrigger view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_WorkflowTrigger_View extends Vtiger_IndexAjax_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'WorkflowTrigger')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
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
