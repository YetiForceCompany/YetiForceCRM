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

class OSSMailScanner_Record_Model extends Vtiger_Record_Model
{

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

	public static function getIdentities($id)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM roundcube_identities WHERE user_id = ?";
		$result = $db->pquery($sql, array($id), true);
		$output = [];
		$newRowCount = $db->getRowCount($result);
		for ($i = 0; $i < $newRowCount; $i++) {
			$output[$i]['name'] = $db->query_result($result, $i, 'name');
			$output[$i]['email'] = $db->query_result($result, $i, 'email');
			$output[$i]['identity_id'] = $db->query_result($result, $i, 'identity_id');
		}
		return $output;
	}

	public function deleteIdentities($id)
	{
		$db = PearDatabase::getInstance();
		$sql = "DELETE FROM roundcube_identities WHERE identity_id = ?";
		$result = $db->pquery($sql, array($id), true);
	}

	public static function getEmailActionsListName($data)
	{
		$return = [];
		foreach ($data as $row) {
			if ($row[0] == 'files') {
				$return[] = array($row[1], $row[1]);
			} else {
				foreach ($row[2] as $row_dir) {
					$return[] = array($row_dir[1], $row[1] . '|' . $row_dir[1]);
				}
			}
		}
		return $return;
	}

	public static function setActions($userid, $vale)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("UPDATE roundcube_users SET actions = ? WHERE user_id = ?", array($vale, $userid), true);
	}

	public static function setFolderList($user, $foldersByType)
	{
		$db = PearDatabase::getInstance();
		$types = ['Received', 'Sent', 'Spam', 'Trash', 'All'];
		$result = $db->pquery('SELECT * FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ?', [$user]);
		$oldFoldersByType = [];
		while ($row = $db->getRow($result)) {
			$oldFoldersByType[$row['type']][] = $row['folder'];
		}
		foreach ($types as $type) {
			$toRemove = $toAdd = $oldFolders = $folders = [];
			if (isset($oldFoldersByType[$type])) {
				$oldFolders = $oldFoldersByType[$type];
			}
			if (isset($foldersByType[$type])) {
				$folders = $foldersByType[$type];
			}

			$toAdd = array_diff_assoc($folders, $oldFolders);
			$toRemove = array_diff_assoc($oldFolders, $folders);
			foreach ($toAdd as $folder) {
				$db->insert('vtiger_ossmailscanner_folders_uid', [
					'user_id' => $user,
					'type' => $type,
					'folder' => html_entity_decode($folder)
				]);
			}
			foreach ($toRemove as $folder) {
				$db->delete('vtiger_ossmailscanner_folders_uid', 'user_id = ? && type = ? && folder = ?', [$user, $type, $folder]);
			}
		}
	}

	public static function getConfigFolderList($folder = false)
	{
		$adb = PearDatabase::getInstance();
		if ($folder) {
			$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'folders' && value LIKE '%$folder%' ORDER BY parameter");
			$return = $adb->query_result($result, 0, 'parameter');
		} else {
			$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'folders' ORDER BY parameter DESC");
			while ($row = $adb->fetch_array($result)) {
				$return[$row['parameter']] = $row['value'];
			}
		}
		return $return;
	}

	public static function getConfig($conf_type)
	{
		$adb = PearDatabase::getInstance();
		$queryParams = [];
		if ($conf_type != '' || $conf_type != false) {
			$sql = 'WHERE conf_type = ?';
			$queryParams[] = $conf_type;
		}
		$result = $adb->pquery("SELECT * FROM vtiger_ossmailscanner_config $sql ORDER BY parameter DESC", $queryParams);
		while ($row = $adb->fetch_array($result)) {
			if ($conf_type != '' || $conf_type != false) {
				$return[$row['parameter']] = $row['value'];
			} else {
				$return[$row['conf_type']][$row['parameter']] = $row['value'];
			}
		}
		return $return;
	}

	public function setConfigWidget($confType, $type, $value)
	{
		if ($value === null || $value == 'null') {
			$value = null;
		}
		App\Db::getInstance()->createCommand()->update('vtiger_ossmailscanner_config', ['value' => $value], ['conf_type' => $confType, 'parameter' => $type])->execute();
		return App\Language::translate('LBL_SAVE', 'OSSMailScanner');
	}

	public static function getTypeFolder($folder)
	{
		switch ($folder) {
			case 'Received': $return = 0;
				break;
			case 'Sent': $return = 1;
				break;
			case 'Spam': $return = 2;
				break;
			case 'Trash': $return = 3;
				break;
			case 'All': $return = 4;
				break;
		}
		return $return;
	}

	public static function getUidFolder($accountID, $folder)
	{
		$db = PearDatabase::getInstance();
		$uid = 0;
		$result = $db->pquery('SELECT uid FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ? && BINARY folder = ?', [$accountID, $folder]);
		while ($value = $db->getSingleValue($result)) {
			$uid = $value;
		}
		return $uid;
	}

	public function getFolders($accountID)
	{
		$db = PearDatabase::getInstance();
		$rows = [];
		$result = $db->pquery('SELECT * FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ?', [$accountID]);
		while ($row = $db->getRow($result)) {
			$rows[] = $row;
		}
		return $rows;
	}

	public static function executeActions($account, $mail, $folder, $params = false)
	{

		\App\Log::trace('Start execute actions: ' . $account['username']);

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
				\App\Log::trace('Start action: ' . $action);

				$mail->addActionResult($action, $handler->process($mail));

				\App\Log::trace('End action');
			}
		}
		\App\Log::trace('End execute actions');
		return $mail->getActionResult();
	}

	public function manualScanMail($params)
	{
		$account = OSSMail_Record_Model::getAccountByHash($params['rcId']);
		if (!$account) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$params['folder'] = urldecode($params['folder']);
		$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$mbox = $mailModel->imapConnect($account['username'], $account['password'], $account['mail_host'], $params['folder']);
		$mail = $mailModel->getMail($mbox, $params['uid']);
		if (!$mail) {
			return [];
		}
		if (empty($account['actions'])) {
			$params['actions'] = ['CreatedEmail', 'BindAccounts', 'BindContacts', 'BindLeads'];
		}
		$return = self::executeActions($account, $mail, $params['folder'], $params);
		unset($mail);
		return $return;
	}

	public static function mail_Scan($mbox, $account, $folder, $scan_id, $countEmails)
	{
		$last_user_uid = self::getUidFolder($account['user_id'], $folder);
		$msgno = imap_msgno($mbox, $last_user_uid);
		$num_msg = imap_num_msg($mbox);
		$get_emails = false;
		if ($msgno == 0 && $num_msg != 0) {
			$last_email_uid = imap_uid($mbox, $num_msg);
			if ($last_user_uid == 1) {
				$get_emails = true;
				$msgno = 1;
			} elseif ($last_email_uid > $last_user_uid) {
				$exit = true;
				while ($exit) {
					$last_user_uid++;
					$last_scaned_num = imap_msgno($mbox, $last_user_uid);
					if ($last_scaned_num != 0) {
						$exit = false;
						$msgno = $last_scaned_num;
					} elseif ($last_user_uid == $last_email_uid) {
						$exit = false;
						$msgno = $num_msg;
					}
				}
				$get_emails = true;
			}
		} else if ($msgno < $num_msg) {
			++$msgno;
			$get_emails = true;
		}

		if ($get_emails) {
			for ($i = $msgno; $i <= $num_msg; $i++) {
				$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');

				$uid = imap_uid($mbox, $i);
				$mail = $mailModel->getMail($mbox, $uid, $i);

				self::executeActions($account, $mail, $folder);
				unset($mail);
				$adb = PearDatabase::getInstance();
				$adb->pquery('UPDATE vtiger_ossmailscanner_folders_uid SET uid=? WHERE user_id=? && BINARY folder = ?', [$uid, $account['user_id'], $folder]);
				$countEmails++;
				self::updateScanHistory($scan_id, ['status' => '1', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
				if ($countEmails >= AppConfig::performance('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
					return $countEmails;
				}
			}
		}

		return $countEmails;
	}

	public static function getEmailSearch($module = false)
	{
		$db = PearDatabase::getInstance();
		$return = [];
		$queryParams = ['Users'];
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
				'key' => $row['tablename'] . '=' . $row['columnname'] . '=' . $row['name'],
				'fieldlabel' => $row['fieldlabel'],
				'tablename' => $row['tablename'],
				'columnname' => $row['columnname'],
				'name' => $row['name'],
				'tabid' => $row['tabid'],
				'fieldname' => $row['fieldname']
			];
		}
		return $return;
	}

	public static function getEmailSearchList()
	{
		$cache = Vtiger_Cache::get('Mail', 'EmailSearchList');
		if ($cache !== false) {
			return $cache;
		}
		$return = [];
		$value = (new \App\Db\Query())->select('value')->from('vtiger_ossmailscanner_config')
			->where(['conf_type' => 'emailsearch', 'parameter' => 'fields'])
			->scalar();
		if (!empty($value)) {
			$return = explode(',', $value);
		}
		Vtiger_Cache::set('Mail', 'EmailSearchList', $return);
		return $return;
	}

	public static function setEmailSearchList($value)
	{
		$db = App\Db::getInstance();
		if ($value === null || $value == 'null') {
			$db->createCommand()
				->update('vtiger_ossmailscanner_config', ['value' => ''], ['conf_type' => 'emailsearch', 'parameter' => 'fields'])
				->execute();
		} else {
			$isExists = (new App\Db\Query())
				->from('vtiger_ossmailscanner_config')
				->where(['conf_type' => 'emailsearch', 'parameter' => 'fields'])
				->exists();
			if (!$isExists) {
				$db->createCommand()->insert('vtiger_ossmailscanner_config', [
					'conf_type' => 'emailsearch',
					'parameter' => 'fields',
					'value' => $value
				])->execute();
			} else {
				$db->createCommand()
					->update('vtiger_ossmailscanner_config', ['value' => $value], ['conf_type' => 'emailsearch', 'parameter' => 'fields'])
					->execute();
			}
		}
	}

	public static function _merge_array($tab1, $tab2)
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

	public static function get_cron()
	{
		$adb = PearDatabase::getInstance();
		$return = false;
		$result = $adb->pquery("SELECT * FROM vtiger_cron_task WHERE module = ?", array('OSSMailScanner'));
		for ($i = 0; $i < $adb->getRowCount($result); $i++) {
			$rowData = $adb->query_result_rowdata($result, $i);
			$return[] = Array('name' => $rowData['name'], 'status' => $rowData['status'], 'frequency' => $rowData['frequency']);
		}
		return $return;
	}

	public function executeCron($who_trigger)
	{

		\App\Log::trace('Start executeCron');
		$row = self::getActiveScan();
		if ($row > 0) {
			\App\Log::info(vtranslate('ERROR_ACTIVE_CRON', 'OSSMailScanner'));
			return vtranslate('ERROR_ACTIVE_CRON', 'OSSMailScanner');
		}
		$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$countEmails = 0;
		$scanId = 0;
		$accounts = OSSMail_Record_Model::getAccountsList();
		if (!$accounts) {
			\App\Log::info('There are no accounts to be scanned');
			return false;
		}
		self::setCronStatus('2');
		$scanId = $scannerModel->add_scan_history(['user' => $who_trigger]);
		foreach ($accounts as $account) {
			\App\Log::trace('Start checking account: ' . $account['username']);
			foreach ($scannerModel->getFolders($account['user_id']) as &$folderRow) {
				$folder = $folderRow['folder'];
				\App\Log::trace('Start checking folder: ' . $folder);

				$mbox = $mailModel->imapConnect($account['username'], $account['password'], $account['mail_host'], $folder, false);
				if (is_resource($mbox)) {
					$countEmails = $scannerModel->mail_Scan($mbox, $account, $folder, $scanId, $countEmails);
					imap_close($mbox);
					if ($countEmails >= AppConfig::performance('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
						\App\Log::info('Reached the maximum number of scanned mails');
						self::updateScanHistory($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
						self::setCronStatus('1');
						return 'ok';
					}
				} else {
					\App\Log::error("Incorrect mail access data, username: {$account['username']} , folder: $folder , Error: " . imap_last_error());
				}
			}
		}
		self::updateScanHistory($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
		self::setCronStatus('1');
		\App\Log::trace('End executeCron');
		return 'ok';
	}

	public function get_cron_history()
	{

		return '';
	}

	public function getHistoryStatus($id)
	{
		switch ($id) {
			case 0: $return = 'OK';
				break;
			case 1: $return = 'In progress';
				break;
			case 2: $return = 'Manually stopped';
				break;
		}
		return $return;
	}

	public function get_scan_history($startNumber = 0)
	{
		$limit = 30;
		$endNumber = $startNumber + $limit;
		$dataReader = (new App\Db\Query())->from('vtiger_ossmails_logs')
				->orderBy(['id' => SORT_DESC])
				->limit($endNumber)
				->offset($startNumber)
				->createCommand()->query();
		$output = [];
		while ($row = $dataReader->read()) {
			$startTime = new DateTimeField($row['start_time']);
			$endTime = new DateTimeField($row['end_time']);
			$output [] = [
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
		return $output;
	}

	public function add_scan_history($array)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("INSERT INTO vtiger_ossmails_logs (start_time,status,user) VALUES (?,1,?)", array(date('Y-m-d H:i:s'), $array['user']));
		return $adb->getLastInsertID();
	}

	public static function updateScanHistory($id, $array)
	{
		$adb = PearDatabase::getInstance();
		$sql = "update vtiger_ossmails_logs set end_time=?,status=? ,count=? ,action=? where id=?";
		$dane = array(date('Y-m-d H:i:s'), $array['status'], $array['count'], $array['action'], $id);
		$adb->pquery($sql, $dane);
	}

	public function checkLogStatus()
	{
		$adb = PearDatabase::getInstance();
		$return = false;
		$result = $adb->pquery("SELECT * FROM vtiger_ossmails_logs ORDER BY id DESC", []);
		if ($adb->getRowCount($result) > 0) {
			$row = $adb->query_result_rowdata($result, 0);
			if ($row['status'] == 1) {
				$config = self::getConfig('cron');
				$time = strtotime($row['start_time']) + ( $config['time'] * 60);
				if (strtotime("now") > $time) {
					$return = $row['start_time'];
				}
			}
		}
		return $return;
	}

	public function getActiveScan()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_ossmails_logs WHERE status = '1'", array(''));
		return $adb->getRowCount($result);
	}

	/**
	 * Cron data
	 * @return bool|array
	 */
	public function getCronStatus()
	{
		$return = (new \App\Db\Query())
			->from('vtiger_cron_task')
			->where(['status' => 2, 'name' => 'LBL_MAIL_SCANNER_ACTION'])
			->one();
		return $return ? $return : false;
	}

	/**
	 * Set cron status
	 * @param int $status
	 */
	public function setCronStatus($status)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_cron_task', ['status' => (int) $status], ['name' => 'LBL_MAIL_SCANNER_ACTION'])->execute();
	}

	public function checkCronStatus()
	{
		$return = false;
		$row = self::getCronStatus();
		if ($row) {
			$config = self::getConfig('cron');
			$time = $row['laststart'] + ( $config['time'] * 60);
			if (strtotime("now") > $time) {
				$return = $row['laststart'];
				//return array( date("Y-m-d H:i:s"), date("Y-m-d H:i:s", $time) , $config['time'] );
			}
		}
		return $return;
	}

	public function verificationCron()
	{
		$checkCronStatus = self::checkCronStatus();
		if ($checkCronStatus != false) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery("SELECT * FROM vtiger_ossmailscanner_log_cron WHERE laststart = ?", array($checkCronStatus));
			if ($adb->getRowCount($result) == 0) {
				$adb->pquery("INSERT INTO vtiger_ossmailscanner_log_cron (laststart,status) VALUES (?,0)", array($checkCronStatus));
				$config = self::getConfig('cron');
				$mail_status = \App\Mailer::addMail([
						//'smtp_id' => 1,
						'to' => $config['email'],
						'subject' => App\Language::translate('Email_FromName', 'OSSMailScanner'),
						'content' => App\Language::translate('Email_Body', 'OSSMailScanner'),
				]);
				$adb->pquery("update vtiger_ossmailscanner_log_cron set status = ? WHERE laststart = ?", array($mail_status, $checkCronStatus));
			}
		}
	}

	/**
	 * Restart cron
	 */
	public function runRestartCron()
	{
		$db = App\Db::getInstance();
		$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
		$db->createCommand()->update('vtiger_cron_task', ['status' => 1], ['name' => 'LBL_MAIL_SCANNER_ACTION'])->execute();
		$db->createCommand()->update('vtiger_ossmails_logs', ['status' => 2, 'stop_user' => $userName, 'end_time' => date('Y-m-d H:i:s')], ['status' => 1])->execute();
	}

	protected $user = false;

	public function getUserList()
	{
		if ($this->user) {
			return $this->user;
		}

		$adb = PearDatabase::getInstance();
		$sql = 'SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = ?';
		$result = $adb->pquery($sql, ['Active']);
		$this->user = $adb->getArray($result);
		return $this->user;
	}

	protected $group = false;

	public function getGroupList()
	{
		if ($this->group) {
			return $this->group;
		}
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT groupid as id,groupname FROM vtiger_groups';
		$result = $adb->query($sql);
		$this->group = $adb->getArray($result);
		return $this->group;
	}

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

	public static function AccontDelete($id)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("DELETE FROM roundcube_users WHERE user_id = '$id';", []);
	}
}
