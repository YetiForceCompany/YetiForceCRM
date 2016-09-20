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

	public function executeScheduled($row)
	{
		$db = PearDatabase::getInstance();
		$currentTime = time();
		$timestampEndDate = empty($row['last_execution']) ? $currentTime : strtotime($row['last_execution'] . ' +' . $row['frequency'] . 'min');
		if ($currentTime >= $timestampEndDate) {
			$endDate = $this->getEndDate($currentTime, $timestampEndDate, $row['frequency']);
			if ($this->existNotifications($row['userid'], $row['last_execution'], $endDate) && Users_Privileges_Model::isPermittedByUserId($row['userid'], 'Dashboard', 'ReceivingMailNotifications')) {
				$data = [
					'sysname' => 'SendNotificationsViaMail',
					'to_email' => getUserEmail($row['userid']),
					'module' => 'System',
					'startDate' => $row['last_execution'],
					'endDate' => $endDate,
					'userId' => $row['userid']
				];
				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
				$recordModel->sendMailFromTemplate($data);
			}
			$db->update('u_yf_watchdog_schedule', ['last_execution' => $endDate], 'userid = ?', [$row['userid']]);
		}
	}

	private function existNotifications($userId, $startDate, $endDate)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT 1 FROM l_yf_notification WHERE `userid` = ?';
		$params = [$userId];
		if (empty($startDate)) {
			$query .= ' && `time` <= ?';
			$params[] = $endDate;
		} else {
			$query .= ' && `time` BETWEEN ? AND ?';
			array_push($params, $startDate, $endDate);
		}
		$query .= ' LIMIT 1';
		$result = $db->pquery($query, $params);
		return (bool) $result->rowCount();
	}

	private function getEndDate($currentTime, $timestampEndDate, $frequency)
	{
		while ($timestampEndDate <= $currentTime && ($nextEndDateTime = $timestampEndDate + ($frequency * 60)) <= $currentTime) {
			$timestampEndDate = $nextEndDateTime;
		}
		return date('Y-m-d H:i:s', $timestampEndDate);
	}

	public function markAsRead()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT userid, id FROM l_yf_notification ORDER BY userid, `time` DESC');
		$notifications = $db->getColumnByGroup($result);
		foreach ($notifications as $userId => $noticesByUser) {
			$noticesByUser = array_slice($noticesByUser, AppConfig::module('Home', 'MAX_NUMBER_NOTIFICATIONS'));
			foreach ($noticesByUser as $noticeId) {
				$notice = Home_NoticeEntries_Model::getInstanceById($noticeId);
				$notice->setMarked();
			}
		}
	}
}
