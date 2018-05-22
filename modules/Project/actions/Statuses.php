<?php

/**
 * GanttDataAjax.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafał Pośpiech <r.pospiech@yetiforce.com>
 */
class Project_Statuses_Action extends \App\Controller\Action
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
		$ids = [];
		if ($request->has('projectId')) {
			$ids = [$request->getByType('projectId', 2)];
		} else {
			// If in next process throws an exception, all sensitive data can leak (do not show error in prod env)
			$gantt = new Project_Gantt_Model();
			$data = $gantt->getAllData($request->getByType('viewname', 2));
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
		$data=['project'=>[], 'milestone'=>[], 'task'=>[]];
		$closingStatuses = Settings_RealizationProcesses_Module_Model::getStatusNotModify();
		$projectClosing = [];
		if (!empty($closingStatuses['Project']) && !empty($closingStatuses['Project']['status'])) {
			$projectClosing = $closingStatuses['Project']['status'];
		}
		$milestoneClosing=[];
		if (!empty($closingStatuses['ProjectMilestone']) && !empty($closingStatuses['ProjectMilestone']['status'])) {
			$milestoneClosing = $closingStatuses['ProjectMilestone']['status'];
		}
		$taskClosing=[];
		if (!empty($closingStatuses['ProjectTask']) && !empty($closingStatuses['ProjectTask']['status'])) {
			$taskClosing = $closingStatuses['ProjectTask']['status'];
		}
		$allStatuses = Project_Gantt_Model::getPicklistValues();
		$data['project'] = array_values(array_filter($allStatuses['Project']['projectstatus'], function ($status) use ($projectClosing) {
			return !in_array($status['value'], $projectClosing);
		}));
		$data['milestone'] = array_values(array_filter($allStatuses['ProjectMilestone']['projectmilestone_status'], function ($status) use ($milestoneClosing) {
			return !in_array($status['value'], $milestoneClosing);
		}));
		$data['task'] = array_values(array_filter($allStatuses['ProjectTask']['projecttaskstatus'], function ($status) use ($taskClosing) {
			return !in_array($status['value'], $taskClosing);
		}));
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
