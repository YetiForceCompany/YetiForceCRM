<?php

/**
 * Notifications Dashboard Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_NotificationsBySender_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in building address URL to listview).
	 *
	 * @param string $owner Name of user
	 * @param array  $time
	 *
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions[] = ['createdtime', 'bw', $time['start'] . ',' . $time['end']];
		}
		if (!empty($owner)) {
			$conditions[] = ['smcreatorid', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data for chart Return number notification by sender.
	 *
	 * @param array $time Contains start and end created time of natification
	 *
	 * @return array
	 */
	private function getNotificationBySender($time)
	{
		$accessibleUsers = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$moduleName = 'Notification';
		$listView = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$query = new \App\Db\Query();
		$query->select(['count' => new \yii\db\Expression('COUNT(*)'), 'smcreatorid'])
			->from('vtiger_crmentity')
			->where([
				'and',
				['setype' => $moduleName],
				['deleted' => 0],
				['smcreatorid' => array_keys($accessibleUsers)],
				['>=', 'createdtime', $time['start'] . ' 00:00:00'],
				['<=', 'createdtime', $time['end'] . ' 23:59:59'],
		]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['smcreatorid']);
		$dataReader = $query->createCommand()->query();
		$data = [];
		$time = \App\Fields\Date::formatToDisplay($time);
		while ($row = $dataReader->read()) {
			$data[] = [
				$row['count'],
				$accessibleUsers[$row['smcreatorid']],
				$listView . $this->getSearchParams($accessibleUsers[$row['smcreatorid']], $time),
			];
		}
		$dataReader->close();
		return $data;
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), Users_Record_Model::getCurrentUserModel()->getId());
		$time = $request->getDateRange('time');
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time === false) {
				$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
				$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			}
		}
		$viewer->assign('DATA', $this->getNotificationBySender($time));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DTIME', \App\Fields\Date::formatToDisplay($time));
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NotificationsBySenderRecipient.tpl', $moduleName);
		}
	}
}
