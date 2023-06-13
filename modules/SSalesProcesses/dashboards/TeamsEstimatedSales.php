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
			array_push($conditions, ['estimated_date', 'bw', implode(',', $time)]);
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . urlencode(json_encode($listSearchParams));
	}

	/**
	 * Gets query.
	 *
	 * @param array $time
	 * @param bool  $owner
	 *
	 * @return App\QueryGenerator
	 */
	public function getQuery(array $time, $owner = false): App\QueryGenerator
	{
		$sum = new \yii\db\Expression('SUM(estimated)');
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id'])
			->setCustomColumn(['estimated' => $sum])
			->setGroup('assigned_user_id')
			->addCondition('estimated_date', implode(',', $time), 'bw');
		if ('all' !== $owner) {
			$queryGenerator->addNativeCondition(['smownerid' => $owner]);
		}

		return $queryGenerator;
	}

	/**
	 * Get raw data.
	 *
	 * @param array $time
	 * @param int   $owner
	 * @param bool  $compare
	 *
	 * @return array
	 */
	public function getDataForWidget(array $time, $owner, $compare): array
	{
		$data = [];
		$dim = implode(',', $time);
		$currentData = $this->getQuery($time, $owner)->createQuery()->createCommand()->queryAllByGroup();
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
			$previousTime = [$start->format('Y-m-d'), $endPeriod->format('Y-m-d')];
			$previousData = $this->getQuery($previousTime, $owner)->createQuery()->createCommand()->queryAllByGroup();

			$dim2 = implode(',', $previousTime);

			foreach ($currentData as $ownerId => $value) {
				$data[$ownerId][$dim2] = $previousData[$ownerId] ?? 0;
				$data[$ownerId][$dim] = $value;
			}
			foreach ($previousData as $ownerId => $value) {
				if (isset($data[$ownerId])) {
					$data[$ownerId][$dim2] = $value;
					$data[$ownerId][$dim] = $currentData[$ownerId] ?? 0;
				}
			}
		} else {
			foreach ($currentData as $ownerId => $value) {
				$data[$ownerId][$dim] = $value;
			}
		}

		return $data;
	}

	/**
	 * Function to get data to chart.
	 *
	 * @param array $time
	 * @param int   $owner
	 * @param bool  $compare
	 *
	 * @return array
	 */
	public function getEstimatedValue(array $time, $owner, $compare): array
	{
		$listViewUrl = Vtiger_Module_Model::getInstance('SSalesProcesses')->getListViewUrl();
		$data = $this->getDataForWidget($time, $owner, $compare);
		$xAxisData = array_keys($data);
		foreach ($data as $ownerId => $row) {
			$label = \App\Fields\Owner::getLabel($ownerId);
			$color = \App\Fields\Owner::getColor($ownerId);
			$seriesIndex = 0;
			foreach ($row as $dim => $value) {
				$setColor = \count($row) > 1 && 0 === $seriesIndex;
				$timeFormat = \App\Fields\Date::formatRangeToDisplay(explode(',', $dim));
				$chartData['series'][$seriesIndex]['name'] = implode(',', $timeFormat);
				$chartData['series'][$seriesIndex]['type'] = 'bar';
				$chartData['series'][$seriesIndex]['color'] = $setColor ? '#EDC240' : $color;
				$chartData['series'][$seriesIndex]['label'] = ['show' => true];
				$statusIndex = array_search($ownerId, $xAxisData);
				$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => round($value, 2), 'itemStyle' => ['color' => $setColor ? '#EDC240' : $color],
					'link' => $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($ownerId, $timeFormat),
					'fullLabel' => $label];
				++$seriesIndex;
			}
		}

		foreach ($xAxisData as $ownerId) {
			$chartData['xAxis']['data'][] = \App\Utils::getInitials(\App\Fields\Owner::getLabel($ownerId));
		}
		$chartData['show_chart'] = !empty($xAxisData);

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

		$data = $this->getEstimatedValue($time, $owner, $compare);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', implode(',', \App\Fields\Date::formatRangeToDisplay($time)));
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
