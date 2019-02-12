<?php

/**
 * Vtiger AssignedUpcomingCalendarTasks dashboard class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_AssignedUpcomingCalendarTasks_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$sortOrder = $request->getForSql('sortorder');
		$orderBy = $request->getForSql('orderby');
		$data = $request->getAll();

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$pagingModel->set('orderby', $orderBy);
		$pagingModel->set('sortorder', $sortOrder);

		$params = [];
		$params['status'] = Calendar_Module_Model::getComponentActivityStateLabel('current');
		$params['user'] = $currentUser->getId();
		if (!$request->isEmpty('activitytype') && $request->getByType('activitytype', 'Text') !== 'all') {
			$params['activitytype'] = $request->getByType('activitytype', 'Text');
		}
		$conditions = [
			'condition' => [
				'vtiger_activity.status' => $params['status'],
				'vtiger_crmentity.smcreatorid' => $params['user'],
			],
		];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$calendarActivities = ($owner === false) ? [] : $moduleModel->getCalendarActivities('assigned_upcoming', $pagingModel, $owner, false, $params);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $calendarActivities);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', AppConfig::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', AppConfig::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_SCHEDULED_ACTIVITIES');
		$viewer->assign('OWNER', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_CONDITIONS', $conditions);
		if ($request->has('content')) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}
