<?php
/**
 * UpcomingProjectTasks chart class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * UpcomingProjectTasks class.
 */
class ProjectTask_UpcomingProjectTasks_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Contains status values which indicate that the record is open.
	 *
	 * @var string[]
	 */
	protected $openStatus = ['PLL_PLANNED', 'PLL_ON_HOLD', 'PLL_IN_PROGRESSING', 'PLL_IN_APPROVAL'];

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = App\User::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUser->getId());
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $request->getInteger('page'));
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'ProjectTask', $request->getByType('owner', 2));
		$params = ['projecttaskstatus' => $this->openStatus];
		if (!$request->isEmpty('projecttaskpriority') && $request->getByType('projecttaskpriority', 'Standard') !== 'all') {
			$params['projecttaskpriority'] = $request->getByType('projecttaskpriority', 'Standard');
		}
		$projectTasks = ($owner === false) ? [] : ProjectTask_Module_Model::getRecordsByStatus($params, $pagingModel, $owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTTASKS', $projectTasks);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', \AppConfig::main('title_max_length'));
		$viewer->assign('OWNER', $owner);
		$viewer->assign('TICKETPRIORITY', $params['projecttaskpriority'] ?? '');
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_UPCOMING_PROJECT_TASKS');
		$viewer->assign('LISTVIEWLINKS', true);
		if ($request->has('content')) {
			$viewer->view('dashboards/UpcomingProjectTasksContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/UpcomingProjectTasks.tpl', $moduleName);
		}
	}
}
