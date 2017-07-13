<?php

/**
 * LowTasks class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class LowTasks
{

	public $name = 'LBL_TASKS_LOW';
	public $sequence = 6;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace("Entering LowTasks::process() method ...");
		$adb = PearDatabase::getInstance();
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskpriority = ? && vtiger_crmentity.deleted=0';
		$result = $adb->pquery($query, array($instance->getId(), 'low'));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting LowTasks::process() method ...");
		return $count;
	}
}
