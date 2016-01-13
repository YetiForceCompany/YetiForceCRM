<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
require_once 'include/main/WebUI.php';

$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile("1");
$adb = PearDatabase::getInstance();

$statusActivity = Calendar_Module_Model::getComponentActivityStateLabel();
$query = 'SELECT vtiger_activity.`activityid`, vtiger_activity.`due_date`,vtiger_activity.`time_end`,vtiger_activity.`date_start`,vtiger_activity.`time_start`,vtiger_activity.`state` FROM vtiger_activity INNER JOIN vtiger_crmentity AS crm ON crm.crmid = vtiger_activity.`activityid` WHERE vtiger_activity.`status` IN (?,?) AND crm.`deleted` = ?';
$result = $adb->pquery($query, [$statusActivity['not_started'], $statusActivity['in_realization'], 0]);
while ($row = $adb->fetch_array($result)) {
	$state = Calendar_Module_Model::getCalendarState($row);
	if ($state) {
		$recordModel = Vtiger_Record_Model::getInstanceById($row['activityid']);
		$recordModel->set('id', $row['activityid']);
		$recordModel->set('activitystatus', $state);
		$recordModel->set('mode', 'edit');
		$recordModel->save();
	}
}

