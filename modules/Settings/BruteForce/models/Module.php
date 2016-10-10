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

	static public function getBruteForceSettings()
	{
		return (new App\db\Query())->from('vtiger_bruteforce')->one();
	}

	public static function getBlockedIP()
	{
		$db = PearDatabase::getInstance();
		$bruteforceSettings = self::getBruteForceSettings();
		$attempsNumber = $bruteforceSettings['attempsnumber'];
		$blockTime = $bruteforceSettings['timelock'];
		$now = date('Y-m-d H:i:s');

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

		if (strpos($browser, 'MSIE') !== false)
			return 'Internet explorer';
		elseif (strpos($browser, 'Trident') !== false) //For Supporting IE 11
			return 'Internet explorer';
		elseif (strpos($browser, 'Firefox') !== false)
			return 'Mozilla Firefox';
		elseif (strpos($browser, 'Chrome') !== false)
			return 'Google Chrome';
		elseif (strpos($browser, 'Opera Mini') !== false)
			return "Opera Mini";
		elseif (strpos($browser, 'Opera') !== false)
			return "Opera";
		elseif (strpos($browser, 'Safari') !== false)
			return "Safari";
		else
			return 'unknow';
	}

	static public function checkBlocked()
	{
		$config = self::getBruteForceSettings();
		$blockDate = new DateTime();
		$blockDate->modify("-{$config['timelock']} minutes");
		$ip = vtlib\Functions::getRemoteIP();

		$count = (new \App\db\Query())
			->from('vtiger_loginhistory')
			->where(['>', 'login_time', $blockDate->format('Y-m-d H:i:s')])
			->andWhere(['status' => 'Failed login'])
			->andWhere(['user_ip' => $ip])
			->andWhere(['unblock' => 0])
			->count(1);
		if ($count >= $config['attempsnumber']) {
			return true;
		}
		return false;
	}

	public static function getAdminUsers()
	{
		$query = (new \App\db\Query())
			->select('id, user_name')
			->from('vtiger_users')
			->where(['is_admin' => 'on'])
			->andWhere(['deleted'=> 0]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$output[$row['id']] = $row['user_name'];
		}
		return $output;
	}

	public static function updateConfig($number, $timelock, $active)
	{
		if ('true' == $active) {
			$active = true;
		} else {
			$active = false;
		}
		$result = \App\DB::getInstance()->createCommand()
				->update('vtiger_bruteforce', [
					'attempsnumber' => $number,
					'timelock' => $timelock,
					'active' => $active,
				])->execute();
		return $result;
	}

	public static function updateUsersForNotifications($selectedUsers)
	{
		$db = \App\DB::getInstance();
		$db->createCommand()
			->delete('vtiger_bruteforce_users')
			->execute();
		if (!empty($selectedUsers)) {
			foreach ($selectedUsers as $userId) {
				$db->createCommand()
					->insert('vtiger_bruteforce_users', ['id' => $userId])->execute();
			}
		}

		return true;
	}

	public static function getUsersForNotifications()
	{
		$query = (new \App\db\Query())->from('vtiger_bruteforce_users');
		$output = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$id = $row['id'];
			$output[$id] = $id;
		}
	
		return $output;
	}

	public static function sendNotificationEmail()
	{
		
		\App\Log::trace('Start ' . __CLASS__ . '::' . __METHOD__);
		$usersId = self::getUsersForNotifications();
		if (count($usersId) == 0) {
			\App\Log::error('No brute force users found to send email');
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
			\App\Log::error('Do not sent mail with information about brute force attack');
		}
		\App\Log::trace('End ' . __CLASS__ . '::' . __METHOD__);
	}
}
