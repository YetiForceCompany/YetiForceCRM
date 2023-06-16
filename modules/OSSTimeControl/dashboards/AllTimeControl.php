<?php

/**
 * Wdiget to show work time.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSTimeControl_AllTimeControl_Dashboard extends Vtiger_IndexAjax_View
{
	public function getSearchParams($assignedto, $date)
	{
		$conditions = [];
		$listSearchParams = [];
		if ('' != $assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($date)) {
			array_push($conditions, ['due_date', 'bw', implode(',', $date)]);
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
		$queryGenerator->setFields(['timecontrol_type', 'assigned_user_id'])
			->setCustomColumn(['sum_time' => new \yii\db\Expression('SUM(sum_time)')])
			->addCondition('due_date', implode(',', $date), 'bw')
			->setGroup('assigned_user_id')->setGroup('timecontrol_type')->setOrder('assigned_user_id');
		if ('all' !== $user) {
			$queryGenerator->addCondition('assigned_user_id', $user, 'e');
		} else {
			$queryGenerator->addNativeCondition([$queryGenerator->getColumnName('assigned_user_id') => \App\Fields\Owner::getInstance()->getQueryInitUsers(false, 'Active')->select(['id'])]);
		}

		$data = $queryGenerator->createQuery()->all();
		$colors = \App\Fields\Picklist::getColors('timecontrol_type', false);
		$chartData = [
			'show_chart' => false,
		];

		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$sumValuePerXAxisData = [];
		$xAxisData = array_values(array_unique(array_column($data, 'assigned_user_id')));
		$types = array_values(array_unique(array_column($data, 'timecontrol_type')));
		foreach ($data as $row) {
			$ownerId = $row['assigned_user_id'];
			$type = $row['timecontrol_type'];
			$sumTime = $row['sum_time'];
			$sumValuePerXAxisData[$type] = ($sumValuePerXAxisData[$type] ?? 0) + (float) $sumTime;
			$seriesIndex = array_search($type, $types);

			if (empty($chartData['series'][$seriesIndex])) {
				foreach (array_keys($xAxisData) as $statusIndex) {
					$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => null];
				}
			}
			$statusIndex = array_search($ownerId, $xAxisData);

			$color = $colors[$type] ?? \App\Colors::getRandomColor($type);
			$link = $listViewUrl . '&viewname=All&entityState=Active' . $this->getSearchParams($ownerId, $date);
			$label = $type ? \App\Language::translate($type, $moduleName, null, false) : '(' . \App\Language::translate('LBL_EMPTY', 'Home', null, false) . ')';

			$chartData['series'][$seriesIndex]['name'] = $label;
			$chartData['series'][$seriesIndex]['type'] = 'bar';
			$chartData['series'][$seriesIndex]['stack'] = 'total';
			$chartData['series'][$seriesIndex]['color'] = $color;
			$chartData['series'][$seriesIndex]['label'] = ['show' => false];
			$chartData['tooltip'] = ['trigger' => 'axis', 'axisPointer' => ['type' => 'shadow']];
			$chartData['labelLayout'] = ['hideOverlap' => false];
			$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => round($sumTime / 60, 2), 'itemStyle' => ['color' => $color], 'link' => $link, 'fullLabel' => \App\Fields\Owner::getLabel($ownerId), 'fullValue' => \App\Fields\RangeTime::displayElapseTime($sumTime), 'seriesLabel' => $label];

			$chartData['show_chart'] = true;
		}

		foreach ($xAxisData as $ownerId) {
			$chartData['xAxis']['data'][] = \App\Utils::getInitials(\App\Fields\Owner::getLabel($ownerId));
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
		$user = $request->getByType('owner', 2);
		$time = $request->getDateRange('time');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$viewer->assign('TCPMODULE_MODEL', Settings_TimeControlProcesses_Module_Model::getCleanInstance()->getConfigInstance());
		$viewer->assign('OWNER', $user);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		$viewer->assign('DATA', $this->getWidgetTimeControl($user, $time));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOGGEDUSERID', $currentUserId);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleGroupForModule());
		if ($request->has('content')) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AllTimeControl.tpl', $moduleName);
		}
	}
}
