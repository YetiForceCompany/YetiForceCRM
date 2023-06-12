<?php

/**
 * Widget showing ticket which have closed. We can filter by users or date.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		if ($ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify()) {
			$conditions[] = ['ticketstatus', 'e', implode('##', $ticketStatus)];
		}
		if (!empty($time)) {
			$conditions[] = ['closing_datatime', 'bw', implode(',', $time)];
		}
		if (!empty($owner) && 'all' != $owner) {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&entityState=Active&viewname=All&search_params=' . urlencode(\App\Json::encode($listSearchParams));
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
		$query = (new App\Db\Query())->select([
			'count' => new \yii\db\Expression('COUNT(*)'),
			'vtiger_troubletickets.priority',
			'vtiger_ticketpriorities.ticketpriorities_id',
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.ticketid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_ticketstatus', 'vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus')
			->leftJoin('vtiger_ticketpriorities', 'vtiger_ticketpriorities.ticketpriorities = vtiger_troubletickets.priority')
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

		$time = \App\Fields\Date::formatRangeToDisplay($time);
		$chartData = [
			'show_chart' => false,
		];
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		while ($row = $dataReader->read()) {
			$priotity = $row['priority'];
			$label = $priotity ? \App\Language::translate($priotity, $moduleName) : ('(' . \App\Language::translate('LBL_EMPTY', 'Home') . ')');
			$link = $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($priotity, $time, $owner);
			$chartData['series'][0]['data'][] = ['value' => (int) $row['count'], 'name' => $label, 'itemStyle' => ['color' => $colors[$row['ticketpriorities_id']] ?? \App\Colors::getRandomColor($priotity)], 'link' => $link];
			$chartData['show_chart'] = true;
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
