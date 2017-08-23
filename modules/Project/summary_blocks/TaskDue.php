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

		\App\Log::trace('Entering TaskDue::process() method ...');
		$currentDate = date('Y-m-d');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $instance->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_projecttask.projecttaskstatus' => ['Open', 'In Progress']])->andWhere(['and', ['not', ['vtiger_projecttask.enddate' => null]], ['<', 'vtiger_projecttask.enddate', $currentDate]])->count();
		\App\Log::trace('Exiting TaskDue::process() method ...');
		return $count;
	}
}
