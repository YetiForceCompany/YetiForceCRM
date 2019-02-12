<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_CalendarActivities_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel();

		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$sortOrder = $request->getForSql('sortorder');
		$orderBy = $request->getForSql('orderby');

		$params = ['status' => [
			$stateActivityLabels['not_started'],
			$stateActivityLabels['in_realization'],
		],
		];
		if (!$request->isEmpty('activitytype') && $request->getByType('activitytype', 'Text') !== 'all') {
			$params['activitytype'] = $request->getByType('activitytype', 'Text');
		}
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Calendar', $request->getByType('owner', 2));

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$pagingModel->set('orderby', $orderBy);
		$pagingModel->set('sortorder', $sortOrder);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$calendarActivities = ($owner === false) ? [] : $moduleModel->getCalendarActivities('upcoming', $pagingModel, $owner, false, $params);
		$msgLabel = 'LBL_NO_SCHEDULED_ACTIVITIES';
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $calendarActivities);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('HREFNAMELENGTH', \AppConfig::main('href_max_length'));
		$viewer->assign('NAMELENGTH', \AppConfig::main('title_max_length'));
		$viewer->assign('OWNER', $owner);
		$viewer->assign('ACTIVITYTYPE', $params['activitytype'] ?? '');
		$viewer->assign('NODATAMSGLABLE', $msgLabel);
		$viewer->assign('LISTVIEWLINKS', true);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_CONDITIONS', ['condition' => ['vtiger_activity.status' => $params['status']]]);
		if ($request->has('content')) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}
