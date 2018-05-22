<?php

/**
 * GanttDataAjax.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafał Pośpiech <r.pospiech@yetiforce.com>
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
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
		}
		$ids = [];
		if ($request->has('projectId')) {
			$ids = [$request->getByType('projectId', 2)];
		} else {
			// If in next process throws an exception, all sensitive data can leak (do not show error in prod env)
			$gantt = new Project_Gantt_Model();
			$data = $gantt->getAllGanttProjects($request->getByType('viewname', 2));
			if (!empty($data) && !empty($data['tasks'])) {
				foreach ($data['tasks'] as $task) {
					$ids[] = $task['id'];
				}
			}
		}
		foreach ($ids as $id) {
			if (!\App\Privilege::isPermitted($request->getModule(), 'Gantt', $id)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$gantt = new Project_Gantt_Model();
		$data = [];
		if (!$request->has('projectId')) {
			$data = $gantt->getAllGanttProjects($request->getByType('viewname', 2));
		} else {
			$data = $gantt->getGanttProject($request->getByType('projectId'), $request->getByType('viewname', 2));
		}
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
