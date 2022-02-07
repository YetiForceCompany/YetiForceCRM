<?php
/**
 * CompletedProjectTasks chart class.
 *
 * @package Dashboards
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * CompletedProjectTasks class.
 */
class ProjectTask_CompletedProjectTasks_Dashboard extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $request->getInteger('page'));
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, $moduleName, $request->getByType('owner', 2));
		$completedStatus = Settings_RealizationProcesses_Module_Model::getStatusNotModify()[$moduleName]['status'] ?? [];
		$completedStatus = array_filter($completedStatus);
		$params = ['projecttaskstatus' => $completedStatus];
		if (!$request->isEmpty('projecttaskpriority') && 'all' !== $request->getByType('projecttaskpriority', 'Standard')) {
			$params['projecttaskpriority'] = $request->getByType('projecttaskpriority', 'Standard');
		}
		$projectTasks = (false === $owner || empty($completedStatus)) ? [] : ProjectTask_Module_Model::getRecordsByStatus($params, $pagingModel, $owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTTASKS', $projectTasks);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('NAMELENGTH', \App\Config::main('title_max_length'));
		$viewer->assign('OWNER', $owner);
		$viewer->assign('TICKETPRIORITY', $params['projecttaskpriority'] ?? '');
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_COMPLETED_PROJECT_TASKS');
		$viewer->assign('LISTVIEWLINKS', true);
		$viewer->assign('STATUS', implode('##', $completedStatus));
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance($moduleName)->getAccessibleUsers());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance($moduleName)->getAccessibleGroups());
		if ($request->has('content')) {
			$viewer->view('dashboards/CompletedProjectTasksContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CompletedProjectTasks.tpl', $moduleName);
		}
	}
}
