<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_ProjectWidget_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Retrieves css styles that need to loaded in the page.
	 *
	 * @param \App\Request $request - request model
	 *
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = [
			//Place your widget specific css files here
		];
		$headerCssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);

		return $headerCssScriptInstances;
	}

	public function getSearchParams($stage, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = [];
		array_push($conditions, ['sales_stage', 'e', $stage]);
		if ($assignedto == '') {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$assignedto = $currenUserModel->getId();
		}
		if ($assignedto != 'all') {
			$ownerType = \App\Fields\Owner::getType($assignedto);
			if ($ownerType == 'Users') {
				array_push($conditions, ['assigned_user_id', 'e', \App\Fields\Owner::getUserLabel($assignedto)]);
			} else {
				$groupName = \App\Fields\Owner::getGroupName($assignedto);
				array_push($conditions, ['assigned_user_id', 'e', $groupName]);
			}
		}
		if (!empty($dates)) {
			array_push($conditions, ['closingdate', 'bw', $dates['start'] . ',' . $dates['end']]);
		}
		$listSearchParams[] = $conditions;

		return '&search_params=' . json_encode($listSearchParams);
	}

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$owner = $request->getByType('owner', 2);
		$dates = $request->getDateRange('expectedclosedate');

		if (!empty($dates)) {
			$dates['start'] = \App\Fields\Date::formatToDisplay($dates['start']);
			$dates['end'] = \App\Fields\Date::formatToDisplay($dates['end']);
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getProjectWidget($owner, $dates);
		$listViewUrl = $moduleModel->getListViewUrl();
		$countData = count($data);
		for ($i = 0; $i < $countData; ++$i) {
			$data[$i][] = $listViewUrl . $this->getSearchParams($data[$i][0], $owner, $dates);
		}

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);

		//Include special script and css needed for this widget
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('CURRENTUSER', $currentUser);
		if ($request->has('content')) {
			$viewer->view('dashboards/CampaignsWidget.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CampaignsWidget.tpl', $moduleName);
		}
	}
}
