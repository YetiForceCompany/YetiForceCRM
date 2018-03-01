<?php

/**
 * FInvoice Summation By Months Dashboard Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByMonths_Dashboard extends Vtiger_IndexAjax_View
{
	private $conditions = false;

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$data = $this->getWidgetData($moduleName, $owner);
		$viewer->assign('USERID', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		if ($request->has('content')) {
			$viewer->view('dashboards/SummationByMonthsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByMonths.tpl', $moduleName);
		}
	}

	/**
	 * Get widget data.
	 *
	 * @param string     $moduleName
	 * @param int|string $owner
	 *
	 * @return array
	 */
	public function getWidgetData($moduleName, $owner)
	{
		$rawData = $data = $years = [];
		$date = date('Y-m-01', strtotime('-23 month', strtotime(date('Y-m-d'))));
		$displayDate = \App\Fields\Date::formatToDisplay($date);
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$y = new \yii\db\Expression('extract(year FROM saledate)');
		$m = new \yii\db\Expression('extract(month FROM saledate)');
		$s = new \yii\db\Expression('sum(sum_gross)');
		$fieldList = ['y' => $y, 'm' => $m, 's' => $s];
		$queryGenerator->setCustomColumn($fieldList);
		$queryGenerator->addCondition('saledate', $displayDate, 'a');
		if ($owner !== 'all') {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e');
		}
		$queryGenerator->setCustomGroup([new \yii\db\Expression('y'), new \yii\db\Expression('m')]);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['y']][] = [$row['m'], (int) $row['s']];
		}
		$dataReader->close();
		$chartData = [
			'labels' => [],
			'datasets' => [],
			'show_chart' => false,
		];
		$this->conditions = ['condition' => ['>', 'saledate', $date]];
		$yearsData = [];
		foreach ($rawData as $y => $raw) {
			$years[] = $y;
			if (!isset($yearsData[$y])) {
				$yearsData[$y] = [
					'data' => [],
					'label' => \App\Language::translate('LBL_YEAR', $moduleName) . ' ' . $y,
					'backgroundColor' => [],
					'borderColor' => [],
				];
				for ($m = 0; $m < 12; $m++) {
					$yearsData[$y]['data'][$m] = [];
				}
			}
			foreach ($raw as $m => &$value) {
				$yearsData[$y]['data'][$m] = ['y' => $value[1], 'x' => (int) $m + 1];
				$hash = md5('color' . $y * 10);
				$color = '#' . substr($hash, 0, 2) . substr($hash, 2, 2) . substr($hash, 4, 2);
				$yearsData[$y]['backgroundColor'][] = $color;
				$yearsData[$y]['borderColor'][] = $color;
				$yearsData[$y]['stack'] = (string) $y;
				$chartData['show_chart'] = true;
			}
		}
		$years = array_values(array_unique($years));
		$chartData['years'] = $years;
		foreach ($years as $y) {
			foreach ($chartData['datasets'] as &$dataset) {
				foreach ($dataset['data'] as $index => &$values) {
					if (count($values) === 0) {
						$values = ['x' => $index, 'y' => 0];
					}
				}
			}
		}
		$chartData['datasets'] = [];
		foreach ($yearsData as $y => $data) {
			$chartData['datasets'][] = $data;
		}
		return $chartData;
	}
}
