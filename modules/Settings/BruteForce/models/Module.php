<?php

/**
 * Brute force model class
 * @package YetiForce.Settings.Module
 * @license licenses/License.html
 * @author YetiForce.com
 */
class Settings_BruteForce_Module_Model extends Settings_Vtiger_Module_Model
{

	const UNBLOCKED = 0;
	const BLOCKED = 1;
	const UNBLOCKED_BY_USER = 2;

	private $isBlocked;
	private $blockedId;

	/**
	 * Function includes class instances
	 * @return Settings_BruteForce_Module_Model
	 */
	public static function getCleanInstance()
	{
		$data = self::getBruteForceSettings();
		$instance = new self();
		$instance->setData($data);
		return $instance;
	}

	/**
	 * Function verifies if module is active
	 * @return boolean
	 */
	public function isActive()
	{
		return (bool) $this->get('active');
	}

	/**
	 * Function returns module configuration
	 * @return array
	 */
	public static function getBruteForceSettings()
	{
		return (new App\Db\Query())->from('a_#__bruteforce')->one();
	}

	/**
	 * Function gets unsuccessful login attempts data of blocked users
	 * @return array
	 */
	public function getBlockedIp()
	{
		$time = $this->get('timelock');
		$blockDate = new DateTime();
		$blockDate->modify("-$time minutes");

		$query = (new \App\Db\Query())
			->select(['COUNT(*) AS c', 'id', 'ip', 'GROUP_CONCAT(DISTINCT(user_name)) as usersName', 'time', 'GROUP_CONCAT(DISTINCT(browser)) as browsers'])
			->from('vtiger_loginhistory')
			->innerJoin('a_#__bruteforce_blocked', 'vtiger_loginhistory.user_ip = a_#__bruteforce_blocked.ip')
			->where(['status' => 'Failed login'])
			->andWhere(['>=', 'time', $blockDate->format('Y-m-d H:i:s')])
			->andWhere(['>=', 'login_time', new \yii\db\Expression('time')])
			->andWhere(['blocked' => self::BLOCKED])
			->groupBy(['ip'])
			->having(['>=', 'c', $this->get('attempsnumber')]);
		return $query->createCommand()->queryAll();
	}

	/**
	 * Function verifies if user is blocked
	 * @return boolean
	 */
	public function isBlockedIp()
	{
		if (isset($this->isBlocked)) {
			return $this->isBlocked;
		}
		$time = $this->get('timelock');
		$blockDate = new DateTime();
		$blockDate->modify("-$time minutes");
		$ip = \App\RequestUtil::getRemoteIP();
		$this->blockedId = (new \App\Db\Query())
			->from('a_#__bruteforce_blocked')
			->where(['>', 'time', $blockDate->format('Y-m-d H:i:s')])
			->andWhere(['ip' => $ip])
			->andWhere(['blocked' => self::BLOCKED])
			->scalar();
		return $this->isBlocked = (bool) $this->blockedId;
	}

	/**
	 * Function increases the number of unsuccessful login attempts
	 */
	public function incAttempts()
	{
		if (!empty($this->blockedId)) {
			\App\Db::getInstance()->createCommand()
				->update('a_#__bruteforce_blocked', [
					'attempts' => new \yii\db\Expression('attempts + 1')
					], ['id' => $this->blockedId])
				->execute();
		}
	}

	/**
	 * Function updates unsuccessful login attempts
	 */
	public function updateBlockedIp()
	{
		\App\Log::trace('Start ' . __CLASS__ . '::' . __METHOD__);
		$db = \App\Db::getInstance();
		$time = $this->get('timelock');
		$date = new DateTime();
		$checkData = $date->modify("-$time minutes")->format('Y-m-d H:i:s');
		$ip = \App\RequestUtil::getRemoteIP();

		$bfData = (new \App\Db\Query())
				->select(['id', 'attempts'])
				->from('a_#__bruteforce_blocked')
				->where(['>=', 'time', $checkData])
				->andWhere(['blocked' => self::UNBLOCKED])
				->andWhere(['ip' => $ip])->one();
		if (!$bfData) {
			$this->setBlockedIp($ip);
		} else {
			$attempts = ++$bfData['attempts'];
			$blocked = $attempts >= $this->get('attempsnumber') ? self::BLOCKED : self::UNBLOCKED;
			$db->createCommand()
				->update('a_#__bruteforce_blocked', [
					'attempts' => $attempts,
					'blocked' => $blocked,
					], ['id' => $bfData['id']])
				->execute();
			$this->isBlocked = $blocked === self::BLOCKED;
			$this->blockedId = $bfData['id'];
		}
		$this->clearBlockedByIp($ip, $checkData);
		\App\Log::trace('End ' . __CLASS__ . '::' . __METHOD__);
	}

	/**
	 * Function adds unsuccessful login attempt to database 
	 * @param int $ip - User IP
	 * @return int - Created record’s ID
	 */
	private function setBlockedIp($ip)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('a_#__bruteforce_blocked', [
			'ip' => $ip,
			'attempts' => 1,
			'blocked' => self::UNBLOCKED,
		])->execute();
		$this->isBlocked = false;
		return $this->blockedId = $db->getLastInsertID();
	}

	/**
	 * Function removes redundant entries from database
	 * @param int $ip - User IP
	 * @param string $data - Cut-off date of user’s block condition
	 */
	private function clearBlockedByIp($ip, $data)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('a_#__bruteforce_blocked', [
			'and', ['<', 'time', $data],
			['blocked' => self::UNBLOCKED],
			['ip' => $ip]])->execute();
	}

	/**
	 * Function unblocks user
	 * @param int $id - Record ID
	 */
	public static function unBlock($id)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		return \App\Db::getInstance()->createCommand()
				->update('a_#__bruteforce_blocked', [
					'blocked' => self::UNBLOCKED_BY_USER,
					'userid' => $currentUser->getRealId()
					], ['id' => $id])
				->execute();
	}

	/**
	 * Function returns a list of users who are administrators
	 * @return array - List of users
	 */
	public static function getAdminUsers()
	{
		return \includes\fields\Owner::getInstance()->getUsers(false, 'Active', false, false, true);
	}

	/**
	 * Function updates module configuration 
	 * @param array $data - Configuration data
	 */
	public static function updateConfig($data)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()
			->update('a_#__bruteforce', [
				'attempsnumber' => $data['attempsnumber'],
				'timelock' => $data['timelock'],
				'active' => $data['active'],
				'sent' => $data['sent'],
			])->execute();
		$db->createCommand()->delete('a_#__bruteforce_users')->execute();
		if (!empty($data['selectedUsers'])) {
			$users = !is_array($data['selectedUsers']) ? [$data['selectedUsers']] : $data['selectedUsers'];
			foreach ($users as $userId) {
				$db->createCommand()->insert('a_#__bruteforce_users', ['id' => $userId])->execute();
			}
		}
	}

	/**
	 * Function returns table of users selected for notifications
	 * @return array - ID list of users
	 */
	public static function getUsersForNotifications()
	{
		return (new \App\Db\Query())->from('a_#__bruteforce_users')->createCommand()->queryColumn();
	}

	/**
	 * Function sends notifications
	 */
	public function sendNotificationEmail()
	{
		\App\Log::trace('Start ' . __CLASS__ . '::' . __METHOD__);
		if (!empty($this->get('sent'))) {
			$usersId = self::getUsersForNotifications();
			if (count($usersId) === 0) {
				\App\Log::trace('End ' . __CLASS__ . '::' . __METHOD__ . ' - No brute force users found to send email');
				return false;
			}
			$emails = [];
			foreach ($usersId as $id) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Users');
				$emails[] = $recordModel->get('email1');
			}
			$data = [
				'sysname' => 'BruteForceSecurityRiskHasBeenDetected',
				'to_email' => implode(',', $emails),
				'module' => 'Contacts',
			];
			$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
			$mailStatus = $recordModel->sendMailFromTemplate($data);

			if ($mailStatus !== 1) {
				\App\Log::error('Do not sent mail with information about brute force attack');
			}
		}
		\App\Log::trace('End ' . __CLASS__ . '::' . __METHOD__);
	}
}
