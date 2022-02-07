<?php

/**
 * OSSEmployees module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_Module_Model extends Vtiger_Module_Model
{
	public function getWorkingDays($startDate, $endDate)
	{
		$begin = strtotime($startDate);
		$end = strtotime($endDate);
		if ($begin > $end) {
			return 0;
		}
		$noDays = 0;
		$weekends = 0;
		while ($begin <= $end) {
			++$noDays; // no of days in the given interval
			$whatDay = date('N', $begin);
			if ($whatDay > 5) { // 6 and 7 are weekend days
				++$weekends;
			}
			$begin += 86400; // +1 day
		}
		return $noDays - $weekends;
	}

	public function getBarChartColors($chartData)
	{
		$i = 0;
		$colors = ['#4bb2c5', '#EAA228', '#c5b47f'];
		foreach ($chartData as $key => $value) {
			$result[$key] = $colors[$i];
			++$i;
		}
		return $result;
	}
}
