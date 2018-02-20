<?php

/**
 * NormalTasks class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class NormalTasks
{
	public $name = 'LBL_TASKS_NORMAL';
	public $sequence = 7;
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
		\App\Log::trace('Entering NormalTasks::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_projecttask')->innerJoin('vtiger_crmentity', 'vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid')->where(['vtiger_projecttask.projectid' => $recordModel->getId(), 'vtiger_projecttask.projecttaskpriority' => 'normal', 'vtiger_crmentity.deleted' => 0])->count();
		\App\Log::trace('Exiting NormalTasks::process() method ...');

		return $count;
	}
}
