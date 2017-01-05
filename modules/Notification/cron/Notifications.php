<?php
/**
 * Cron - Send notifications via mail
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();
$notifications = new Cron_Notification();
$result = $db->query('SELECT * FROM u_yf_watchdog_schedule');
while ($row = $db->getRow($result)) {
	$notifications->executeScheduled($row);
}
$notifications->markAsRead();

class Cron_Notification
{

	const MODULE_NAME = 'Notification';

	/**
	 * Function executes the sending notifications action
	 * @param array $row
	 */
	public function executeScheduled($row)
	{
		$db = PearDatabase::getInstance();
		$currentTime = time();
		$timestampEndDate = empty($row['last_execution']) ? $currentTime : strtotime($row['last_execution'] . ' +' . $row['frequency'] . 'min');
		if ($currentTime >= $timestampEndDate) {
			$endDate = $this->getEndDate($currentTime, $timestampEndDate, $row['frequency']);
			if (\App\Privilege::isPermitted(self::MODULE_NAME, 'ReceivingMailNotifications', false, $row['userid']) && $this->existNotifications($row['userid'], $row['last_execution'], $endDate)) {
				\App\Mailer::sendFromTemplate([
					'template' => 'SendNotificationsViaMail',
					'to' => \App\User::getUserModel($row['userid'])->getDetail('email1'),
					'startDate' => $row['last_execution'],
					'endDate' => $endDate,
					'userId' => $row['userid']
				]);
			}
			\App\Db::getInstance()->createCommand()
				->update('u_#__watchdog_schedule', ['last_execution' => $endDate], ['userid' => $row['userid']])
				->execute();
		}
	}

	/**
	 * Function checks if notification exists
	 * @param int $userId
	 * @param string $startDate
	 * @param string $endDate
	 * @return int
	 */
	private function existNotifications($userId, $startDate, $endDate)
	{
		$scheduleData = Vtiger_Watchdog_Model::getWatchingModulesSchedule($userId, true);
		$modules = $scheduleData['modules'];
		return Notification_Module_Model::getEmailSendEntries($userId, $modules, $startDate, $endDate, true);
	}

	/**
	 * Function get date
	 * @param string $currentTime
	 * @param string $timestampEndDate
	 * @param int $frequency
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
	 * Function get date
	 * @param string $currentTime
	 * @param string $timestampEndDate
	 * @param int $frequency
	 */
	public function markAsRead()
	{
		$notifications = (new \App\Db\Query())
				->select(['smownerid', 'crmid'])
				->from('u_#__notification')
				->innerJoin('vtiger_crmentity', 'u_#__notification.notificationid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, 'notification_status' => 'PLL_UNREAD'])
				->orderBy(['smownerid' => SORT_ASC, 'createdtime' => SORT_ASC])
				->createCommand()->queryAllByGroup(2);
		foreach ($notifications as $userId => $noticesByUser) {
			$noticesByUser = array_slice($noticesByUser, 0, AppConfig::module('Home', 'MAX_NUMBER_NOTIFICATIONS'));
			foreach ($noticesByUser as $noticeId) {
				$notice = Vtiger_Record_Model::getInstanceById($noticeId);
				$notice->setMarked();
			}
		}
	}
}
