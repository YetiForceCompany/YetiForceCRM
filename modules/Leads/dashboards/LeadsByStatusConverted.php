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

class Leads_LeadsByStatusConverted_Dashboard extends Vtiger_IndexAjax_View
{

	public function getSearchParams($value, $assignedto, $dates)
	{
		$listSearchParams = array();
		$conditions = array(array('leadstatus', 'e', $value));
		if ($assignedto != '')
			array_push($conditions, array('assigned_user_id', 'e', $assignedto));
		if (!empty($dates)) {
			array_push($conditions, array('createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59'));
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$data = $request->get('data');
		$createdTime = $request->get('createdtime');

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Leads');
		else
			$owner = $request->get('owner');
		$ownerForwarded = $owner;
		if ($owner == 'all')
			$owner = '';

		$dates = [];
		//Date conversion from user to database format
		if (!empty($createdTime)) {
			$dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
		} else {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if($time !== false){
				$dates = $time;
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = ($owner === false) ? array() : $moduleModel->getLeadsByStatusConverted($owner, $dates);
		$listViewUrl = $moduleModel->getListViewUrl();
		foreach ($data as &$value) {
			$value['links'] = $listViewUrl . $this->getSearchParams($value[2], $owner, $dates);
		}
		//Include special script and css needed for this widget

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('DTIME', $dates);

		$accessibleUsers = \App\Fields\Owner::getInstance('Leads', $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance('Leads', $currentUser)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('OWNER', $ownerForwarded);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsByStatusConverted.tpl', $moduleName);
		}
	}
}
