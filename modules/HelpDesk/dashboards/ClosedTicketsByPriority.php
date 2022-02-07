<?php

/**
 * Widget showing ticket which have closed. We can filter by users or date.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class HelpDesk_ClosedTicketsByPriority_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in bulding address URL to listview).
	 *
	 * @param string $priority
	 * @param array  $time
	 * @param int    $owner
	 *
	 * @return string
	 */
	public function getSearchParams($priority, $time, $owner)
	{
		$listSearchParams = [];
		$conditions = [['ticketpriorities', 'e', $priority]];
		if (!empty($time)) {
			$conditions[] = ['closing_datatime', 'bw', implode(',', $time)];
		}
		if (!empty($owner) && 'all' != $owner) {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&entityState=Active&viewname=All&search_params=' . \App\Json::encode($listSearchParams);
	}

	/**
	 * Function returns Tickets grouped by priority.
	 *
	 * @param array $time
	 * @param int   $owner
	 *
	 * @return array
	 */
	public function getTicketsByPriority($time, $owner)
	{
		$moduleName = 'HelpDesk';
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$query = (new App\Db\Query())->select([
			'count' => new \yii\db\Expression('COUNT(*)'),
			'vtiger_troubletickets.priority',
			'vtiger_ticketpriorities.ticketpriorities_id',
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.ticketid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_ticketstatus', 'vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus')
			->innerJoin('vtiger_ticketpriorities', 'vtiger_ticketpriorities.ticketpriorities = vtiger_troubletickets.priority')
			->where(['vtiger_crmentity.deleted' => 0]);
		if (!empty($ticketStatus)) {
			$query->andWhere(['vtiger_troubletickets.status' => $ticketStatus]);
		}
		if (!empty($time)) {
			$query->andWhere([
				'and',
				['>=', 'vtiger_troubletickets.closing_datatime', $time[0] . ' 00:00:00'],
				['<=', 'vtiger_troubletickets.closing_datatime', $time[1] . ' 23:59:59'],
			]);
		}
		if (!empty($owner) && 'all' != $owner) {
			$query->andWhere(['vtiger_crmentity.smownerid' => $owner]);
		}
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['vtiger_troubletickets.priority', 'vtiger_ticketpriorities.ticketpriorities_id']);
		$dataReader = $query->createCommand()->query();
		$colors = \App\Fields\Picklist::getColors('ticketpriorities');
		$chartData = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'links' => [],
				],
			],
			'show_chart' => false,
		];
		$chartData['show_chart'] = (bool) $dataReader->count();
		$time = \App\Fields\Date::formatRangeToDisplay($time);
		while ($row = $dataReader->read()) {
			$chartData['labels'][] = \App\Language::translate($row['priority'], $moduleName);
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['links'][] = $listViewUrl . $this->getSearchParams($row['priority'], $time, $owner);
			$chartData['datasets'][0]['backgroundColor'][] = $colors[$row['ticketpriorities_id']];
		}
		$dataReader->close();
		return $chartData;
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUserId = \App\User::getCurrentUserId();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$time = $request->getDateRange('time');
		$owner = $request->getByType('owner', 2);
		if (empty($owner)) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		$data = $this->getTicketsByPriority($time, $owner);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleGroupForModule());
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ClosedTicketsByPriority.tpl', $moduleName);
		}
	}
}
