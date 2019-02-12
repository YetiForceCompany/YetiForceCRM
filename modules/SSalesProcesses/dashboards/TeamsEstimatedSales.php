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
		$listSearchParams = [[['estimated_date', 'bw', implode(',', $time)]]];
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
		foreach ($data as $key => $values) {
			if (!isset($previousData[$key])) {
				$previousData[$key] = [0, $values[1], ''];
			}
		}
		foreach ($previousData as $key => $values) {
			if (!isset($data[$key])) {
				$data[$key] = [0, $values[1], ''];
			}
		}
		return [array_values($data), array_values($previousData)];
	}

	/**
	 * Function to get data to chart.
	 *
	 * @param string      $time
	 * @param string|bool $compare
	 *
	 * @return array
	 */
	public function getEstimatedValue($time, $compare = false)
	{
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->setGroup('assigned_user_id');
		$queryGenerator->addCondition('estimated_date', $time, 'bw');
		$sum = new \yii\db\Expression('SUM(estimated)');
		$queryGenerator->setCustomColumn(['estimated' => $sum]);
		$query = $queryGenerator->createQuery();
		$listView = $queryGenerator->getModuleModel()->getListViewUrl();
		$dataReader = $query->createCommand()->query();

		$data = [];
		$i = -1;
		while ($row = $dataReader->read()) {
			$i = $compare ? $row['assigned_user_id'] : $i + 1;
			$data[$i] = [
				$row['estimated'],
				\App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				$listView . $this->getSearchParams($row['assigned_user_id'], $time),
			];
		}
		$dataReader->close();

		return $data;
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
		$linkId = $request->getInteger('linkid');
		$time = $request->getDateRange('time');
		$compare = $request->getBoolean('compare');
		$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
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

		$data = $this->getEstimatedValue($timeSting, $compare);
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
			$previousData = $this->getEstimatedValue($previousTime, $compare);
			if (!empty($data) || !empty($previousData)) {
				list($data, $previousData) = $this->parseData($data, $previousData);
				$data = [$previousData, 'compare' => $data];
			}
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $timeSting);
		$viewer->assign('COMPARE', $compare);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TeamsEstimatedSales.tpl', $moduleName);
		}
	}
}
