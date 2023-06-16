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
			array_push($conditions, ['actual_date', 'bw', implode(',', $time)]);
		}
		$listSearchParams[] = $conditions;

		return '&viewname=All&search_params=' . urlencode(json_encode($listSearchParams));
	}

	/** {@inheritdoc} */
	public function getQuery(array $time, $owner = false): App\QueryGenerator
	{
		$sum = new \yii\db\Expression('SUM(actual_sale)');
		$queryGenerator = new \App\QueryGenerator('SSalesProcesses');
		$queryGenerator->setFields(['assigned_user_id'])
			->setCustomColumn(['estimated' => $sum])
			->setGroup('assigned_user_id')
			->addCondition('actual_date', implode(',', $time), 'bw');
		if ('all' !== $owner) {
			$queryGenerator->addNativeCondition(['smownerid' => $owner]);
		}

		return $queryGenerator;
	}
}
