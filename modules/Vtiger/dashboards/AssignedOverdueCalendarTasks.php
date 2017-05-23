<?php

/**
 * Vtiger AssignedOverdueCalendarTasks dashboard class
 * @package YetiForce.Dashboard
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_AssignedOverdueCalendarTasks_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$page = $request->get('page');
		$linkId = $request->get('linkid');
		$sortOrder = $request->get('sortorder');
		$orderBy = $request->get('orderby');
		$data = $request->getAll();

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		else
			$owner = $request->get('owner');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$pagingModel->set('orderby', $orderBy);
		$pagingModel->set('sortorder', $sortOrder);

		$params = [];
		$params['status'] = Calendar_Module_Model::getComponentActivityStateLabel('overdue');
		$params['user'] = $currentUser->getId();
		$conditions = ['condition' => ['vtiger_activity.status' => $params['status'], 'vtiger_crmentity.smcreatorid' => $params['user']]];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$calendarActivities = ($owner === false) ? [] : $moduleModel->getCalendarActivities('assigned_over', $pagingModel, $owner, false, $params);

		$colorList = [];
		foreach ($calendarActivities as $activityModel) {
			$colorList[$activityModel->getId()] = Settings_DataAccess_Module_Model::executeColorListHandlers('Calendar', $activityModel->getId(), $activityModel);
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $calendarActivities);
		$viewer->assign('COLOR_LIST', $colorList);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', AppConfig::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', AppConfig::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_OVERDUE_ACTIVITIES');
		$viewer->assign('OWNER', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_CONDITIONS', $conditions);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}
