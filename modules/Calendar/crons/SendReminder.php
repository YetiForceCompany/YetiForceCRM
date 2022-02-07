<?php
/**
 * Send information about events in calendar.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Calendar_SendReminder_Cron class.
 */
class Calendar_SendReminder_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		\App\Log::trace('Start SendReminder');
		$dataReader = (new \App\Db\Query())->select([
			'vtiger_crmentity.smownerid',
			'vtiger_activity.date_start',
			'vtiger_activity.time_start',
			'vtiger_activity.activityid',
			'vtiger_activity.activitytype',
			'vtiger_activity_reminder.reminder_time'
		])->from('vtiger_activity')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid=vtiger_activity.activityid')
			->innerJoin('vtiger_activity_reminder', 'vtiger_activity.activityid = vtiger_activity_reminder.activity_id')
			->where(['and',
				['>=', 'vtiger_activity.date_start', date('Y-m-d')],
				['vtiger_activity.status' => 'PLL_PLANNED'],
				['vtiger_activity_reminder.reminder_sent' => 0],
			])->createCommand()->query();
		if ($dataReader->count()) {
			//To fetch reminder frequency from cron tasks
			$reminderFrequency = (new \App\Db\Query())->select(['frequency'])->from('vtiger_cron_task')->where(['name' => 'LBL_SEND_REMINDER'])->scalar();
			$dbCommand = App\Db::getInstance()->createCommand();
			$recordModel = Vtiger_Record_Model::getCleanInstance('Calendar');
			while ($row = $dataReader->read()) {
				$dateStart = $row['date_start'];
				$timeStart = $row['time_start'];
				$reminderTime = $row['reminder_time'] * 60;
				$date = new DateTimeField(null);
				$userFormatedString = $date->getDisplayDate();
				$timeFormatedString = $date->getDisplayTime();
				$dBFomatedDate = DateTimeField::convertToDBFormat($userFormatedString);
				$currTime = strtotime("$dBFomatedDate $timeFormatedString");
				$activityId = $row['activityid'];
				//code included for recurring events by jaguar ends
				$date = new DateTimeField("$dateStart $timeStart");
				$userFormatedString = $date->getDisplayDate();
				$timeFormatedString = $date->getDisplayTime();
				$dBFomatedDate = DateTimeField::convertToDBFormat($userFormatedString);
				$activityTime = strtotime("$dBFomatedDate $timeFormatedString");
				$differenceOfActivityTimeAndCurrentTime = ($activityTime - $currTime);
				if (($differenceOfActivityTimeAndCurrentTime > 0) && (($differenceOfActivityTimeAndCurrentTime <= $reminderTime) || ($differenceOfActivityTimeAndCurrentTime <= $reminderFrequency))) {
					\App\Log::trace('Start Send SendReminder');
					$toEmail = App\Fields\Email::getUserMail($row['smownerid']);
					$invitees = [];
					$recordModel->setId($activityId);
					if (App\Config::module('Calendar', 'SEND_REMINDER_INVITATION')) {
						$invitees = $recordModel->getInvities();
					}
					if (!empty($toEmail)) {
						\App\Mailer::sendFromTemplate([
							'template' => 'ActivityReminderNotificationTask',
							'moduleName' => 'Calendar',
							'recordId' => $activityId,
							'to' => $toEmail,
						]);
						$dbCommand->update('vtiger_activity_reminder', ['reminder_sent' => 1], ['activity_id' => $activityId])->execute();
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
	}
}
