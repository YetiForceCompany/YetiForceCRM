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

	/**
	 * Process
	 * @param Vtiger_Record_Model $instance
	 * @return int
	 */
	public function process(Vtiger_Record_Model $instance)
	{

		\App\Log::trace('Entering OtherTasks::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $instance->getId(), 'vtiger_crmentity.deleted' => 0])->andWhere(['not in', 'vtiger_projecttask.projecttaskpriority', ['high', 'low', 'normal']])->count();
		\App\Log::trace('Exiting OtherTasks::process() method ...');
		return $count;
	}
}
