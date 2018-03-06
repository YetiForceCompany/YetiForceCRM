<?php

/**
 * Widget showing ticket which have closed. We can filter by date.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class HelpDesk_ClosedTicketsByUser_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in building address URL to listview).
	 *
	 * @param int     $owner numer id of user
	 * @param <Array> $time  contain start date and end time
	 *
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions[] = ['closedtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner)) {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Tickets grouped by users.
	 *
	 * @param <Array> $time contain start date and end time
	 *
	 * @return <Array> data to display chart
	 */
	public function getTicketsByUser($time)
	{
		$moduleName = 'HelpDesk';
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$query = (new App\Db\Query())->select([
				'count' => new \yii\db\Expression('COUNT(*)'),
				'vtiger_crmentity.smownerid',
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
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy('vtiger_crmentity.smownerid');
		$dataReader = $query->createCommand()->query();
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
			$label = \App\Fields\Owner::getLabel($row['smownerid']);
			$chartData['labels'][] = vtlib\Functions::getInitials($label);
			$chartData['datasets'][0]['titlesFormatted'][] = $label;
			$chartData['datasets'][0]['data'][] = (int) $row['count'];
			$chartData['datasets'][0]['backgroundColor'][] = \App\Fields\Owner::getColor((int) $row['smownerid']);
			$chartData['datasets'][0]['links'][] = $listViewUrl . $this->getSearchParams($row['smownerid'], $time);
		}
		$dataReader->close();
		return $chartData;
	}

	/**
	 * Main function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$time = $request->getDateRange('time');
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time === false) {
				$time['start'] = \App\Fields\Date::formatToDisplay(date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y'))));
				$time['end'] = \App\Fields\Date::formatToDisplay(date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y'))));
			}
		}
		$data = $this->getTicketsByUser($time);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $time);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ClosedTicketsByUser.tpl', $moduleName);
		}
	}
}
