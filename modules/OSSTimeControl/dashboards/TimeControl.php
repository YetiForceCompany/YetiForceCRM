<?php

/**
 * OSSTimeControl TimeControl dashboard class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_TimeControl_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in building address URL to listview).
	 *
	 * @param int|string $owner
	 * @param string     $date
	 * @param mixed      $assignedto
	 *
	 * @return string
	 */
	public function getSearchParams($assignedto, $date)
	{
		$conditions = [];
		$date = \App\Fields\Date::formatToDisplay($date);
		$listSearchParams = [];
		if ($assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($date)) {
			array_push($conditions, ['due_date', 'bw', $date . ',' . $date]);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . urlencode(json_encode($listSearchParams));
	}

	public function getWidgetTimeControl($user, $date)
	{
		if (!$date) {
			return ['show_chart' => false];
		}
		$moduleName = 'OSSTimeControl';
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['due_date', 'timecontrol_type'])
			->setCustomColumn(['sum_time' => new \yii\db\Expression('SUM(sum_time)')])
			->addCondition('assigned_user_id', $user, 'e')
			->addCondition('due_date', implode(',', $date), 'bw')
			->setGroup('due_date')->setGroup('timecontrol_type')->setOrder('due_date');
		$showMonth = \App\Fields\DateTime::getDiff($date[0], $date[1], 'days') > 31;
		$data = $queryGenerator->createQuery()->all();
		$colors = \App\Fields\Picklist::getColors('timecontrol_type', false);
		$chartData = [
			'show_chart' => false,
		];

		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$sumValuePerXAxisData = [];
		$chartData['xAxis']['data'] = array_map(fn ($dueDate) => $showMonth ? substr($dueDate, -5) : substr($dueDate, -2), array_unique(array_column($data, 'due_date')));
		$types = array_values(array_unique(array_column($data, 'timecontrol_type')));
		foreach ($data as $row) {
			$dueDate = $row['due_date'];
			$type = $row['timecontrol_type'];
			$sumTime = $row['sum_time'];
			$xAxisLabel = $showMonth ? substr($dueDate, -5) : substr($dueDate, -2);
			$sumValuePerXAxisData[$type] = ($sumValuePerXAxisData[$type] ?? 0) + (float) $sumTime;
			$seriesIndex = array_search($type, $types);

			if (empty($chartData['series'][$seriesIndex])) {
				foreach (array_keys($chartData['xAxis']['data']) as $statusIndex) {
					$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => null];
				}
			}
			$statusIndex = array_search($xAxisLabel, $chartData['xAxis']['data']);

			$color = $colors[$type] ?? \App\Colors::getRandomColor($type);
			$link = $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($user, $dueDate);
			$label = $type ? \App\Language::translate($type, $moduleName, null, false) : '(' . \App\Language::translate('LBL_EMPTY', 'Home', null, false) . ')';

			$chartData['series'][$seriesIndex]['name'] = $label;
			$chartData['series'][$seriesIndex]['type'] = 'bar';
			$chartData['series'][$seriesIndex]['stack'] = 'total';
			$chartData['series'][$seriesIndex]['color'] = $color;
			$chartData['series'][$seriesIndex]['label'] = ['show' => false];
			$chartData['tooltip'] = ['trigger' => 'axis', 'axisPointer' => ['type' => 'shadow']];
			$chartData['labelLayout'] = ['hideOverlap' => false];
			$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => round($sumTime / 60, 2), 'itemStyle' => ['color' => $color], 'link' => $link, 'fullLabel' => \App\Fields\DateTime::formatToDay($dueDate, true), 'fullValue' => \App\Fields\RangeTime::displayElapseTime($sumTime), 'seriesLabel' => $label];

			$chartData['show_chart'] = true;
		}

		foreach ($sumValuePerXAxisData as $type => $value) {
			if (!$value) {
				continue;
			}
			$seriesIndex = array_search($type, $types);
			$chartData['series'][$seriesIndex]['name'] .= ': ' . \App\Fields\RangeTime::displayElapseTime($value);
		}

		return $chartData;
	}

	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$user = $request->getByType('user', 2);
		$time = $request->getDateRange('time');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		if (empty($user)) {
			$user = $currentUserId;
		}
		$data = $this->getWidgetTimeControl($user, $time);
		$viewer->assign('USER_CONDITIONS', null);
		$viewer->assign('TCPMODULE_MODEL', Settings_TimeControlProcesses_Module_Model::getCleanInstance()->getConfigInstance());
		$viewer->assign('USERID', $user);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOGGEDUSERID', $currentUserId);
		$viewer->assign('SOURCE_MODULE', 'OSSTimeControl');
		if ($request->has('content')) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeControl.tpl', $moduleName);
		}
	}
}
