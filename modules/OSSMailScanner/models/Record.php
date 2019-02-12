<?php

/**
 * OSSMailScanner Record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Main folders array.
	 *
	 * @var array
	 */
	public static $mainFolders = ['Received', 'Sent', 'Spam', 'Trash', 'All'];

	/**
	 * Returns array list of actions.
	 *
	 * @return array
	 */
	public static function getActionsList()
	{
		$accountsPriority = ['CreatedEmail', 'CreatedHelpDesk', 'BindAccounts', 'BindContacts', 'BindLeads', 'BindHelpDesk', 'BindSSalesProcesses'];
		$moduleModel = Vtiger_Module_Model::getInstance('OSSMailScanner');
		$iterator = new DirectoryIterator($moduleModel->actionsDir);
		$actions = [];
		foreach ($iterator as $i => $fileInfo) {
			if (!$fileInfo->isDot()) {
				$action = $fileInfo->getFilename();
				$action = rtrim($action, '.php');
				$key = array_search($action, $accountsPriority);
				if ($key === false) {
					$key = $i + 100;
				}
				$actions[$key] = $action;
			}
		}
		ksort($actions);

		return $actions;
	}

	/**
	 * Return user identities.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getIdentities($id)
	{
		return (new \App\Db\Query())->select(['name', 'email', 'identity_id'])->from('roundcube_identities')->where(['user_id' => $id])->all();
	}

	/**
	 * Delete identity by id.
	 *
	 * @param int $id
	 */
	public function deleteIdentities($id)
	{
		\App\Db::getInstance()->createCommand()->delete('roundcube_identities', ['identity_id' => $id])->execute();
	}

	/**
	 * Return email  actions name list.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function getEmailActionsListName($data)
	{
		$return = [];
		foreach ($data as $row) {
			if ($row[0] == 'files') {
				$return[] = [$row[1], $row[1]];
			} else {
				foreach ($row[2] as $row_dir) {
					$return[] = [$row_dir[1], $row[1] . '|' . $row_dir[1]];
				}
			}
		}
		return $return;
	}

	/**
	 * Update user actions.
	 *
	 * @param int    $userid
	 * @param string $value
	 */
	public static function setActions($userid, $value)
	{
		\App\Db::getInstance()->createCommand()
			->update('roundcube_users', [
				'actions' => $value,
			], ['user_id' => $userid])
			->execute();
	}

	/**
	 * Update folder list for user.
	 *
	 * @param int   $user
	 * @param array $foldersByType
	 */
	public static function setFolderList($user, $foldersByType)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$oldFoldersByType = (new \App\Db\Query())->select(['type', 'folder'])->from('vtiger_ossmailscanner_folders_uid')->where(['user_id' => $user])->createCommand()->queryAllByGroup(2);
		foreach (self::$mainFolders as $type) {
			$toRemove = $toAdd = $oldFolders = $folders = [];
			if (isset($oldFoldersByType[$type])) {
				$oldFolders = $oldFoldersByType[$type];
			}
			if (isset($foldersByType[$type])) {
				$folders = $foldersByType[$type];
			}

			$toAdd = array_diff_assoc($folders, $oldFolders);
			$toRemove = array_diff_assoc($oldFolders, $folders);
			foreach ($toRemove as $folder) {
				$dbCommand->delete('vtiger_ossmailscanner_folders_uid', ['user_id' => $user, 'type' => $type, 'folder' => html_entity_decode($folder)])->execute();
			}
			foreach ($toAdd as $folder) {
				$dbCommand->insert('vtiger_ossmailscanner_folders_uid', [
					'user_id' => $user,
					'type' => $type,
					'folder' => html_entity_decode($folder),
				])->execute();
			}
		}
	}

	/**
	 * Return folders config.
	 *
	 * @param string|bool $folder
	 *
	 * @return string|array
	 */
	public static function getConfigFolderList($folder = false)
	{
		if ($folder) {
			return (new \App\Db\Query())->select(['parameter'])->from('vtiger_ossmailscanner_config')->where(['and', ['conf_type' => 'folders'], ['like', 'value', $folder]])->orderBy('parameter')->scalar();
		} else {
			return (new \App\Db\Query())->select(['parameter', 'value'])->from('vtiger_ossmailscanner_config')->where(['conf_type' => 'folders'])->orderBy(['parameter' => SORT_DESC])->createCommand()->queryAllByGroup(0);
		}
	}

	/**
	 * Return mailscanner config.
	 *
	 * @param string|bool $confType
	 *
	 * @return array
	 */
	public static function getConfig($confType)
	{
		$query = (new \App\Db\Query())->from('vtiger_ossmailscanner_config');
		if ($confType !== false) {
			$query->where(['conf_type' => $confType]);
		}
		$query->orderBy(['parameter' => SORT_DESC]);
		$dataReader = $query->createCommand()->query();
		$return = [];
		while ($row = $dataReader->read()) {
			if ($confType !== false) {
				$return[$row['parameter']] = $row['value'];
			} else {
				$return[$row['conf_type']][$row['parameter']] = $row['value'];
			}
		}
		$dataReader->close();

		return $return;
	}

	/**
	 * Update config widget param.
	 *
	 * @param string $confType
	 * @param string $type
	 * @param string $value
	 *
	 * @return string
	 */
	public function setConfigWidget($confType, $type, $value)
	{
		if ($value === null || $value === 'null') {
			$value = null;
		}
		App\Db::getInstance()->createCommand()->update('vtiger_ossmailscanner_config', ['value' => $value], ['conf_type' => $confType, 'parameter' => $type])->execute();

		return App\Language::translate('LBL_SAVE', 'OSSMailScanner');
	}

	/**
	 * Returns folder type.
	 *
	 * @param string $folder
	 *
	 * @return int
	 */
	public static function getTypeFolder($folder)
	{
		switch ($folder) {
			case 'Received':
				$return = 0;
				break;
			case 'Sent':
				$return = 1;
				break;
			case 'Spam':
				$return = 2;
				break;
			case 'Trash':
				$return = 3;
				break;
			case 'All':
				$return = 4;
				break;
			default:
				break;
		}
		return $return;
	}

	/**
	 * Return folder UID.
	 *
	 * @param int    $accountId
	 * @param string $folder
	 *
	 * @return int
	 */
	public static function getUidFolder($accountId, $folder)
	{
		$rows = (new \App\Db\Query())->select(['uid', 'folder'])->from('vtiger_ossmailscanner_folders_uid')->where(['user_id' => $accountId, 'folder' => $folder])->createCommand()->query();
		while ($row = $rows->read()) {
			if ($folder === $row['folder']) {
				return $row['uid'];
			}
		}
		return 0;
	}

	/**
	 * Return user folders.
	 *
	 * @param int $accountId
	 *
	 * @return array
	 */
	public function getFolders($accountId)
	{
		return (new \App\Db\Query())->from('vtiger_ossmailscanner_folders_uid')->where(['user_id' => $accountId])->createCommand()->queryAll();
	}

	/**
	 * @param int                $account
	 * @param OSSMail_Mail_Model $mail
	 * @param string             $folder
	 * @param array              $params
	 *
	 * @return array
	 */
	public static function executeActions($account, OSSMail_Mail_Model $mail, $folder, $params = false)
	{
		\App\Log::trace('Start execute actions: ' . $account['username'], 'MailScanner');

		$actions = [];
		if ($params && array_key_exists('actions', $params)) {
			$actions = $params['actions'];
		} elseif (is_string($account['actions'])) {
			$actions = explode(',', $account['actions']);
		} else {
			$actions = $account['actions'];
		}
		$mail->setAccount($account);
		$mail->setFolder($folder);
		foreach ($actions as &$action) {
			$handlerClass = Vtiger_Loader::getComponentClassName('ScannerAction', $action, 'OSSMailScanner');
			$handler = new $handlerClass();
			if ($handler) {
				\App\Log::trace('Start action: ' . $action, 'MailScanner');
				try {
					$mail->addActionResult($action, $handler->process($mail));
				} catch (Exception $e) {
					App\Log::error($e->__toString(), 'MailScanner');
				}
				\App\Log::trace('End action', 'MailScanner');
			}
		}
		$mail->postProcess();
		\App\Log::trace('End execute actions', 'MailScanner');

		return $mail->getActionResult();
	}

	/**
	 * Manually scan mail.
	 *
	 * @param int    $uid
	 * @param string $folder
	 * @param array  $account
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function manualScanMail(int $uid, string $folder, array $account)
	{
		$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$imapFolder = \App\Utils::convertCharacterEncoding($folder, 'UTF-8', 'UTF7-IMAP');
		$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], $imapFolder);
		$mail = $mailModel->getMail($mbox, $uid);
		if (!$mail) {
			return [];
		}
		$params = [];
		if (empty($account['actions'])) {
			$params['actions'] = ['CreatedEmail', 'BindAccounts', 'BindContacts', 'BindLeads'];
		}
		$return = self::executeActions($account, $mail, $folder, $params);
		unset($mail);

		return $return;
	}

	/**
	 * Scan mailbox for emails.
	 *
	 * @param resource $mbox
	 * @param array    $account
	 * @param string   $folder      Character encoding UTF-8
	 * @param int      $scan_id
	 * @param int      $countEmails
	 *
	 * @throws \ReflectionException
	 * @throws \yii\db\Exception
	 *
	 * @return mixed
	 */
	public static function mailScan($mbox, array $account, string $folder, int $scan_id, int $countEmails)
	{
		$lastScanUid = self::getUidFolder($account['user_id'], $folder);
		$msgno = imap_msgno($mbox, $lastScanUid);
		$numMsg = imap_num_msg($mbox);
		$getEmails = false;
		if ($msgno === 0 && $numMsg !== 0) {
			if ($lastScanUid === 0) {
				$msgno = 1;
				$getEmails = true;
			} elseif (imap_uid($mbox, $numMsg) > $lastScanUid) {
				foreach (imap_search($mbox, 'ALL', SE_UID) as $uid) {
					if ($uid > $lastScanUid) {
						$msgno = imap_msgno($mbox, $uid);
						$getEmails = true;
						break;
					}
				}
			}
		} elseif ($msgno < $numMsg) {
			++$msgno;
			$getEmails = true;
		}
		if ($getEmails) {
			$dbCommand = \App\Db::getInstance()->createCommand();
			for ($i = $msgno; $i <= $numMsg; ++$i) {
				$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
				$uid = imap_uid($mbox, $i);
				$mail = $mailModel->getMail($mbox, $uid, $i);

				self::executeActions($account, $mail, $folder);
				unset($mail);
				$dbCommand->update('vtiger_ossmailscanner_folders_uid', ['uid' => $uid], ['user_id' => $account['user_id'], 'folder' => $folder])->execute();
				++$countEmails;
				self::updateScanHistory($scan_id, ['status' => '1', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
				if ($countEmails >= \App\Config::performance('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
					return $countEmails;
				}
			}
		}
		return $countEmails;
	}

	/**
	 * Return email search results.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function getEmailSearch($module = false)
	{
		$return = [];
		$query = (new App\Db\Query())->from('vtiger_field')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['and', ['or', ['uitype' => 13], ['uitype' => 14]], ['<>', 'vtiger_field.presence', 1], ['<>', 'vtiger_tab.name', 'Users']]);
		if ($module) {
			$query->andWhere(['vtiger_tab.name' => $module]);
		}
		$query->orderBy('name');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$return[] = [
				'key' => $row['fieldname'] . '=' . $row['name'],
				'fieldlabel' => $row['fieldlabel'],
				'tablename' => $row['tablename'],
				'columnname' => $row['columnname'],
				'name' => $row['name'],
				'tabid' => $row['tabid'],
				'fieldname' => $row['fieldname'],
			];
		}
		$dataReader->close();

		return $return;
	}

	/**
	 * Return email search list.
	 *
	 * @return array
	 */
	public static function getEmailSearchList()
	{
		$cache = Vtiger_Cache::get('Mail', 'EmailSearchList');
		if ($cache !== false) {
			return $cache;
		}
		$return = [];
		$value = (new \App\Db\Query())->select(['value'])->from('vtiger_ossmailscanner_config')
			->where(['conf_type' => 'emailsearch', 'parameter' => 'fields'])
			->scalar();
		if (!empty($value)) {
			$return = explode(',', $value);
		}
		Vtiger_Cache::set('Mail', 'EmailSearchList', $return);

		return $return;
	}

	/**
	 * Set email search list.
	 *
	 * @param string $value
	 */
	public static function setEmailSearchList($value)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		if ($value === null || $value == 'null') {
			$dbCommand->update('vtiger_ossmailscanner_config', ['value' => ''], ['conf_type' => 'emailsearch', 'parameter' => 'fields'])->execute();
		} else {
			$isExists = (new App\Db\Query())->from('vtiger_ossmailscanner_config')->where(['conf_type' => 'emailsearch', 'parameter' => 'fields'])->exists();
			if (!$isExists) {
				$dbCommand->insert('vtiger_ossmailscanner_config', [
					'conf_type' => 'emailsearch',
					'parameter' => 'fields',
					'value' => $value,
				])->execute();
			} else {
				$dbCommand->update('vtiger_ossmailscanner_config', ['value' => $value], ['conf_type' => 'emailsearch', 'parameter' => 'fields'])->execute();
			}
		}
	}

	/**
	 * Merge arrays.
	 *
	 * @param array $tab1
	 * @param array $tab2
	 *
	 * @return array
	 */
	public static function mergeArray($tab1, $tab2)
	{
		$return = [];
		if (count($tab1) != 0 && count($tab2) != 0) {
			$return = array_unique(array_merge($tab1, $tab2));
		} elseif (count($tab1) != 0) {
			$return = $tab1;
		} elseif (count($tab2) != 0) {
			$return = $tab2;
		}
		return $return;
	}

	/**
	 * The function returns information about OSSMailScanner Crons.
	 *
	 * @return array
	 */
	public static function getCron()
	{
		return (new App\Db\Query())->select(['name', 'status', 'frequency'])->from('vtiger_cron_task')->where(['module' => 'OSSMailScanner'])->createCommand()->queryAll();
	}

	/**
	 * Execute cron task.
	 *
	 * @param int $whoTrigger
	 *
	 * @return bool|string
	 */
	public function executeCron($whoTrigger)
	{
		\App\Log::trace('Start executeCron');
		$row = $this->getActiveScan();
		if ($row > 0) {
			\App\Log::warning(\App\Language::translate('ERROR_ACTIVE_CRON', 'OSSMailScanner'));
			return \App\Language::translate('ERROR_ACTIVE_CRON', 'OSSMailScanner');
		}
		$accounts = OSSMail_Record_Model::getAccountsList();
		if (!$accounts) {
			\App\Log::info('There are no accounts to be scanned');
			return false;
		}
		$this->setCronStatus(2);
		$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$countEmails = 0;
		$scanId = $scannerModel->addScanHistory(['user' => $whoTrigger]);
		foreach ($accounts as $account) {
			\App\Log::trace('Start checking account: ' . $account['username']);
			if (!$this->isConnection($account)) {
				continue;
			}
			foreach ($scannerModel->getFolders($account['user_id']) as $folderRow) {
				$folder = \App\Utils::convertCharacterEncoding($folderRow['folder'], 'UTF-8', 'UTF7-IMAP');
				\App\Log::trace('Start checking folder: ' . $folder);

				$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], $folder, false);
				if (is_resource($mbox)) {
					$countEmails = $scannerModel->mailScan($mbox, $account, $folderRow['folder'], $scanId, $countEmails);
					imap_close($mbox);
					if ($countEmails >= AppConfig::performance('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
						\App\Log::info('Reached the maximum number of scanned mails');
						self::updateScanHistory($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
						$this->setCronStatus(1);
						return 'ok';
					}
				} else {
					\App\Log::error("Incorrect mail access data, username: {$account['username']} , folder: $folder , type: {$folderRow['type']} ,  Error: " . imap_last_error());
				}
			}
		}
		self::updateScanHistory($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
		$this->setCronStatus(1);
		\App\Log::trace('End executeCron');

		return 'ok';
	}

	/**
	 * Function checks connection to mailbox.
	 *
	 * @param array $account
	 *
	 * @return bool
	 */
	public function isConnection(array $account)
	{
		$result = false;
		try {
			$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], '');
			if (is_resource($mbox)) {
				imap_close($mbox);
				$result = true;
			}
		} catch (\Throwable $e) {
			$result = false;
		}
		return $result;
	}

	/**
	 * Return history status label.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getHistoryStatus($id)
	{
		switch ($id) {
			case 0:
				$return = 'OK';
				break;
			case 1:
				$return = 'In progress';
				break;
			case 2:
				$return = 'Manually stopped';
				break;
			default:
				break;
		}
		return $return;
	}

	/**
	 * Return scan history.
	 *
	 * @param int $startNumber
	 *
	 * @return array
	 */
	public function getScanHistory($startNumber = 0)
	{
		$limit = 30;
		$endNumber = $startNumber + $limit;
		$dataReader = (new App\Db\Query())->from('vtiger_ossmails_logs')->orderBy(['id' => SORT_DESC])->limit($endNumber)->offset($startNumber)->createCommand()->query();
		$output = [];
		while ($row = $dataReader->read()) {
			$startTime = new DateTimeField($row['start_time']);
			$endTime = new DateTimeField($row['end_time']);
			$output[] = [
				'id' => $row['id'],
				'start_time' => $startTime->getDisplayDateTimeValue(),
				'end_time' => $endTime->getDisplayDateTimeValue(),
				'status' => self::getHistoryStatus($row['status']),
				'user' => $row['user'],
				'stop_user' => $row['stop_user'],
				'count' => $row['count'],
				'action' => $row['count'],
				'info' => $row['info'],
			];
		}
		$dataReader->close();

		return $output;
	}

	/**
	 * Insert new scan history row.
	 *
	 * @param array $array
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function addScanHistory($array): int
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_ossmails_logs', ['status' => 1, 'user' => $array['user'], 'start_time' => date('Y-m-d H:i:s')])->execute();

		return $db->getLastInsertID('vtiger_ossmails_logs_id_seq');
	}

	/**
	 * Update scan history row.
	 *
	 * @param int   $id
	 * @param array $array
	 */
	public static function updateScanHistory($id, $array)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_ossmails_logs', ['end_time' => date('Y-m-d H:i:s'), 'status' => $array['status'], 'count' => $array['count'], 'action' => $array['action']], ['id' => $id])->execute();
	}

	/**
	 * Return log status.
	 *
	 * @return timestamp|bool
	 */
	public function checkLogStatus()
	{
		$return = false;
		$row = (new App\Db\Query())->from('vtiger_ossmails_logs')->orderBy(['id' => SORT_DESC])->one();
		if ($row && (int) $row['status'] === 1) {
			$config = self::getConfig('cron');
			if (!empty($config['time']) && strtotime('now') > strtotime($row['start_time']) + ($config['time'] * 60)) {
				$return = $row['start_time'];
			}
		}
		return $return;
	}

	/**
	 * Return active scan count.
	 *
	 * @return int
	 */
	public function getActiveScan()
	{
		return (new App\Db\Query())->from('vtiger_ossmails_logs')->where(['status' => 1])->createCommand()->query()->count();
	}

	/**
	 * Cron data.
	 *
	 * @return bool|array
	 */
	public static function getCronStatus()
	{
		return (new \App\Db\Query())->from('vtiger_cron_task')->where(['status' => 2, 'name' => 'LBL_MAIL_SCANNER_ACTION'])->one();
	}

	/**
	 * Set cron status.
	 *
	 * @param int $status
	 */
	public function setCronStatus(int $status)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_cron_task', ['status' => $status], ['name' => 'LBL_MAIL_SCANNER_ACTION'])->execute();
	}

	/**
	 * Retun cron status.
	 *
	 * @return timestamp
	 */
	public static function checkCronStatus()
	{
		$return = false;
		$row = self::getCronStatus();
		if ($row) {
			$config = self::getConfig('cron');
			if (!empty($config['time']) && strtotime('now') > $row['laststart'] + ($config['time'] * 60)) {
				$return = $row['laststart'];
			}
		}
		return $return;
	}

	/**
	 * Verification cron.
	 */
	public static function verificationCron()
	{
		$checkCronStatus = self::checkCronStatus();
		if ($checkCronStatus !== false && !(new \App\Db\Query())->from('vtiger_ossmailscanner_log_cron')->where(['laststart' => $checkCronStatus])->createCommand()->query()->count()) {
			$db = App\Db::getInstance();
			$db->createCommand()->insert('vtiger_ossmailscanner_log_cron', ['laststart' => $checkCronStatus, 'status' => 0, 'created_time' => date('Y-m-d H:i:s')])->execute();
			$config = self::getConfig('cron');
			$mailStatus = \App\Mailer::addMail([
				'to' => $config['email'],
				'subject' => App\Language::translate('Email_FromName', 'OSSMailScanner'),
				'content' => App\Language::translate('Email_Body', 'OSSMailScanner'),
			]);
			$db->createCommand()->update('vtiger_ossmailscanner_log_cron', ['status' => $mailStatus], ['laststart' => $checkCronStatus])->execute();
			$db->createCommand()->update('vtiger_ossmails_logs', ['status' => 2, 'stop_user' => 'verificationCron'], ['status' => 1])->execute();
		}
	}

	/**
	 * Restart cron.
	 */
	public static function runRestartCron()
	{
		$db = App\Db::getInstance();
		$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
		$db->createCommand()->update('vtiger_cron_task', ['status' => 1], ['name' => 'LBL_MAIL_SCANNER_ACTION'])->execute();
		$db->createCommand()->update('vtiger_ossmails_logs', ['status' => 2, 'stop_user' => $userName, 'end_time' => date('Y-m-d H:i:s')], ['status' => 1])->execute();
		self::verificationCron();
	}

	/**
	 * Active users list.
	 *
	 * @var array|bool
	 */
	protected $user = false;

	/**
	 * Return active users list.
	 *
	 * @return array
	 */
	public function getUserList()
	{
		if ($this->user) {
			return $this->user;
		}

		$this->user = (new \App\Db\Query())->select(['id', 'user_name', 'first_name', 'last_name'])->from('vtiger_users')->where(['status' => 'Active'])->createCommand()->queryAll();

		return $this->user;
	}

	/**
	 * Groups list.
	 *
	 * @var array
	 */
	protected $group = false;

	/**
	 * Return groups list.
	 *
	 * @return array
	 */
	public function getGroupList()
	{
		if ($this->group) {
			return $this->group;
		}
		return $this->group = (new \App\Db\Query())->select(['groupid', 'groupname'])->from('vtiger_groups')->all();
	}

	/**
	 * Assign data to model.
	 *
	 * @param array $row
	 *
	 * @return bool
	 */
	public function bindMail($row)
	{
		if (empty($row['actions'])) {
			return false;
		}
		$actions = array_diff(explode(',', $row['actions']), ['CreatedEmail', 'CreatedHelpDesk']);
		if (empty($actions)) {
			return false;
		}

		$mail = new OSSMail_Mail_Model();
		$mail->setMailCrmId($row['ossmailviewid']);
		$mail->setFolder($row['mbox']);
		$mail->set('message_id', $row['uid']);
		$mail->set('toaddress', $row['to_email']);
		$mail->set('fromaddress', $row['from_email']);
		$mail->set('reply_to_email', $row['reply_to_email']);
		$mail->set('ccaddress', $row['cc_email']);
		$mail->set('bccaddress', $row['bcc_email']);
		$mail->set('subject', $row['subject']);
		$mail->set('udate_formated', $row['date']);
		$mail->set('body', $row['content']);

		foreach ($actions as $action) {
			$handlerClass = Vtiger_Loader::getComponentClassName('ScannerAction', $action, 'OSSMailScanner');
			$handler = new $handlerClass();
			if ($handler) {
				$handler->process($mail);
			}
		}
		return true;
	}

	/**
	 * Delete user email accounts.
	 *
	 * @param int $id
	 */
	public static function accontDelete($id)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->delete('roundcube_users', ['user_id' => $id])->execute();
		$db->createCommand()->delete('vtiger_ossmailscanner_folders_uid', ['user_id' => $id])->execute();
	}
}
