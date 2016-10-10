<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_BruteForce_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getConfig()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT * FROM vtiger_bruteforce", true);
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$output[] = $db->query_result($result, $i, 'value');
		}
		return $output;
	}

	static public function getBruteForceSettings()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT * FROM vtiger_bruteforce", true);
		$output = $db->query_result_rowdata($result, 0);
		return $output;
	}

	static public function getBlockedIP()
	{
		$db = PearDatabase::getInstance();
		$bruteforceSettings = self::getBruteForceSettings();
		$attempsNumber = $bruteforceSettings['attempsnumber'];
		$blockTime = $bruteforceSettings['timelock'];
		$now = date("Y-m-d H:i:s");

		$query = "SELECT  COUNT(*) AS COUNT, user_ip, GROUP_CONCAT(DISTINCT(user_name)) as usersName, login_time, GROUP_CONCAT(DISTINCT(browser)) as browsers"
			. " FROM `vtiger_loginhistory` vlh WHERE "
			. "STATUS = 'Failed login' && "
			. "(UNIX_TIMESTAMP(vlh.login_time) - UNIX_TIMESTAMP(ADDDATE(?, INTERVAL -$blockTime MINUTE))) > 0 "
			. "GROUP BY user_ip "
			. "HAVING COUNT>=?";

		$result = $db->pquery($query, [$now, $attempsNumber]);

		while ($row = $db->fetch_array($result)) {
			$output[] = $row;
		}
		return $output;
	}

	static public function browserDetect()
	{

		$browser = $_SERVER['HTTP_USER_AGENT'];

		if (strpos($browser, 'MSIE') !== FALSE)
			return 'Internet explorer';
		elseif (strpos($browser, 'Trident') !== FALSE) //For Supporting IE 11
			return 'Internet explorer';
		elseif (strpos($browser, 'Firefox') !== FALSE)
			return 'Mozilla Firefox';
		elseif (strpos($browser, 'Chrome') !== FALSE)
			return 'Google Chrome';
		elseif (strpos($browser, 'Opera Mini') !== FALSE)
			return "Opera Mini";
		elseif (strpos($browser, 'Opera') !== FALSE)
			return "Opera";
		elseif (strpos($browser, 'Safari') !== FALSE)
			return "Safari";
		else
			return 'unknow';
	}

	static public function checkBlocked()
	{
		$db = PearDatabase::getInstance();

		$query = "SELECT * FROM `vtiger_bruteforce` LIMIT 1";
		$result = $db->pquery($query, array());
		$ip = vtlib\Functions::getRemoteIP();
		$bruteforceSettings = $db->query_result_rowdata($result, 0);
		$attempsNumber = $bruteforceSettings['attempsnumber'];
		$blockTime = $bruteforceSettings['timelock'];

		$blockDate = new DateTime();
		$blockDate->modify("-$blockTime minutes");

		$query = "SELECT count(login_id) as cn FROM `vtiger_loginhistory` vlh 
			WHERE STATUS = 'Failed login' && user_ip = ? && unblock = 0 
			AND vlh.login_time > ?";
		$result = $db->pquery($query, array($ip, $blockDate->format('Y-m-d H:i:s')));
		if ($db->getSingleValue($result) >= $attempsNumber) {
			return true;
		}
		return false;
	}

	public static function getAdminUsers()
	{
		$adb = PearDatabase::getInstance();
		$query = "SELECT id, user_name FROM `vtiger_users` WHERE is_admin = 'on' && deleted = 0";
		$result = $adb->query($query);
		$numRows = $adb->num_rows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$userId = $adb->query_result_raw($result, $i, 'id');
			$userName = $adb->query_result_raw($result, $i, 'user_name');
			$output[$userId] = $userName;
		}

		return $output;
	}

	public static function updateConfig($number, $timelock, $active)
	{
		$adb = PearDatabase::getInstance();

		if ('true' == $active) {
			$active = TRUE;
		} else {
			$active = FALSE;
		}

		$query = "UPDATE vtiger_bruteforce SET attempsnumber = ?, timelock = ?, active = ?;";
		$params = array($number, $timelock, $active);
		$result = $adb->pquery($query, $params);

		return $result;
	}

	public static function updateUsersForNotifications($selectedUsers)
	{
		$adb = PearDatabase::getInstance();
		$deleteQuery = "DELETE FROM `vtiger_bruteforce_users`";
		$adb->query($deleteQuery);
		if ('null' != $selectedUsers) {
			$insertQuery = "INSERT INTO `vtiger_bruteforce_users` (id) VALUES(?)";
			foreach ($selectedUsers as $userId) {
				$adb->pquery($insertQuery, array($userId));
			}
		}

		return TRUE;
	}

	public static function getUsersForNotifications()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query("SELECT * FROM vtiger_bruteforce_users", true);
		$numRows = $adb->num_rows($result);
		$output = [];
		for ($i = 0; $i < $numRows; $i++) {
			$id = $adb->query_result($result, $i, 'id');
			$output[$id] = $id;
		}

		return $output;
	}

	public static function sendNotificationEmail()
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . '::' . __METHOD__);
		$usersId = self::getUsersForNotifications();
		if (count($usersId) == 0) {
			$log->fatal('No brute force users found to send email');
			return false;
		}
		foreach ($usersId as $id) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Users');
			$userEmail = $recordModel->get('email1');
			$emails[] = $userEmail;
		}
		$emailsList = implode(',', $emails);
		$data = [
			'sysname' => 'BruteForceSecurityRiskHasBeenDetected',
			'to_email' => $emailsList,
			'module' => 'Contacts',
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		$mail_status = $recordModel->sendMailFromTemplate($data);

		if ($mail_status != 1) {
			$log->error('Do not sent mail with information about brute force attack');
		}
		$log->debug('End ' . __CLASS__ . '::' . __METHOD__);
	}
}
