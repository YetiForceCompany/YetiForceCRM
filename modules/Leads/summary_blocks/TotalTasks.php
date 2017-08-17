<?php
/**
 * TotalTasks class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * Summary block total tasks for leads
 */
class TotalTasks
{

	/**
	 * Name
	 * @var string
	 */
	public $name = 'Total tasks';

	/**
	 * Sequence
	 * @var int
	 */
	public $sequence = 4;

	/**
	 * Reference
	 * @var string
	 */
	public $reference = 'Calendar';

	/**
	 * Process
	 * @param Vtiger_Record_Model $instance
	 * @return type
	 */
	public function process($instance)
	{

		\App\Log::trace('Entering TotalTasks::process() method ...');
		$count = (new \App\Db\Query())->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')->where(['vtiger_activity.link' => $instance->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_activity.activitytype' => 'Task'])->count();
		\App\Log::trace('Exiting TotalTasks::process() method ...');
		return $count;
	}
}
