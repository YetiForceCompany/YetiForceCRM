<?php
/**
 * Cron to change state of events in calendar.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Calendar_ActivityState_Cron class.
 */
class Calendar_ActivityState_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$statusActivity = Calendar_Module_Model::getComponentActivityStateLabel();
		$dataReader = (new App\Db\Query())->select(['vtiger_activity.activityid', 'vtiger_activity.due_date', 'vtiger_activity.time_end',
			'vtiger_activity.date_start', 'vtiger_activity.time_start', 'activitystatus' => 'vtiger_activity.status', ])
			->from('vtiger_activity')
			->innerJoin(['crm' => 'vtiger_crmentity'], 'crm.crmid = vtiger_activity.activityid')
			->where(['vtiger_activity.status' => [$statusActivity['not_started'], $statusActivity['in_realization']], 'crm.deleted' => 0, 'crm.setype' => 'Calendar'])
			->orderBy(['vtiger_activity.date_start' => SORT_ASC])
			->limit(App\Config::module('Calendar', 'CRON_MAX_NUMBERS_ACTIVITY_STATE'))
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$state = Calendar_Module_Model::getCalendarState($row);
			if ($state && $state != $row['activitystatus']) {
				$recordModel = Vtiger_Record_Model::getInstanceById($row['activityid']);
				$recordModel->setId($row['activityid']);
				$recordModel->set('activitystatus', $state);
				$recordModel->save();
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
		$dataReader->close();
	}
}
