<?php
/**
 * Widget that displays the actual value of the team's sales.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class to Team's actual sales widget.
 */
class SSalesProcesses_ActualSalesOfTeam_Dashboard extends SSalesProcesses_TeamsEstimatedSales_Dashboard
{
	/**
	 * Function to get search params in address listview.
	 *
	 * @param int    $owner  number id of user
	 * @param string $status
	 * @param mixed  $row
	 * @param mixed  $time
	 *
	 * @return string
	 */
	public function getSearchParams($row, $time)
	{
		$conditions = [];
		$listSearchParams = [];
		if (!empty($owner)) {
			array_push($conditions, ['assigned_user_id', 'e', $owner]);
		}
		if (!empty($time)) {
			array_push($conditions, ['actual_date', 'bw', implode(',', \App\Fields\Date::formatRangeToDisplay(explode(',', $time)))]);
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
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
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id']);
		$queryGenerator->setGroup('assigned_user_id');
		$queryGenerator->addCondition('actual_date', $timeString, 'bw', true, false);
		if ('all' !== $owner) {
		 $queryGenerator->addNativeCondition(['smownerid' => $owner]);
		}
		$sum = new \yii\db\Expression('SUM(actual_sale)');
		$queryGenerator->setCustomColumn(['actual_sale' => $sum]);
		$query = $queryGenerator->createQuery();
		$listView = $queryGenerator->getModuleModel()->getListViewUrl();
		$dataReader = $query->createCommand()->query();
		$chartData = [];
		while ($row = $dataReader->read()) {
			$chartData['datasets'][0]['data'][] = round($row['actual_sale'], 2);
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
}
