<?php

/**
 * Widget show estimated value sale.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$listSearchParams = [[['estimated_date', 'bw', $time]]];
		if (isset($owner)) {
			$listSearchParams[0][] = ['assigned_user_id', 'e', $owner];
		}
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
		if (!empty($previousData['show_chart'])) {
			foreach ($previousData['datasets'] as $key => $values) {
				if (isset($data['datasets'][$key]) && is_array($values)) {
					$data['datasets'][] = $values;
				}
			}
		}
		return $data;
	}

	/**
	 * Function to get data to chart.
	 *
	 * @param string      $time
	 * @param string|bool $compare
	 *
	 * @return array
	 */
	public function getEstimatedValue($time, $owner)
	{
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->setGroup('assigned_user_id');
		$queryGenerator->addCondition('estimated_date', $time, 'bw', true, true);
		if ('all' === $owner) {
			$queryGenerator->setStateCondition('All');
		}	else {
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
				$chartData['datasets'][0]['links'][] = $listView . $this->getSearchParams($row['assigned_user_id'], $time);
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
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, 'Accounts');
		} else {
			$owner = $request->getByType('owner', 2);
		}
		if (empty($time)) {
			$time = [0 => ''];
			$date = new \DateTime();
			$time[1] = $date->format('Y-m-d');
			$date->modify('-30 days');
			$time[0] = $date->format('Y-m-d');
			$time[0] = \App\Fields\Date::formatToDisplay($time[0]);
			$time[1] = \App\Fields\Date::formatToDisplay($time[1]);
		}
		$timeSting = implode(',', $time);
		$data = $this->getEstimatedValue($timeSting, $owner);
		if ($compare) {
			$start = new \DateTime(\DateTimeField::convertToDBFormat($time[0]));
			$endPeriod = clone $start;
			$end = new \DateTime(\DateTimeField::convertToDBFormat($time[1]));
			$interval = (int) $start->diff($end)->format('%r%a');
			if ($time[0] !== $time[1]) {
				++$interval;
			}
			$endPeriod->modify('-1 days');
			$start->modify("-{$interval} days");
			$previousTime = \App\Fields\Date::formatToDisplay($start->format('Y-m-d')) . ',' . \App\Fields\Date::formatToDisplay($endPeriod->format('Y-m-d'));
			$previousData = $this->getEstimatedValue($previousTime, $owner);
			if (!empty($data) || !empty($previousData)) {
				$data = $this->parseData($data, $previousData);
			}
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $timeSting);
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
