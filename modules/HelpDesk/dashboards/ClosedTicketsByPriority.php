<?php

/**
 * Widget showing ticket which have closed. We can filter by users or date.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			$conditions[] = ['closedtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner) && $owner != 'all') {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . \App\Json::encode($listSearchParams);
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
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$query = (new App\Db\Query())->select([
				'count' => new \yii\db\Expression('COUNT(*)'),
				'priority',
				'vtiger_ticketpriorities.color',
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
				['>=', 'vtiger_crmentity.closedtime', $time['start']],
				['<=', 'vtiger_crmentity.closedtime', $time['end']],
			]);
		}
		if (!empty($owner) && $owner != 'all') {
			$query->andWhere(['vtiger_crmentity.smownerid' => $owner]);
		}
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['priority', 'vtiger_ticketpriorities.color']);
		$dataReader = $query->createCommand()->query();
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
		while ($row = $dataReader->read()) {
			$chartData['show_chart'] = true;
			$chartData['labels'][] = \App\Language::translate($row['priority'], $moduleName);
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['links'][] = $listViewUrl . $this->getSearchParams($row['priority'], $time, $owner);
			$chartData['datasets'][0]['backgroundColor'][] = !empty($row['color']) ? trim($row['color']) : \App\Colors::getRandomColor($row['priority']);
		}
		$dataReader->close();
		return $chartData;
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$time = $request->getDateRange('time');
		$owner = $request->getByType('owner', 2);
		if (empty($owner)) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time === false) {
				$time['start'] = \App\Fields\Date::formatToDisplay(date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y'))));
				$time['end'] = \App\Fields\Date::formatToDisplay(date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y'))));
			}
		}
		$data = $this->getTicketsByPriority($time, $owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $time);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ClosedTicketsByPriority.tpl', $moduleName);
		}
	}
}
