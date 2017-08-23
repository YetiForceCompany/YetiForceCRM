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

		\App\Log::trace('Entering LowTasks::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $instance->getId(), 'vtiger_projecttask.projecttaskpriority' => 'low', 'vtiger_crmentity.deleted' => 0])->count();
		\App\Log::trace('Exiting LowTasks::process() method ...');
		return $count;
	}
}
