<?php
$adb = PearDatabase::getInstance();
\App\Log::trace(' Start SendReminder ');

$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_activity.*, vtiger_activity_reminder.reminder_time, vtiger_activity_reminder.reminder_sent, vtiger_crmentity.setype AS crmsetype
FROM vtiger_activity 
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid 
INNER JOIN vtiger_activity_reminder ON vtiger_activity.activityid=vtiger_activity_reminder.activity_id 
WHERE DATE_FORMAT(vtiger_activity.date_start,'%Y-%m-%d, %H:%i:%s') >= ? 
AND vtiger_crmentity.crmid != 0 
AND vtiger_activity.status = 'PLL_PLANNED' 
AND vtiger_activity_reminder.reminder_sent = 0 
GROUP BY vtiger_activity.activityid";

$result = $adb->pquery($query, [date('Y-m-d')]);
if ($adb->getRowCount($result) >= 1) {
	//To fetch reminder frequency from cron tasks
	$reminderFrequencyQuery = 'SELECT frequency FROM vtiger_cron_task WHERE name = "SendReminder"';
	$reminderResult = $adb->query($reminderFrequencyQuery);
	$reminderFrequency = $adb->getSingleValue($reminderResult);

	$eventsRecordModel = Vtiger_Record_Model::getCleanInstance('Events');

	while ($row = $adb->getRow($result)) {
		$date_start = $row['date_start'];
		$time_start = $row['time_start'];
		$reminder_time = $row['reminder_time'] * 60;
		$date = new DateTimeField(null);
		$userFormatedString = $date->getDisplayDate();
		$timeFormatedString = $date->getDisplayTime();
		$dBFomatedDate = DateTimeField::convertToDBFormat($userFormatedString);
		$curr_time = strtotime("$dBFomatedDate $timeFormatedString");
		$activityId = $row['activityid'];
		$activitymode = ($row['activitytype'] == 'Task') ? 'Task' : 'Events';
		$parent_type = $row['setype'];
		$activity_sub = $row['subject'];
		$to_addr = '';

		//code included for recurring events by jaguar ends
		$date = new DateTimeField("$date_start $time_start");
		$userFormatedString = $date->getDisplayDate();
		$timeFormatedString = $date->getDisplayTime();
		$dBFomatedDate = DateTimeField::convertToDBFormat($userFormatedString);
		$activity_time = strtotime("$dBFomatedDate $timeFormatedString");
		$differenceOfActivityTimeAndCurrentTime = ($activity_time - $curr_time);

		if (($differenceOfActivityTimeAndCurrentTime > 0) && (($differenceOfActivityTimeAndCurrentTime <= $reminder_time) || ($differenceOfActivityTimeAndCurrentTime <= $reminderFrequency))) {
			\App\Log::trace('Start Send SendReminder');
			$toEmail = App\Fields\Email::getUserMail($row['smownerid']);
			$invitees = [];

			if ($row['activitytype'] == 'Task') {
				$template = 'ActivityReminderNotificationTask';
			} else {
				$template = 'ActivityReminderNotificationEvents';
				$eventsRecordModel->set('id', $activityId);
				if (AppConfig::module('Calendar', 'SEND_REMINDER_INVITATION')) {
					$invitees = $eventsRecordModel->getInvities();
				}
			}
			if (!empty($toEmail)) {
				\App\Mailer::sendFromTemplate([
					'template' => $template,
					'moduleName' => 'Calendar',
					'recordId' => $activityId,
					'to' => $toEmail,
				]);
				$params = ['reminder_sent' => 1];
				$query = 'activity_id = ?';
				$adb->update('vtiger_activity_reminder', $params, $query, [$activityId]);
			}
			foreach ($invitees as &$invitation) {
				if (!empty($invitation['email'])) {
					\App\Mailer::sendFromTemplate([
						'template' => 'ActivityReminderNotificationInvitation',
						'moduleName' => 'Calendar',
						'recordId' => $activityId,
						'to' => $invitation['email'],
					]);
				}
			}
			\App\Log::trace('End Send SendReminder');
		}
	}
}
