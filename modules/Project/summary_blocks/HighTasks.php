<?php

/**
 * HighTasks class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class HighTasks
{

	public $name = 'LBL_TASKS_HIGH';
	public $sequence = 8;
	public $reference = 'ProjectTask';

	public function process($instance)
	{

		\App\Log::trace('Entering HighTasks::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $instance->getId(), 'vtiger_projecttask.projecttaskpriority' => 'high', 'vtiger_crmentity.deleted' => 0])->count();
		\App\Log::trace('Exiting HighTasks::process() method ...');
		return $count;
	}
}
