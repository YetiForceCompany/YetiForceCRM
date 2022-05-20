<?php

/**
 * Notifications Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notification_NotificationsByRecipient_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in building address URL to listview).
	 *
	 * @param int|string $owner
	 * @param array      $time
	 *
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions[] = ['createdtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner)) {
			$conditions[] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&entityState=Active&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data for chart. Return number notification by recipient.
	 *
	 * @param array $time Contains start and end created time of natification
	 *
	 * @return array
	 */
	private function getNotificationByRecipient($time)
	{
		$accessibleUsers = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$moduleName = 'Notification';
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$query = new \App\Db\Query();
		$query->select(['count' => new \yii\db\Expression('COUNT(*)'), 'smownerid'])
			->from('vtiger_crmentity')
			->where([
				'and',
				['setype' => $moduleName],
				['deleted' => 0],
				['smcreatorid' => array_keys($accessibleUsers)],
				['>=', 'createdtime', $time[0] . ' 00:00:00'],
				['<=', 'createdtime', $time[1] . ' 23:59:59'],
			]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['smownerid']);
		$dataReader = $query->createCommand()->query();
		$time = \App\Fields\Date::formatRangeToDisplay($time);
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
			$label = $accessibleUsers[$row['smownerid']];
			$chartData['labels'][] = \App\Utils::getInitials($label);
			$chartData['datasets'][0]['titlesFormatted'][] = $label;
			$chartData['datasets'][0]['data'][] = $row['count'];
			$chartData['datasets'][0]['links'][] = $listViewUrl . $this->getSearchParams($row['smownerid'], $time);
			$chartData['datasets'][0]['backgroundColor'][] = App\Fields\Owner::getColor($row['smownerid']);
		}
		$chartData['show_chart'] = (bool) $dataReader->count();
		$dataReader->close();
		return $chartData;
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), Users_Record_Model::getCurrentUserModel()->getId());
		$time = $request->getDateRange('time');
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		$viewer->assign('DATA', $this->getNotificationByRecipient($time));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NotificationsBySenderRecipient.tpl', $moduleName);
		}
	}
}
