<?php

/**
 * Brute force model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    YetiForce S.A.
 */
class Settings_BruteForce_Module_Model extends Settings_Vtiger_Module_Model
{
	const UNBLOCKED = 0;
	const BLOCKED = 1;
	const UNBLOCKED_BY_USER = 2;

	private $isBlocked;
	private $blockedId;

	/**
	 * Function includes class instances.
	 *
	 * @return Settings_BruteForce_Module_Model
	 */
	public static function getCleanInstance()
	{
		$instance = new self();
		$instance->setData(self::getBruteForceSettings());

		return $instance;
	}

	/**
	 * Function verifies if module is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return (bool) $this->get('active');
	}

	/**
	 * Function returns module configuration.
	 *
	 * @return array
	 */
	public static function getBruteForceSettings()
	{
		if (App\Cache::has('BruteForce', 'Settings')) {
			return App\Cache::get('BruteForce', 'Settings');
		}
		$row = (new App\Db\Query())->from('a_#__bruteforce')->one();
		App\Cache::save('BruteForce', 'Settings', $row, App\Cache::LONG);

		return $row;
	}

	/**
	 * Function gets unsuccessful login attempts data of blocked users.
	 *
	 * @return array
	 */
	public function getBlockedIp()
	{
		$time = $this->get('timelock');
		$blockDate = new DateTime();
		$blockDate->modify("-$time minutes");

		$query = (new \App\Db\Query())
			->select(['id', 'attempts', 'ip', 'time'])
			->from('a_#__bruteforce_blocked')
			->andWhere(['>=', 'time', $blockDate->format('Y-m-d H:i:s')])
			->andWhere(['blocked' => self::BLOCKED])
			->andWhere(['>=', 'attempts', $this->get('attempsnumber')]);

		return $query->createCommand()->queryAll();
	}

	/**
	 * Functions gets data from login history.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getLoginHistoryData($data)
	{
		$query = (new \App\Db\Query())
			->select(['usersName' => new \yii\db\Expression('DISTINCT user_name'), 'browser' => new \yii\db\Expression('browser')])
			->from('vtiger_loginhistory')
			->where(['status' => ['Failed login', 'Blocked IP', 'ForgotPasswordNoUserFound'], 'user_ip' => $data['ip']])
			->andWhere(['>=', 'login_time', $data['time']]);
		$historyData = $query->createCommand()->queryAllByGroup(2);
		$users = array_keys($historyData);
		$browsers = [];
		foreach ($historyData as $browsersUserName) {
			$browsers = array_merge($browsers, $browsersUserName);
		}
		$data['usersName'] = implode(', ', $users);
		$data['browsers'] = implode(', ', array_unique($browsers));

		return $data;
	}

	/**
	 * Function verifies if user is blocked.
	 *
	 * @return bool
	 */
	public function isBlockedIp()
	{
		if (isset($this->isBlocked)) {
			return $this->isBlocked;
		}
		$time = $this->get('timelock');
		$blockDate = new DateTime();
		$blockDate->modify("-$time minutes");
		$ip = \App\RequestUtil::getRemoteIP(true);
		$this->blockedId = (new \App\Db\Query())
			->select(['id'])
			->from('a_#__bruteforce_blocked')
			->where(['>', 'time', $blockDate->format('Y-m-d H:i:s')])
			->andWhere(['ip' => $ip])
			->andWhere(['blocked' => self::BLOCKED])
			->scalar();

		return $this->isBlocked = (!empty($this->blockedId));
	}

	/**
	 * Function increases the number of unsuccessful login attempts.
	 */
	public function incAttempts()
	{
		if (!empty($this->blockedId)) {
			\App\Db::getInstance('admin')->createCommand()
				->update('a_#__bruteforce_blocked', [
					'attempts' => new \yii\db\Expression('attempts + 1'),
				], ['id' => $this->blockedId])
				->execute();
		}
	}

	/**
	 * Function updates unsuccessful login attempts.
	 */
	public function updateBlockedIp()
	{
		\App\Log::trace('Start ' . __METHOD__);
		$db = \App\Db::getInstance('admin');
		$time = $this->get('timelock');
		$date = new DateTime();
		$checkData = $date->modify("-$time minutes")->format('Y-m-d H:i:s');
		$ip = \App\RequestUtil::getRemoteIP(true);

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
			$this->isBlocked = self::BLOCKED === $blocked;
			$this->blockedId = $bfData['id'];
		}
		$this->clearBlockedByIp($ip, $checkData);
		\App\Log::trace('End ' . __METHOD__);
	}

	/**
	 * Function adds unsuccessful login attempt to database.
	 *
	 * @param string $ip - User IP
	 *
	 * @return int - Created records ID
	 */
	private function setBlockedIp($ip)
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()->insert('a_#__bruteforce_blocked', [
			'ip' => $ip,
			'time' => date('Y-m-d H:i:s'),
			'attempts' => 1,
			'blocked' => self::UNBLOCKED,
		])->execute();
		$this->isBlocked = false;

		return $this->blockedId = $db->getLastInsertID('a_#__bruteforce_blocked_id_seq');
	}

	/**
	 * Function removes redundant entries from database.
	 *
	 * @param string $ip   - User IP
	 * @param string $data - Cut-off date of users block condition
	 */
	private function clearBlockedByIp($ip, $data)
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()->delete('a_#__bruteforce_blocked', [
			'and', ['<', 'time', $data],
			['blocked' => self::UNBLOCKED],
			['ip' => $ip], ])->execute();
	}

	/**
	 * Function unblocks user.
	 *
	 * @param int $id - Record ID
	 */
	public static function unBlock($id)
	{
		return \App\Db::getInstance('admin')->createCommand()
			->update('a_#__bruteforce_blocked', [
				'blocked' => self::UNBLOCKED_BY_USER,
				'userid' => \App\User::getCurrentUserRealId(),
			], ['id' => $id])
			->execute();
	}

	/**
	 * Function returns a list of users who are administrators.
	 *
	 * @return array - List of users
	 */
	public static function getAdminUsers()
	{
		return \App\Fields\Owner::getInstance()->getUsers(false, 'Active', false, false, true);
	}

	/**
	 * Function updates module configuration.
	 *
	 * @param array $data - Configuration data
	 */
	public static function updateConfig($data)
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()
			->update('a_#__bruteforce', [
				'attempsnumber' => $data['attempsnumber'],
				'timelock' => $data['timelock'],
				'active' => $data['active'],
				'sent' => $data['sent'],
			])->execute();
		$db->createCommand()->delete('a_#__bruteforce_users')->execute();
		if (!empty($data['selectedUsers'])) {
			$users = !\is_array($data['selectedUsers']) ? [$data['selectedUsers']] : $data['selectedUsers'];
			foreach ($users as $userId) {
				$db->createCommand()->insert('a_#__bruteforce_users', ['id' => $userId])->execute();
			}
		}
		App\Cache::delete('BruteForce', 'Settings');
	}

	/**
	 * Function returns table of users selected for notifications.
	 *
	 * @return array - ID list of users
	 */
	public static function getUsersForNotifications()
	{
		return (new \App\Db\Query())->from('a_#__bruteforce_users')->createCommand()->queryColumn();
	}

	/**
	 * Function sends notifications.
	 */
	public function sendNotificationEmail()
	{
		\App\Log::trace('Start ' . __METHOD__);
		if (!empty($this->get('sent'))) {
			$usersId = self::getUsersForNotifications();
			if (0 === \count($usersId)) {
				\App\Log::trace('End ' . __METHOD__ . ' - No brute force users found to send email');
				return false;
			}
			$emails = [];
			foreach ($usersId as $id) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, 'Users');
				$emails[] = $recordModel->get('email1');
			}
			$configBruteForce = self::getBruteForceSettings();
			\App\Mailer::sendFromTemplate([
				'template' => 'BruteForceSecurityRiskHasBeenDetected',
				'moduleName' => 'Users',
				'to' => $emails,
				'ip' => \App\RequestUtil::getRemoteIP(true),
				'time' => (new DateTime())->modify("-{$configBruteForce['timelock']} minutes")->format('Y-m-d H:i:s'),
			]);
		}
		\App\Log::trace('End ' . __METHOD__);
	}
}
