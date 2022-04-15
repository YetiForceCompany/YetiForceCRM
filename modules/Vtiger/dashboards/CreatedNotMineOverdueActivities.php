<?php

/**
 * Vtiger CreatedNotMineOverdueActivities dashboard class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_CreatedNotMineOverdueActivities_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = 'Home';
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$sortOrder = $request->getForSql('sortorder');
		if (empty($sortOrder) || !\in_array($sortOrder, ['asc', 'desc'])) {
			$sortOrder = 'asc';
		}
		$sortOrder = ($sortOrder === 'asc') ? SORT_ASC : SORT_DESC;
		$orderBy = $request->getForSql('orderby') ?: ['due_date' => $sortOrder, 'time_end' => $sortOrder];
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
		$params = ['status' => Calendar_Module_Model::getComponentActivityStateLabel('overdue')];
		$params['user'] = $currentUser->getId();
		$conditions = [
			'condition' => [
				'and',
				['vtiger_activity.status' => $params['status']],
				['vtiger_crmentity.smcreatorid' => $params['user']],
				['not in', 'vtiger_crmentity.smownerid', $params['user']],
			],
		];
		if (!$request->isEmpty('activitytype') && 'all' !== $request->getByType('activitytype')) {
			$params['activitytype'] = $request->getByType('activitytype');
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$overDueActivities = (false === $owner) ? [] : $moduleModel->getCalendarActivities('createdByMeButNotMineOverdue', $pagingModel, $owner, false, $params);
		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $overDueActivities);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', \App\Config::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', \App\Config::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_RECORDS_MATCHED_THIS_CRITERIA');
		$viewer->assign('OWNER', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('DATE_TYPE', 'DUE');
		$viewer->assign('USER_CONDITIONS', $conditions);
		if ($request->has('content')) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CreatedNotMineActivities.tpl', $moduleName);
		}
	}
}
