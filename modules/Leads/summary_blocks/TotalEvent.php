<?php

/**
 * TotalEvent class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class TotalEvent
{
	public $name = 'Total event';
	public $sequence = 3;
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
		\App\Log::trace('Entering TotalEvent::process() method ...');
		$count = (new App\Db\Query())->from('vtiger_activity')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
			->where([
				'and',
				['vtiger_activity.link' => $recordModel->getId()],
				['vtiger_crmentity.deleted' => 0],
				['<>', 'vtiger_activity.activitytype', 'Task'],
			])->count('vtiger_activity.activityid');
		\App\Log::trace('Exiting TotalEvent::process() method ...');

		return $count;
	}
}
