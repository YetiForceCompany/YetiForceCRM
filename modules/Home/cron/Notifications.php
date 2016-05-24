<?php
/**
 * Cron - Send notifications via mail
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/main/WebUI.php';
$db = PearDatabase::getInstance();
$result = $db->query('SELECT * FROM u_yf_watchdog_schedule');
while ($row = $db->getRow($result)) {
	executeScheduled($row);
}

function executeScheduled($row)
{
	$db = PearDatabase::getInstance();
	$currentTime = time();
	$timestampEndDate = empty($row['last_execution']) ? $currentTime : strtotime($row['last_execution'] . ' +' . $row['frequency'] . 'min');
	if ($currentTime >= $timestampEndDate) {
		$endDate = getEndDate($currentTime, $timestampEndDate, $row['frequency']);
		if (existNotifications($row['userid'], $row['last_execution'], $endDate) && Users_Privileges_Model::isPermittedByUserId($row['userid'], 'Dashboard', 'ReceivingMailNotifications')) {
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

function existNotifications($userId, $startDate, $endDate)
{
	$db = PearDatabase::getInstance();
	$query = 'SELECT 1 FROM l_yf_notification WHERE `userid` = ?';
	$params = [$userId];
	if (empty($startDate)) {
		$query .= ' AND `time` <= ?';
		$params[] = $endDate;
	} else {
		$query .= ' AND `time` BETWEEN ? AND ?';
		array_push($params, $startDate, $endDate);
	}
	$query .= ' LIMIT 1';
	$result = $db->pquery($query, $params);
	return (bool) $result->rowCount();
}

function getEndDate($currentTime, $timestampEndDate, $frequency)
{
	while ($timestampEndDate <= $currentTime && ($nextEndDateTime = $timestampEndDate + ($frequency * 60)) <= $currentTime) {
		$timestampEndDate = $nextEndDateTime;
	}
	return date('Y-m-d H:i:s', $timestampEndDate);
}
