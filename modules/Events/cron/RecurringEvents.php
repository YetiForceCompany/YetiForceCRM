<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$dataReader = (new App\Db\Query())->select(['followup'])
		->from('vtiger_activity')
		->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
		->where([
			'and',
			['vtiger_crmentity.deleted' => 0],
			['vtiger_crmentity.setype' => 'Calendar'],
			['vtiger_activity.reapeat' => 1],
			['NOT', ['vtiger_activity.recurrence' => null]],
			['not like', 'vtiger_activity.recurrence', ['UNTIL', 'COUNT']]
		])->distinct('followup')->createCommand()->query();
$recurringEvents = Events_RecuringEvents_Model::getInstance();
while ($row = $dataReader->read()) {
	$recurringEvents->updateNeverEndingEvents($row['followup']);
}


