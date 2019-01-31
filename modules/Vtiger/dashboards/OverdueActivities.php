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

class Vtiger_OverdueActivities_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$sortOrder = $request->getForSql('sortorder');
		$orderBy = $request->getForSql('orderby');
		$data = $request->getAll();

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Calendar', $request->getByType('owner', 2));

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$pagingModel->set('orderby', $orderBy);
		$pagingModel->set('sortorder', $sortOrder);

		$params = ['status' => Calendar_Module_Model::getComponentActivityStateLabel('overdue')];
		if (!$request->isEmpty('activitytype') && $request->getByType('activitytype', 'Text') !== 'all') {
			$params['activitytype'] = $request->getByType('activitytype', 'Text');
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$overDueActivities = ($owner === false) ? [] : $moduleModel->getCalendarActivities('overdue', $pagingModel, $owner, false, $params);
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $overDueActivities);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', AppConfig::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', AppConfig::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_OVERDUE_ACTIVITIES');
		$viewer->assign('OWNER', $owner);
		$viewer->assign('ACTIVITYTYPE', $params['activitytype'] ?? '');
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
