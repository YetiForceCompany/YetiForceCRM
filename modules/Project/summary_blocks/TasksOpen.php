<?php

/**
 * TasksOpen class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TasksOpen
{

	public $name = 'LBL_TASKS_OPEN';
	public $sequence = 2;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace("Entering TasksOpen::process() method ...");
		$adb = PearDatabase::getInstance();
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_crmentity.deleted=0 && vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskstatus = ? ';
		$result = $adb->pquery($query, array($instance->getId(), 'Open'));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting TasksOpen::process() method ...");
		return $count;
	}
}
