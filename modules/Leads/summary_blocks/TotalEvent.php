<?php

/**
 * TotalEvent class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TotalEvent
{

	public $name = 'Total event';
	public $sequence = 3;
	public $reference = 'Calendar';

	public function process($instance)
	{

		\App\Log::trace("Entering TotalEvent::process() method ...");
		$adb = PearDatabase::getInstance();
		$activity = 'SELECT COUNT(vtiger_activity.activityid) AS count
			FROM vtiger_activity 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid 
			WHERE vtiger_activity.link=? 
			AND vtiger_crmentity.deleted = 0 
			AND vtiger_activity.activitytype <> ?';
		$result_Event = $adb->pquery($activity, array($instance->getId(), 'Task'));
		$count = $adb->query_result($result_Event, 0, 'count');
		\App\Log::trace("Exiting TotalEvent::process() method ...");
		return $count;
	}
}
