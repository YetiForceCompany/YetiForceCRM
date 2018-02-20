<?php

/**
 * OSSEmployees module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		$queryGenerator->addNativeCondition(['vtiger_ossemployees.employee_status' => 'Employee']);
	}

	public function getWorkingDays($startDate, $endDate)
	{
		$begin = strtotime($startDate);
		$end = strtotime($endDate);
		if ($begin > $end) {
			return 0;
		} else {
			$no_days = 0;
			$weekends = 0;
			while ($begin <= $end) {
				++$no_days; // no of days in the given interval
				$what_day = date('N', $begin);
				if ($what_day > 5) { // 6 and 7 are weekend days
					++$weekends;
				}
				$begin += 86400; // +1 day
			}
			$working_days = $no_days - $weekends;

			return $working_days;
		}
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
