<?php
/**
 * Recurring events cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Calendar_RecurringEvents_Cron class.
 */
class Calendar_RecurringEvents_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$dataReader = (new App\Db\Query())->select(['followup'])
			->from('vtiger_activity')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
			->where([
				'and',
				['vtiger_crmentity.deleted' => 0],
				['vtiger_crmentity.setype' => 'Calendar'],
				['vtiger_activity.reapeat' => 1],
				['NOT', ['vtiger_activity.recurrence' => null]],
				['not like', 'vtiger_activity.recurrence', ['UNTIL', 'COUNT']],
			])->distinct('followup')->createCommand()->query();
		$recurringEvents = Calendar_RecuringEvents_Model::getInstance();
		while ($row = $dataReader->read()) {
			if (!empty($row['followup'])) {
				$recurringEvents->updateNeverEndingEvents($row['followup']);
			}
		}
		$dataReader->close();
	}
}
