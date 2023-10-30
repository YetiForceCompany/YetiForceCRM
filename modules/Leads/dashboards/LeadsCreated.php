<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_LeadsCreated_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$owner = $request->getByType('owner', 2);
		$data = Vtiger_Module_Model::getInstance($moduleName)->getLeadsCreated($owner, $request->getDateRange('createdtime'));
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		//Include special script and css needed for this widget
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSERID', $currentUserId);
		$viewer->assign('DATA', $data);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance('Leads', $currentUserId)->getAccessibleUsersForModule());
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsCreated.tpl', $moduleName);
		}
	}
}
