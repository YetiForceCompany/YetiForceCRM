<?php
/**
 * Widget that displays the actual value of the team's sales.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 *
	 * @return string
	 */
	public function getSearchParams($row, $time)
	{
		$listSearchParams = [[['actual_date', 'bw', $time]]];
		if (isset($row['assigned_user_id'])) {
			$listSearchParams[0][] = ['assigned_user_id', 'e', $row['assigned_user_id']];
		}
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
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
		$queryGenerator->addCondition('actual_date', $time, 'bw');
		$sum = new \yii\db\Expression('SUM(actual_sale)');
		$queryGenerator->setCustomColumn(['actual_sale' => $sum]);
		$query = $queryGenerator->createQuery();
		$listView = $queryGenerator->getModuleModel()->getListViewUrl();
		$dataReader = $query->createCommand()->query();

		$data = [];
		$i = -1;
		while ($row = $dataReader->read()) {
			$i = $compare ? $row['assigned_user_id'] : $i + 1;
			$data[$i] = [
				$row['actual_sale'],
				\App\Fields\Owner::getUserLabel($row['assigned_user_id']),
				$listView . $this->getSearchParams($row, $time),
			];
		}
		$dataReader->close();

		return $data;
	}
}
