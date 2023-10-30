<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************************************************************** */

class HelpDesk_OpenTickets_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Function returns Tickets grouped by Status.
	 *
	 * @return array
	 */
	public function getOpenTickets()
	{
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$moduleName = 'HelpDesk';
		$query = new \App\Db\Query();
		$userNameSql = App\Module::getSqlForNameInDisplayFormat('Users');
		$query->select(['count' => new \yii\db\Expression('COUNT(*)'),
			'name' => new \yii\db\Expression("CASE WHEN ($userNameSql NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END"),
			'color' => new \yii\db\Expression("CASE WHEN ($userNameSql NOT LIKE '') THEN
					vtiger_users.cal_color ELSE vtiger_groups.color END"),
			'id' => 'smownerid', ])
			->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.ticketid = vtiger_crmentity.crmid')
			->leftJoin('vtiger_users', 'vtiger_crmentity.smownerid = vtiger_users.id')
			->leftJoin('vtiger_groups', 'vtiger_crmentity.smownerid = vtiger_groups.groupid')
			->where(['vtiger_crmentity.deleted' => 0]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		if (!empty($ticketStatus)) {
			$query->andWhere(['not in', 'vtiger_troubletickets.status', $ticketStatus]);
		}
		$query->groupBy(['smownerid', 'vtiger_users.last_name', 'vtiger_users.first_name', 'vtiger_groups.groupname', 'vtiger_users.cal_color', 'vtiger_groups.color']);
		$dataReader = $query->createCommand()->query();
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'links' => [],
					'titlesFormatted' => [],
				],
			],
			'show_chart' => false,
		];
		$chartData['show_chart'] = (bool) $dataReader->count();
		while ($row = $dataReader->read()) {
			$label = trim($row['name']);
			$chartData['labels'][] = \App\Utils::getInitials($label);
			$chartData['datasets'][0]['titlesFormatted'][] = $label;
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['links'][] = $listViewUrl . $this->getSearchParams($row['id']);
			$chartData['datasets'][0]['backgroundColor'][] = \App\Fields\Owner::getColor($row['id']);
		}
		$dataReader->close();
		return $chartData;
	}

	/**
	 * Return params to search.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getSearchParams($value)
	{
		$openTicketsStatus = Settings_SupportProcesses_Module_Model::getOpenTicketStatus();
		if ($openTicketsStatus) {
			$openTicketsStatus = implode('##', $openTicketsStatus);
		} else {
			$allTicketStatus = Settings_SupportProcesses_Module_Model::getAllTicketStatus();
			$openTicketsStatus = implode('##', $allTicketStatus);
		}
		$listSearchParams = [];
		$conditions = [['assigned_user_id', 'e', $value]];
		array_push($conditions, ['ticketstatus', 'e', $openTicketsStatus]);
		$listSearchParams[] = $conditions;
		return '&entityState=Active&viewname=All&search_params=' . urlencode(App\Json::encode($listSearchParams));
	}

	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $this->getOpenTickets();
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId()));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		//Include special script and css needed for this widget
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/OpenTickets.tpl', $moduleName);
		}
	}
}
