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
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		$stateActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel();

		$page = $request->get('page');
		$linkId = $request->get('linkid');
		$sortOrder = $request->get('sortorder');
		$orderBy = $request->get('orderby');

		$params = ['status' => [
				$stateActivityLabels['not_started'],
				$stateActivityLabels['in_realization']
			]
		];
		$conditions = [
			'condition' => [
				'vtiger_activity.status' => $params['status']
			]
		];
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Calendar', $request->get('owner'));

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$pagingModel->set('orderby', $orderBy);
		$pagingModel->set('sortorder', $sortOrder);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$calendarActivities = ($owner === false) ? [] : $moduleModel->getCalendarActivities('upcoming', $pagingModel, $owner, false, $params);

		$colorList = [];
		foreach ($calendarActivities as $activityModel) {
			$colorList[$activityModel->getId()] = Settings_DataAccess_Module_Model::executeColorListHandlers('Calendar', $activityModel->getId(), $activityModel);
		}
		$msgLabel = 'LBL_NO_SCHEDULED_ACTIVITIES';
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('COLOR_LIST', $colorList);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $calendarActivities);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$title_max_length = vglobal('title_max_length');
		$href_max_length = vglobal('href_max_length');
		$viewer->assign('NAMELENGHT', $title_max_length);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('HREFNAMELENGHT', $href_max_length);
		$viewer->assign('NODATAMSGLABLE', $msgLabel);
		$viewer->assign('LISTVIEWLINKS', true);
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
