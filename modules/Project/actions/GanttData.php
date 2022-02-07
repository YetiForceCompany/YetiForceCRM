<?php

/**
 * GanttDataAjax.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Project_GanttData_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$gantt = new Project_Gantt_Model();
		if (!$request->has('projectId')) {
			$data = $gantt->getAllData($request->getByType('viewname', 2));
		} else {
			$data = $gantt->getById($request->getInteger('projectId'));
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
