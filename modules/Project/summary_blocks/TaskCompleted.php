<?php

/**
 * TaskCompleted class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TaskCompleted
{

	public $name = 'LBL_TASKS_COMPLETED';
	public $sequence = 1;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace("Entering TaskCompleted::process() method ...");
		$adb = PearDatabase::getInstance();
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_crmentity.deleted=0 && vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskstatus = ? ';
		$result = $adb->pquery($query, array($instance->getId(), 'Completed'));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting TaskCompleted::process() method ...");
		return $count;
	}
}
