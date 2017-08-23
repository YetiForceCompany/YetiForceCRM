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

		\App\Log::trace('Entering TasksOpen::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $instance->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_projecttask.projecttaskstatus' => 'Open'])->count();
		\App\Log::trace('Exiting TasksOpen::process() method ...');
		return $count;
	}
}
