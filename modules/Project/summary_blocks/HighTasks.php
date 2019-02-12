<?php

/**
 * HighTasks class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class HighTasks
{
	public $name = 'LBL_TASKS_HIGH';
	public $sequence = 8;
	public $reference = 'ProjectTask';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HighTasks::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $recordModel->getId(), 'vtiger_projecttask.projecttaskpriority' => 'high', 'vtiger_crmentity.deleted' => 0])->count();
		\App\Log::trace('Exiting HighTasks::process() method ...');

		return $count;
	}
}
