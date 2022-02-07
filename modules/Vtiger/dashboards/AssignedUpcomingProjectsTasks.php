<?php

/**
 * Vtiger AssignedUpcomingProjectsTasks dashboard class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_AssignedUpcomingProjectsTasks_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$data = $request->getAll();

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Leads');
		} else {
			$owner = $request->getByType('owner', 2);
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$projectsTasks = (false === $owner) ? [] : $moduleModel->getAssignedProjectsTasks('upcoming', $pagingModel, $owner);
		$currentDate = date('Y-m-d');

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'ProjectTask');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTSTASKS', $projectsTasks);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$title_max_length = \App\Config::main('title_max_length');
		$href_max_length = \App\Config::main('href_max_length');
		$viewer->assign('NAMELENGTH', $title_max_length);
		$viewer->assign('HREFNAMELENGTH', $href_max_length);
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_SCHEDULED_ACTIVITIES');
		$viewer->assign('OWNER', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_CONDITIONS', ['condition' => ['>=', 'targetenddate', $currentDate]]);
		if ($request->has('content')) {
			$viewer->view('dashboards/AssignedProjectsTasksContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AssignedProjectsTasks.tpl', $moduleName);
		}
	}
}
