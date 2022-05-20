<?php
/**
 * Cron - Send notifications via mail.
 *
 * @package Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radoslaw Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Notification_Notifications_Cron class.
 */
class Notification_Notifications_Cron extends \App\CronHandler
{
	const MODULE_NAME = 'Notification';

	/** {@inheritdoc} */
	public function process()
	{
		$query = (new \App\Db\Query())->from('u_#__watchdog_schedule');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->executeScheduled($row);
		}
		$dataReader->close();
	}

	/**
	 * Function executes the sending notifications action.
	 *
	 * @param array $row
	 */
	private function executeScheduled($row)
	{
		$currentTime = time();
		$timestampEndDate = empty($row['last_execution']) ? $currentTime : strtotime($row['last_execution'] . ' +' . $row['frequency'] . 'min');
		if ($currentTime >= $timestampEndDate) {
			$endDate = $this->getEndDate($currentTime, $timestampEndDate, $row['frequency']);
			$notificationsToSend = $this->existNotifications($row['userid'], $row['last_execution'], $endDate);
			if (\App\Privilege::isPermitted(self::MODULE_NAME, 'ReceivingMailNotifications', false, $row['userid']) && $notificationsToSend) {
				$ifEmailSend = \App\Mailer::sendFromTemplate([
					'moduleName' => 'Notification',
					'template' => 'SendNotificationsViaMail',
					'to' => \App\User::getUserModel($row['userid'])->getDetail('email1'),
					'startDate' => $row['last_execution'],
					'endDate' => $endDate,
					'userId' => $row['userid'],
				]);
				if ($ifEmailSend && \App\Config::module('Notification', 'AUTO_MARK_NOTIFICATIONS_READ_AFTER_EMAIL_SEND')) {
					$this->markSentNotificationsAsRead($notificationsToSend);
				}
			}
			\App\Db::getInstance()->createCommand()
				->update('u_#__watchdog_schedule', ['last_execution' => $endDate], ['userid' => $row['userid']])
				->execute();
		}
	}

	/**
	 * Function checks if notification exists.
	 *
	 * @param int    $userId
	 * @param string $startDate
	 * @param string $endDate
	 *
	 * @return array
	 */
	private function existNotifications($userId, $startDate, $endDate): array
	{
		$scheduleData = Vtiger_Watchdog_Model::getWatchingModulesSchedule($userId, true);
		if ($scheduleData) {
			$modules = $scheduleData['modules'];
			return Notification_Module_Model::getEmailSendEntries($userId, $modules, $startDate, $endDate);
		}
		return [];
	}

	/**
	 * Function get date.
	 *
	 * @param string $currentTime
	 * @param string $timestampEndDate
	 * @param int    $frequency
	 *
	 * @return string
	 */
	private function getEndDate($currentTime, $timestampEndDate, $frequency)
	{
		while ($timestampEndDate <= $currentTime && ($nextEndDateTime = $timestampEndDate + ($frequency * 60)) <= $currentTime) {
			$timestampEndDate = $nextEndDateTime;
		}
		return date('Y-m-d H:i:s', $timestampEndDate);
	}

	/**
	 * Function set notifications as read.
	 *
	 * @param mixed $notificationsToSend
	 */
	private function markSentNotificationsAsRead($notificationsToSend): void
	{
		foreach ($notificationsToSend as $notificationsType) {
			foreach ($notificationsType as $notification) {
				$noticeRecordModel = \Vtiger_Record_Model::getInstanceById($notification->get('notificationid'), 'Notification');
				$noticeRecordModel->setMarked();
			}
		}
	}
}
