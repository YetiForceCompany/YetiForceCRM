<?php

/**
 * Widget show estimated value sale.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_TeamsEstimatedSales_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Function to get search params in address listview.
	 *
	 * @param int   $owner number id of user
	 * @param array $time
	 *
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$conditions = [];
		$listSearchParams = [];
		if (!empty($owner)) {
			array_push($conditions, ['assigned_user_id', 'e', $owner]);
		}
		if (!empty($time)) {
			array_push($conditions, ['estimated_date', 'bw', implode(',', \App\Fields\Date::formatRangeToDisplay(explode(',', $time)))]);
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Parse data.
	 *
	 * @param array $data
	 * @param array $previousData
	 *
	 * @return array
	 */
	public function parseData($data, $previousData)
	{
		foreach ($data['datasets'] ?? [] as $key => $values) {
			unset($values);
			if (!isset($previousData['datasets'][$key])) {
				$previousData['datasets'][$key]['data'] = [0];
				$previousData['datasets'][$key]['backgroundColor'] = '#EDC240';
			}
		}
		foreach ($previousData['datasets'] ?? [] as $key => $values) {
			$values['backgroundColor'] = '#EDC240';
			if (isset($data['datasets'][$key]) && is_array($values)) {
				$data['datasets'][] = $values;
			}
		}
		if (isset($data['datasets'])) {
			$data['datasets'] = array_reverse($data['datasets']);
		}
		return $data;
	}

	/**
	 * Function to get data to chart.
	 *
	 * @param string      $time
	 * @param bool $compare
	 * @param int|string $owner
	 *
	 * @return array
	 */
	public function getEstimatedValue(string $timeString, bool $compare = false, $owner = false): array
	{
		unset($compare);
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->setGroup('assigned_user_id');
		$queryGenerator->addCondition('estimated_date', $timeString, 'bw', false, false);
		if ('all' !== $owner) {
		 $queryGenerator->addNativeCondition(['smownerid' => $owner]);
		}
		$sum = new \yii\db\Expression('SUM(estimated)');
		$queryGenerator->setCustomColumn(['estimated' => $sum]);
		$query = $queryGenerator->createQuery();
		$listView = $queryGenerator->getModuleModel()->getListViewUrl();
		$dataReader = $query->createCommand()->query();
		$chartData = [];
		while ($row = $dataReader->read()) {
			$chartData['datasets'][0]['data'][] = round($row['estimated'], 2);
			$chartData['datasets'][0]['backgroundColor'][] = '#95a458';
			$chartData['datasets'][0]['links'][] = $listView . $this->getSearchParams($row['assigned_user_id'], $timeString);
			$ownerName = \App\Fields\Owner::getUserLabel($row['assigned_user_id']);
			$chartData['labels'][] = \App\Utils::getInitials($ownerName);
			$chartData['fullLabels'][] = $ownerName;
		}
		$chartData['show_chart'] = (bool) isset($chartData['datasets']);
		$dataReader->close();
		return $chartData;
	}

	/**
	 * Main function.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$time = $request->getDateRange('time');
		$compare = $request->getBoolean('compare');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Accounts');
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$timeString = implode(',', $time);
		$data = $this->getEstimatedValue($timeString, $compare, $owner);
		if ($compare) {
			$start = new \DateTime($time[0]);
			$endPeriod = clone $start;
			$end = new \DateTime($time[1]);
			$interval = (int) $start->diff($end)->format('%r%a');
			if ($time[0] !== $time[1]) {
				++$interval;
			}
			$endPeriod->modify('-1 days');
			$start->modify("-{$interval} days");
			$previousTime = $start->format('Y-m-d') . ',' . $endPeriod->format('Y-m-d');
			$previousData = $this->getEstimatedValue($previousTime, $compare, $owner);
			if (!empty($data) || !empty($previousData)) {
				$data = $this->parseData($data, $previousData);
			}
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME',  implode(',', \App\Fields\Date::formatRangeToDisplay($time)));
		$viewer->assign('COMPARE', $compare);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance('Accounts', $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance('Accounts', $currentUserId)->getAccessibleGroupForModule());
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TeamsEstimatedSales.tpl', $moduleName);
		}
	}
}
