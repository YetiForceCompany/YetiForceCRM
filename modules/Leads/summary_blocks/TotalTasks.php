<?php

/**
 * TotalTasks class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TotalTasks
{

	public $name = 'Total tasks';
	public $sequence = 4;
	public $reference = 'Calendar';

	public function process($instance)
	{

		\App\Log::trace("Entering TotalTasks::process() method ...");
		$adb = PearDatabase::getInstance();
		$activity = 'SELECT COUNT(vtiger_activity.activityid) AS count
			FROM vtiger_activity 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid 
			WHERE vtiger_activity.link=? 
			AND vtiger_crmentity.deleted = 0 
			AND vtiger_activity.activitytype = ?';
		$result_Task = $adb->pquery($activity, array($instance->getId(), 'Task'));
		$count = $adb->query_result($result_Task, 0, 'count');
		\App\Log::trace("Exiting TotalTasks::process() method ...");
		return $count;
	}
}
