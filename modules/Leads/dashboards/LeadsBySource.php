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

class Leads_LeadsBySource_Dashboard extends Vtiger_IndexAjax_View
{
	public function getSearchParams($value, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = [['leadsource', 'e', $value]];
		if ($assignedto != '') {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($dates)) {
			array_push($conditions, ['createdtime', 'bw', $dates['start'] . ' 00:00:00,' . $dates['end'] . ' 23:59:59']);
		}
		$listSearchParams[] = $conditions;

		return '&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Leads grouped by Source.
	 *
	 * @param type $data
	 *
	 * @return array
	 */
	public function getLeadsBySource($owner, $dateFilter)
	{
		$query = new \App\Db\Query();
		$query->select([
				'leadsourceid' => 'vtiger_leadsource.leadsourceid',
				'count' => new \yii\db\Expression('COUNT(*)'),
				'leadsourcevalue' => new \yii\db\Expression("CASE WHEN vtiger_leaddetails.leadsource IS NULL OR vtiger_leaddetails.leadsource = '' THEN '' ELSE vtiger_leaddetails.leadsource END"), ])
				->from('vtiger_leaddetails')
				->innerJoin('vtiger_crmentity', 'vtiger_leaddetails.leadid = vtiger_crmentity.crmid')
				->innerJoin('vtiger_leadsource', 'vtiger_leaddetails.leadsource = vtiger_leadsource.leadsource')
				->where(['deleted' => 0, 'converted' => 0]);
		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($dateFilter)) {
			$query->andWhere(['between', 'createdtime', $dateFilter['start'] . ' 00:00:00', $dateFilter['end'] . ' 23:59:59']);
		}
		\App\PrivilegeQuery::getConditions($query, 'Leads');
		$query->groupBy(['vtiger_leaddetails.leadsource']);
		$dataReader = $query->createCommand()->query();
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'links' => [], // links generated in proccess method
				],
			],
			'show_chart' => false,
			'names' => [] // names for link generation
		];
		$colors = \App\Fields\Picklist::getColors('leadsource');
		while ($row = $dataReader->read()) {
			$chartData['labels'][] = \App\Language::translate($row['leadsourcevalue'], 'Leads');
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['backgroundColor'][] = $colors[$row['leadsourceid']];
			$chartData['show_chart'] = true;
			$chartData['names'][] = $row['leadsourcevalue'];
		}
		$dataReader->close();
		return $chartData;
	}

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$createdTime = $request->getDateRange('createdtime');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUser->getId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Leads');
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$ownerForwarded = $owner;
		if ($owner == 'all') {
			$owner = '';
		}
		$dates = [];
		//Date conversion from user to database format
		if (!empty($createdTime)) {
			$dates['start'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Vtiger_Date_UIType::getDBInsertedValue($createdTime['end']);
		} else {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time !== false) {
				$dates = $time;
			}
		}
		$data = ($owner === false) ? [] : $this->getLeadsBySource($owner, $dates);
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$leadSourceAmount = count($data['names']);
		for ($i = 0; $i < $leadSourceAmount; ++$i) {
			$data['datasets'][0]['links'][$i] = $listViewUrl . $this->getSearchParams($data['names'][$i], $owner, $dates);
		}
		//Include special script and css needed for this widget
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('DTIME', $dates);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance('Leads', $currentUser)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance('Leads', $currentUser)->getAccessibleGroupForModule());
		$viewer->assign('OWNER', $ownerForwarded);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsBySource.tpl', $moduleName);
		}
	}
}
