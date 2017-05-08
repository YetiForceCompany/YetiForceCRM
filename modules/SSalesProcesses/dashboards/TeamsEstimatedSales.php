<?php

/**
 * Widget show estimated value sale
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SSalesProcesses_TeamsEstimatedSales_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Function to get search params in address listview
	 * @param int $owner number id of user
	 * @param string $status
	 * @return string
	 */
	public function getSearchParams($row, $time)
	{
		$listSearchParams = [[['estimated_date', 'bw', $time]]];
		if (isset($row['assigned_user_id'])) {
			$listSearchParams[0][] = ['assigned_user_id', 'e', $row['assigned_user_id']];
		}
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Parse data
	 * @param array $data
	 * @param array $previousData
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
	 * Function to get data to chart
	 * @param string $time
	 * @param string|bool $compare
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
			$data [$i] = [
				$row['estimated'],
				\App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				$listView . $this->getSearchParams($row, $time)
			];
		}
		return $data;
	}

	/**
	 * Main function
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$time = $request->get('time');
		$compare = $request->get('compare') === 'true';
		$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
		if (empty($time)) {
			$time = ['start' => ''];
			$date = new \DateTime();
			$time['end'] = $date->format('Y-m-d');
			$date->modify('-30 days');
			$time['start'] = $date->format('Y-m-d');
			$time['start'] = \App\Fields\DateTime::currentUserDisplayDate($time['start']);
			$time['end'] = \App\Fields\DateTime::currentUserDisplayDate($time['end']);
		}
		$timeSting = implode(',', $time);

		$data = $this->getEstimatedValue($timeSting, $compare);
		if ($compare) {
			$start = new \DateTime(\DateTimeField::convertToDBFormat($time['start']));
			$endPeriod = clone $start;
			$end = new \DateTime(\DateTimeField::convertToDBFormat($time['end']));
			$interval = (int) $start->diff($end)->format("%r%a");
			if ($time['start'] !== $time['end']) {
				$interval++;
			}
			$endPeriod->modify("-1 days");
			$start->modify("-{$interval} days");
			$previousTime = \App\Fields\DateTime::currentUserDisplayDate($start->format('Y-m-d')) . ',' . \App\Fields\DateTime::currentUserDisplayDate($endPeriod->format('Y-m-d'));
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

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TeamsEstimatedSales.tpl', $moduleName);
		}
	}
}
