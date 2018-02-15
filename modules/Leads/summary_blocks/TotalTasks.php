<?php
/**
 * TotalTasks class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Summary block gets tasks count.
 */
class TotalTasks
{
	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name = 'Total tasks';

	/**
	 * Sequence.
	 *
	 * @var int
	 */
	public $sequence = 4;

	/**
	 * Reference.
	 *
	 * @var string
	 */
	public $reference = 'Calendar';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering TotalTasks::process() method ...');
		$count = (new \App\Db\Query())->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')->where(['vtiger_activity.link' => $recordModel->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_activity.activitytype' => 'Task'])->count();
		\App\Log::trace('Exiting TotalTasks::process() method ...');

		return $count;
	}
}
