<?php

/**
 * OtherTasks class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OtherTasks
{

	public $name = 'LBL_TASKS_OTHER';
	public $sequence = 4;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace("Entering OtherTasks::process() method ...");
		$adb = PearDatabase::getInstance();
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_crmentity.deleted=0 && vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskpriority NOT IN (?,?,?) ';
		$result = $adb->pquery($query, array($instance->getId(), 'high', 'low', 'normal'));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting OtherTasks::process() method ...");
		return $count;
	}
}
