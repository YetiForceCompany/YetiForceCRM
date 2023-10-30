<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Leads_LeadsByStatusConverted_Dashboard extends Vtiger_IndexAjax_View
{
	public function getSearchParams($value, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = [['leadstatus', 'e', $value]];
		if ('' != $assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($dates)) {
			array_push($conditions, ['createdtime', 'bw', $dates[0] . ' 00:00:00,' . $dates[1] . ' 23:59:59']);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Leads grouped by Status (converted).
	 *
	 * @param int   $owner
	 * @param array $dateFilter
	 *
	 * @return array
	 */
	public function getLeadsByStatusConverted($owner, $dateFilter)
	{
		$query = new \App\Db\Query();
		$query->select([
			'leadstatusid' => 'vtiger_leadstatus.leadstatusid',
			'count' => new \yii\db\Expression('COUNT(*)'),
			'leadstatusvalue' => 'vtiger_leadstatus.leadstatus', ])
			->from('vtiger_leaddetails')
			->innerJoin('vtiger_crmentity', 'vtiger_leaddetails.leadid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_leadstatus', 'vtiger_leaddetails.leadstatus = vtiger_leadstatus.leadstatus')
			->where(['deleted' => 0]);
		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($dateFilter)) {
			$query->andWhere(['between', 'createdtime', $dateFilter[0] . ' 00:00:00', $dateFilter[1] . ' 23:59:59']);
		}
		\App\PrivilegeQuery::getConditions($query, 'Leads');
		$query->groupBy(['leadstatusvalue', 'vtiger_leadstatus.leadstatusid', 'vtiger_leadstatus.sortorderid'])->orderBy('vtiger_leadstatus.sortorderid');
		$dataReader = $query->createCommand()->query();
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'links' => [], // links generated in proccess method
					'names' => [] // names for link generation
				],
			],
			'show_chart' => false,
		];
		$colors = \App\Fields\Picklist::getColors('leadstatus');
		while ($row = $dataReader->read()) {
			$value = $row['leadstatusvalue'];
			if (empty($value)) {
				$value = 'LBL_BLANK';
			}
			$chartData['labels'][] = \App\Language::translate($value, 'Leads');
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['names'][] = $value;
			$chartData['datasets'][0]['backgroundColor'][] = $colors[$row['leadstatusid']];
		}
		$chartData['show_chart'] = (bool) \count($chartData['datasets'][0]['data']);
		$dataReader->close();
		return $chartData;
	}

	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$createdTime = $request->getDateRange('createdtime');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Leads');
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$ownerForwarded = $owner;
		if ('all' == $owner) {
			$owner = '';
		}
		if (empty($createdTime)) {
			$createdTime = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		$data = (false === $owner) ? [] : $this->getLeadsByStatusConverted($owner, $createdTime);
		$createdTime = \App\Fields\Date::formatRangeToDisplay($createdTime);
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$leadStatusAmount = \count($data['datasets'][0]['names']);
		for ($i = 0; $i < $leadStatusAmount; ++$i) {
			$data['datasets'][0]['links'][$i] = $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($data['datasets'][0]['names'][$i], $owner, $createdTime);
		}
		//Include special script and css needed for this widget
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $createdTime);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance('Leads', $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance('Leads', $currentUserId)->getAccessibleGroupForModule());
		$viewer->assign('OWNER', $ownerForwarded);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsByStatusConverted.tpl', $moduleName);
		}
	}
}
