<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

$statusActivity = Calendar_Module_Model::getComponentActivityStateLabel();
$dataReader = (new App\Db\Query())->select(['vtiger_activity.activityid', 'vtiger_activity.due_date', 'vtiger_activity.time_end',
			'vtiger_activity.date_start', 'vtiger_activity.time_start', 'activitystatus' => 'vtiger_activity.status'])
		->from('vtiger_activity')
		->innerJoin(['crm' => 'vtiger_crmentity'], 'crm.crmid = vtiger_activity.activityid')
		->where(['vtiger_activity.status' => [$statusActivity['not_started'], $statusActivity['in_realization']], 'crm.deleted' => 0, 'crm.setype' => 'Calendar'])
		->limit(AppConfig::module('Calendar', 'CRON_MAX_NUMERS_ACTIVITY_STATE'))
		->createCommand()->query();
while ($row = $dataReader->read()) {
	$state = Calendar_Module_Model::getCalendarState($row);
	if ($state && $state != $row['activitystatus']) {
		$recordModel = Vtiger_Record_Model::getInstanceById($row['activityid']);
		$recordModel->set('id', $row['activityid']);
		$recordModel->set('activitystatus', $state);
		$recordModel->save();
	}
}

