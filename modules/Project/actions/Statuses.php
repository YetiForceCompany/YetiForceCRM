<?php

/**
 * GanttDataAjax.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafał Pośpiech <r.pospiech@yetiforce.com>
 */
class Project_Statuses_Action extends Vtiger_BasicAjax_Action
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
