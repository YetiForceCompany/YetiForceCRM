<?php

/**
 * TasksOpen class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class TasksOpen
{
	public $name = 'LBL_TASKS_OPEN';
	public $sequence = 2;
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
		\App\Log::trace('Entering TasksOpen::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_projecttask.projecttaskstatus' => 'Open'])->count();
		\App\Log::trace('Exiting TasksOpen::process() method ...');

		return $count;
	}
}
