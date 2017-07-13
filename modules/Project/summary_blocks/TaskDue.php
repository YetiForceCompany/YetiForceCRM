<?php

/**
 * TaskDue class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TaskDue
{

	public $name = 'LBL_TASKS_DUE';
	public $sequence = 3;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace("Entering TaskDue::process() method ...");
		$adb = PearDatabase::getInstance();
		$currentDate = date('Y-m-d');
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_crmentity.deleted=0 && vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskstatus IN (?,?) && vtiger_projecttask.enddate IS NOT NULL && vtiger_projecttask.enddate < ? ';
		$result = $adb->pquery($query, array($instance->getId(), 'Open', 'In Progress', $currentDate));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting TaskDue::process() method ...");
		return $count;
	}
}
