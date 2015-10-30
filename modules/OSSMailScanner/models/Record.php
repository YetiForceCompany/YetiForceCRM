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

	public static function getEmailActionsList()
	{
		$moduleModel = Vtiger_Module_Model::getInstance('OSSMailScanner');
		return self::listFolderFiles($moduleModel->ActionsDirector);
	}

	public function getIdentities($id)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM roundcube_identities WHERE user_id = ?";
		$result = $db->pquery($sql, array($id), true);
		$output = array();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
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

	public function listFolderFiles($dir)
	{
		$moduleModel = Vtiger_Module_Model::getInstance('OSSMailScanner');
		$ffs = scandir($dir);
		foreach ($ffs as $ff) {
			if ($ff != '.' && $ff != '..') {
				$reportURL = str_replace($moduleModel->ActionsDirector, "", $dir);
				if (is_dir($dir . '/' . $ff)) {
					$FolderFiles[] = array('dir', str_replace('.php', "", $ff), self::listFolderFiles($dir . '/' . $ff));
				} else {
					$FolderFiles[] = array('files', str_replace('.php', "", $ff));
				}
			}
		}
		return $FolderFiles;
	}

	public static function getEmailActionsListName($data)
	{
		$return = array();
		foreach ($data as $row) {
			if ($row[0] == 'files') {
				$return[] = array($row[1], $row[1]);
			} else {
				foreach ($row[2] as $row_dir) {
					$return[] = array($row_dir[1], $row[1] . '|' . $row_dir[1]);
				}
			}
		}
		//var_dump($return);
		return $return;
	}

	public static function setActions($userid, $vale)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("UPDATE roundcube_users SET actions = ? WHERE user_id = ?", array($vale, $userid), true);
	}

	public static function setConfigFolderList($type, $vale)
	{
		$adb = PearDatabase::getInstance();
		if ($vale == null || $vale == 'null') {
			$result = $adb->pquery("UPDATE vtiger_ossmailscanner_config SET value = NULL WHERE conf_type = 'folders' AND parameter = ?", array($type), true);
			self::deleteUidFolders($type, $vale);
		} else {
			$result = $adb->pquery("UPDATE vtiger_ossmailscanner_config SET value = '$vale' WHERE conf_type = 'folders' AND parameter = ?", array($type), true);
			self::createUidFolders($type, $vale);
		}
	}

	public static function getConfigFolderList($folder = false)
	{
		$adb = PearDatabase::getInstance();
		if ($folder) {
			$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'folders' AND value LIKE '%$folder%' ORDER BY parameter", true);
			$return = $adb->query_result($result, 0, 'parameter');
		} else {
			$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'folders' ORDER BY parameter DESC", true);
			while ($row = $adb->fetch_array($result)) {
				$return[$row['parameter']] = $row['value'];
			}
		}
		return $return;
	}

	public static function getConfig($conf_type)
	{
		$adb = PearDatabase::getInstance();
		$queryParams = array();
		if ($conf_type != '' || $conf_type != false) {
			$sql = "WHERE conf_type = ?";
			$queryParams[] = $conf_type;
		}
		$result = $adb->pquery("SELECT * FROM vtiger_ossmailscanner_config $sql ORDER BY parameter DESC", $queryParams, true);
		while ($row = $adb->fetch_array($result)) {
			if ($conf_type != '' || $conf_type != false) {
				$return[$row['parameter']] = $row['value'];
			} else {
				$return[$row['conf_type']][$row['parameter']] = $row['value'];
			}
		}
		return $return;
	}

	public function setConfigWidget($conf_type, $type, $vale)
	{
		$adb = PearDatabase::getInstance();
		if ($vale == null || $vale == 'null') {
			$result = $adb->pquery("UPDATE vtiger_ossmailscanner_config SET value = NULL WHERE conf_type = ? AND parameter = ?", [$conf_type, $type]);
		} else {
			$result = $adb->pquery("UPDATE vtiger_ossmailscanner_config SET value = ? WHERE conf_type = ? AND parameter = ?", [$vale, $conf_type, $type]);
		}
		return vtranslate('LBL_SAVE', 'OSSMailScanner');
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

	public static function createUidFolders($type, $vale)
	{
		$adb = PearDatabase::getInstance();
		if ($vale != null && $vale != 'null') {
			if (strpos($vale, ',')) {
				$folders = explode(",", $vale);
			} else {
				$folders[0] = $vale;
			}
			$OSSMailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			foreach ($OSSMailModel->getAccountsList() as $Account) {
				foreach ($folders as $folder) {
					$adb->pquery("INSERT INTO vtiger_ossmailscanner_folders_uid (user_id,type,folder) VALUES (?,?,?)", array($Account['user_id'], $type, $folder));
				}
			}
		}
	}

	public static function checkFolderUid($user_id, $folder)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT uid FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ? AND folder = ?", array($user_id, $folder), true);
		if ($adb->num_rows($result) == 0) {
			$result_type = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'folders' AND value LIKE '%$folder%' ORDER BY parameter", true);
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_folders_uid (user_id,type,folder) VALUES (?,?,?)", array($user_id, $adb->query_result($result_type, 0, 'parameter'), $folder));
		}
	}

	public static function deleteUidFolders($type, $vale)
	{
		$adb = PearDatabase::getInstance();
		$OSSMailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		foreach ($OSSMailModel->getAccountsList() as $Account) {
			$adb->pquery("DELETE FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ? AND type = ? ;", array($Account['user_id'], $type));
		}
	}

	public static function getUidFolder($accountID, $folder)
	{
		$db = PearDatabase::getInstance();
		$uid = 0;
		$result = $db->pquery('SELECT uid FROM vtiger_ossmailscanner_folders_uid WHERE user_id = ? AND folder = ?', [$accountID, $folder]);
		while ($value = $db->getSingleValue($result)) {
			$uid = $value;
		}
		return $uid;
	}

	public static function executeActions($account, $mail_detail, $folder, $params = false)
	{
		$log = vglobal('log');
		$log->debug('Start execute actions: ' . $account['username']);
		global $who_trigger;
		$actions = $return = [];
		if ($params && array_key_exists('actions', $params)) {
			$actions = $params['actions'];
		} elseif (strpos($account['actions'], ',')) {
			$actions = explode(',', $account['actions']);
		} else {
			$actions[] = $account['actions'];
		}
		$self = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$EmailActionsList = $self->getEmailActionsList();
		$EmailActionsListName = $self->getEmailActionsListName($EmailActionsList);
		foreach ($EmailActionsListName as $action) {
			foreach ($actions as $user_action) {
				if ($action[0] == $user_action) {
					$url = str_replace('|', '/', $action[1]);
					$OSSMailScanner_Module_Model = Vtiger_Module_Model::getCleanInstance('OSSMailScanner');
					$action_adress = $OSSMailScanner_Module_Model->ActionsDirector . '/' . $url . '.php';
					if (file_exists($action_adress)) {
						require_once $action_adress;
						$fn_name = '_' . $action[0];
						$log->debug('Start action: ' . $fn_name);
						$return[$user_action] = $fn_name($account['user_id'], $mail_detail, $folder, $return);
						$log->debug('End action: ' . $fn_name);
					}
				}
			}
		}
		$log->debug('End execute actions');
		return $return;
	}

	public static function compare_vale($actions, $item)
	{
		if (strpos($actions, ',')) {
			$actionsTab = explode(",", $actions);
			if (in_array($item, $actionsTab)) {
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = $actions == $item ? true : false;
		}
		return $return;
	}

	function manualScanMail($params)
	{
		$params['folder'] = urldecode($params['folder']);
		$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$account = $mailModel->get_account_detail_by_name($params['username']);
		if ($account === false) {
			return false;
		}

		$mbox = $mailModel->imapConnect($account['username'], $account['password'], $account['mail_host'], $params['folder']);
		$mail_detail = $mailModel->get_mail_detail($mbox, $params['uid']);
		$return = self::executeActions($account, $mail_detail, $params['folder'], $params);
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
			$get_emails = true;
		}

		if ($get_emails) {
			for ($i = $msgno; $i <= $num_msg; $i++) {
				$OSSMailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
				self::checkFolderUid($account['user_id'], $folder);
				$uid = imap_uid($mbox, $i);
				$mail_detail = $OSSMailModel->get_mail_detail($mbox, $uid, $i);
				$mail_detail['Account_username'] = $account['username'];
				$mail_detail['Account_user_id'] = $account['user_id'];
				self::executeActions($account, $mail_detail, $folder);
				$adb = PearDatabase::getInstance();
				$adb->pquery('update vtiger_ossmailscanner_folders_uid set uid=? where user_id=? AND folder = ?', [$uid, $account['user_id'], $folder]);
				$countEmails++;
				self::update_scan_history($scan_id, ['status' => '1', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
				if ($countEmails >= PerformancePrefs::get('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
					return $countEmails;
				}
			}
		}

		return $countEmails;
	}

	public static function getEmailSearch($module = false)
	{
		$adb = PearDatabase::getInstance();
		$return = array();
		$queryParams = array();
		if ($module != false) {
			$ifwhere = "AND vtiger_tab.name = ? ";
			$queryParams[] = $module;
		}
		$result = $adb->pquery("SELECT * FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid  WHERE (uitype = '13' OR uitype = '104') AND vtiger_field.presence <> '1' $ifwhere ORDER BY name", array($queryParams), true);
		while ($row = $adb->fetch_array($result)) {
			array_push($return, array($row['fieldlabel'], $row['tablename'], $row['columnname'], $row['name'], $row['tabid'], $row['fieldname']));
		}
		return $return;
	}

	public static function getEmailSearchList()
	{
		$adb = PearDatabase::getInstance();
		$return = array();
		$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'emailsearch' AND parameter = 'fields'", true);
		while ($row = $adb->fetch_array($result)) {
			$return[$row['parameter']] = $row['value'];
		}
		return $return;
	}

	public static function findEmail($emails, $searchModule = false, $returnArray = true)
	{
		$adb = PearDatabase::getInstance();
		if ($returnArray) {
			$return = [];
		} else {
			$return = '';
		}

		$EmailSearchList = self::getEmailSearchList();
		if (strpos($emails, ',')) {
			$emailsArray = explode(',', $emails);
		} else {
			$emailsArray[0] = $emails;
		}
		if ($EmailSearchList != null && $emails != '' && !empty($EmailSearchList['fields'])) {
			if (strpos($EmailSearchList['fields'], ',')) {
				$fields = explode(',', $EmailSearchList['fields']);
			} else {
				$fields[0] = $EmailSearchList['fields'];
			}
			foreach ($fields as $field) {
				$enableFind = true;
				$row = explode('=', $field);
				if ($searchModule) {
					$moduleId = Vtiger_Functions::getModuleId($searchModule);
					if ($moduleId != $row[2]) {
						$enableFind = false;
					}
				}

				if ($enableFind) {
					$module = Vtiger_Functions::getModuleName($row[2]);
					require_once("modules/$module/$module.php");
					$ModuleObject = new $module();
					$table_index = $ModuleObject->table_index;
					foreach ($emailsArray as $email) {
						$result = $adb->pquery("SELECT $table_index FROM " . $row[0] . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ' . $row[0] . ".$table_index WHERE vtiger_crmentity.deleted = 0 AND " . $row[1] . ' = ? ', [$email]);
						while ($crmid = $adb->getSingleValue($result)) {
							if ($returnArray) {
								$return[] = $crmid;
							} else {
								if ($return != '') {
									$return .= ',';
								}
								$return .= $crmid;
							}
						}
					}
				}
			}
		}
		return $return;
	}

	public static function findEmailUser($emails)
	{
		$adb = PearDatabase::getInstance();
		$return = array();
		$no_find_user_count = 0;
		if (strpos($emails, ',')) {
			$emails_array = explode(",", $emails);
		} else {
			$emails_array[0] = $emails;
		}
		foreach ($emails_array as $email) {
			$result = $adb->pquery("SELECT id FROM vtiger_users WHERE email1 = ?", array($email), true);
			if ($adb->num_rows($result) > 0) {
				array_push($return, array($adb->query_result($result, 0, 0), $email));
			} else {
				$no_find_user_count++;
			}
		}
		return array('users' => $return, 'no_find_user_count' => $no_find_user_count);
	}

	public static function setEmailSearchList($vale)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->query("SELECT * FROM vtiger_ossmailscanner_config WHERE conf_type = 'emailsearch' AND parameter = 'fields'", true);
		if ($vale == null || $vale == 'null') {
			$adb->query("UPDATE vtiger_ossmailscanner_config SET value = NULL WHERE conf_type = 'emailsearch' AND parameter = 'fields'", true);
		} else {
			if ($adb->num_rows($result) == 0) {
				$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter,value) VALUES (?,?,?)", array('emailsearch', 'fields', $vale));
			} else {
				$adb->pquery("UPDATE vtiger_ossmailscanner_config SET value = ? WHERE conf_type = 'emailsearch' AND parameter = 'fields'", array($vale), true);
			}
		}
	}

	public static function findEmailNumPrefix($ModuleName, $subject)
	{
		$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($ModuleName);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		$redex = '/' . $moduleData['prefix'] . '([0-9]*)/';
		preg_match($redex, $subject, $match);
		//var_dump($subject,$match);echo '<br/>';
		if ($match[0] != NULL) {
			return $match[0];
		} else {
			return false;
		}
	}

	public static function _merge_array($tab1, $tab2)
	{
		$return = array();
		if (count($tab1) != 0 && count($tab2) != 0) {
			$return = array_unique(array_merge($tab1, $tab2));
		} elseif (count($tab1) != 0) {
			$return = $tab1;
		} elseif (count($tab2) != 0) {
			$return = $tab2;
		}
		return $return;
	}

	public static function getTypeEmail($mail_detail, $return_text = false)
	{
		$fromEmailUser = self::findEmailUser($mail_detail['fromaddress']);
		$toEmailUser = self::findEmailUser($mail_detail['toaddress']);
		$ccEmailUser = self::findEmailUser($mail_detail['ccaddress']);
		$bccEmailUser = self::findEmailUser($mail_detail['bccaddress']);
		$count = $toEmailUser['no_find_user_count'] + $ccEmailUser['no_find_user_count'] + $bccEmailUser['no_find_user_count'];
		$Identities = self::getIdentities($mail_detail['Account_user_id']);
		$type = false;
		foreach ($Identities as $Identitie) {
			if ($Identitie['email'] == $mail_detail['fromaddress']) {
				$type = true;
			}
		}
		if ($fromEmailUser['no_find_user_count'] == 0 && $count == 0) {
			$return = 2; // wew
			$return_txt = 'Internal'; // wew
		} elseif ($type) {
			$return = 0; // Sent
			$return_txt = 'Sent'; // Sent
		} else {
			$return = 1; // Received
			$return_txt = 'Received'; // Received
		}
		if ($return_text) {
			return $return_txt;
		} else {
			return $return;
		}
	}

	public static function get_cron()
	{
		$adb = PearDatabase::getInstance();
		$return = false;
		$result = $adb->pquery("SELECT * FROM vtiger_cron_task WHERE module = ?", array('OSSMailScanner'));
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$rowData = $adb->query_result_rowdata($result, $i);
			$return[] = Array('name' => $rowData['name'], 'status' => $rowData['status'], 'frequency' => $rowData['frequency']);
		}
		return $return;
	}

	public function executeCron($who_trigger)
	{
		$log = vglobal('log');
		$log->debug('Start executeCron');
		$row = self::getActiveScan();
		if ($row > 0) {
			$log->warn(vtranslate('ERROR_ACTIVE_CRON', 'OSSMailScanner'));
			return vtranslate('ERROR_ACTIVE_CRON', 'OSSMailScanner');
		}
		$OSSMailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
		$OSSMailScannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$countEmails = 0;
		$scanId = 0;
		$accounts = $OSSMailModel->getAccountsList();
		if (!$accounts) {
			$log->warn('There are no accounts to be scanned');
			return false;
		}
		self::setCronStatus('2');
		$scanId = $OSSMailScannerModel->add_scan_history(Array('user' => $who_trigger));
		foreach ($OSSMailModel->getAccountsList() as $account) {
			$log->debug('Start checking account: ' . $account['username']);
			foreach ($OSSMailScannerModel->getConfigFolderList() as $key => $folders) {
				if ($folders != null) {
					$folderArray = Array();
					if (strpos($folders, ',')) {
						$folderArray = explode(",", $folders);
					} else {
						$folderArray[0] = $folders;
					}
					foreach ($folderArray as $folder) {
						$log->debug('Start checking folder: ' . $folder);
						$mbox = $OSSMailModel->imapConnect($account['username'], $account['password'], $account['mail_host'], $folder, false);
						if (!$mbox) {
							$log->fatal('Incorrect mail access data: ' . $account['username']);
							continue;
						}
						$countEmails = $OSSMailScannerModel->mail_Scan($mbox, $account, $folder, $scanId, $countEmails);
						imap_close($mbox);
						if ($countEmails >= PerformancePrefs::get('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING')) {
							$log->warn('Reached the maximum number of scanned mails');
							$OSSMailScannerModel->update_scan_history($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
							self::setCronStatus('1');
							return 'ok';
						}
					}
				}
			}
		}
		$OSSMailScannerModel->update_scan_history($scanId, ['status' => '0', 'count' => $countEmails, 'action' => 'Action_CronMailScanner']);
		self::setCronStatus('1');
		$log->debug('End executeCron');
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
		$adb = PearDatabase::getInstance();
		$limit = 30;
		$endNumber = $startNumber + $limit;
		$result = $adb->query("SELECT * FROM vtiger_ossmails_logs ORDER BY id DESC LIMIT $startNumber , $endNumber");
		$output = array();
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$output[$i]['id'] = $adb->query_result($result, $i, 'id');
			$output[$i]['start_time'] = DateTimeField::convertToUserFormat($adb->query_result($result, $i, 'start_time'));
			$output[$i]['end_time'] = DateTimeField::convertToUserFormat($adb->query_result($result, $i, 'end_time'));
			$output[$i]['status'] = self::getHistoryStatus($adb->query_result($result, $i, 'status'));
			$output[$i]['user'] = $adb->query_result($result, $i, 'user');
			$output[$i]['stop_user'] = $adb->query_result($result, $i, 'stop_user');
			//$output[$i]['folder'] = $adb->query_result($result, $i, 'folder'); 
			///$output[$i]['action'] = $adb->query_result($result, $i, 'action'); 
			$output[$i]['count'] = $adb->query_result($result, $i, 'count');
			$output[$i]['action'] = vtranslate($adb->query_result($result, $i, 'action'), 'OSSMailScanner');
			$output[$i]['info'] = $adb->query_result($result, $i, 'info');
		}
		return $output;
	}

	public function add_scan_history($array)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("INSERT INTO vtiger_ossmails_logs (start_time,status,user) VALUES (?,1,?)", array(date('Y-m-d H:i:s'), $array['user']));
		return $adb->getLastInsertID();
	}

	public function update_scan_history($id, $array)
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
		$result = $adb->pquery("SELECT * FROM vtiger_ossmails_logs ORDER BY id DESC", array());
		if ($adb->num_rows($result) > 0) {
			$row = $adb->query_result_rowdata($result, 0);
			if ($row['status'] == 1) {
				$config = self::getConfig('cron');
				$time = strtotime($row['start_time']) + ( $config['time'] * 60);
				//var_dump(strtotime("now") > $time, strtotime("now"), $time, date('Y-m-d H:i:s'));
				if (strtotime("now") > $time) {
					$return = $row['start_time'];
					//return array( date("Y-m-d H:i:s"), date("Y-m-d H:i:s", $time) , $config['time'] );
				}
			}
		}
		return $return;
	}

	public function getActiveScan()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_ossmails_logs WHERE status = '1'", array(''));
		return $adb->num_rows($result);
	}

	public function getCronStatus()
	{
		$adb = PearDatabase::getInstance();
		$return = false;
		$result = $adb->pquery("SELECT * FROM vtiger_cron_task WHERE name = ? AND status = '2'", array('MailScannerAction'));
		if ($adb->num_rows($result) > 0) {
			$return = $adb->query_result_rowdata($result, 0);
		}
		return $return;
	}

	public function setCronStatus($status)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_cron_task SET status = ? WHERE name = ?", array($status, 'MailScannerAction'));
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
			if ($adb->num_rows($result) == 0) {
				$adb->pquery("INSERT INTO vtiger_ossmailscanner_log_cron (laststart,status) VALUES (?,0)", array($checkCronStatus));
				$SUPPORT_NAME = vglobal('HELPDESK_SUPPORT_NAME');
				$config = self::getConfig('cron');
				$mail_status = send_mail('Support', $config['email'], vtranslate('Email_FromName', 'OSSMailScanner'), $SUPPORT_NAME, vtranslate('Email_Subject', 'OSSMailScanner'), vtranslate('Email_Body', 'OSSMailScanner'), '', '', '', 1);
				$adb->pquery("update vtiger_ossmailscanner_log_cron set status = ? WHERE laststart = ?", array($mail_status, $checkCronStatus));
			}
		}
	}

	public function runRestartCron()
	{
		$adb = PearDatabase::getInstance();
		$user_name = Users_Record_Model::getCurrentUserModel()->user_name;
		$adb->pquery("update vtiger_cron_task set status = 1 WHERE name = ?", array('MailScannerAction'));
		$adb->pquery("update vtiger_ossmails_logs set status = 2,stop_user = ? ,end_time = ? WHERE status = 1", array($user_name, date("Y-m-d H:i:s")));
	}

	public static function getUserList()
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT id,user_name,first_name,last_name FROM vtiger_users WHERE status = ?';
		$result = $adb->pquery($sql, array('Active'));
		return $adb->getArray($result);
	}

	public static function getGroupList()
	{
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT groupid as id,groupname FROM vtiger_groups';
		$result = $adb->pquery($sql, array());
		return $adb->getArray($result);
	}

	public function BindRecords()
	{
		$adb = PearDatabase::getInstance();
		$actions = array('2_bind_Accounts', '3_bind_Contacts', '4_bind_Leads', '5_bind_HelpDesk', '6_bind_Potentials', '7_bind_Project', '8_bind_ServiceContracts', '9_bind_Campaigns',);
		$result = $adb->pquery("SELECT * FROM vtiger_ossmailview WHERE verify = '1' ", array());
		$num_rows = $adb->num_rows($result);
		if ($num_rows == 0) {
			return false;
		}
		$return = array();
		$OSSMailScannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$scan_id = $OSSMailScannerModel->add_scan_history(Array('user' => PHP_SAPI));
		for ($i = 0; $i < $num_rows; $i++) {
			$mail_detail['ossmailviewid'] = $adb->query_result($result, $i, 'ossmailviewid');
			$mail_detail['message_id'] = $adb->query_result($result, $i, 'uid');
			$mail_detail['fromaddress'] = $adb->query_result($result, $i, 'from_email');
			$mail_detail['toaddress'] = $adb->query_result($result, $i, 'to_email');
			$mail_detail['ccaddress'] = $adb->query_result($result, $i, 'cc_email');
			$mail_detail['bccaddress'] = $adb->query_result($result, $i, 'bcc_email');
			$mail_detail['subject'] = $adb->query_result($result, $i, 'subject');
			$mail_detail['folder'] = $adb->query_result($result, $i, 'mbox');
			foreach ($actions as $user_action) {
				$OSSMailScanner_Module_Model = Vtiger_Module_Model::getCleanInstance('OSSMailScanner');
				$action_adress = $OSSMailScanner_Module_Model->ActionsDirector . '/' . $user_action . '.php';
				if (file_exists($action_adress)) {
					require_once $action_adress;
					$fn_name = '_' . $user_action;
					$return[$mail_detail['ossmailviewid']][$user_action] = $fn_name('', $mail_detail, $mail_detail['folder'], $return[$mail_detail['ossmailviewid']]);
				}
			}
			$adb->pquery("update vtiger_ossmailview set verify = '0' WHERE ossmailviewid = ?", array($mail_detail['ossmailviewid']));
			self::update_scan_history($scan_id, Array('status' => '1', 'count' => $i, 'action' => 'Action_CronBind'));
		}
		$OSSMailScannerModel->update_scan_history($scan_id, Array('status' => '0', 'count' => $num_rows, 'action' => 'Action_CronBind'));
		return $return;
	}

	public static function AccontDelete($id)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("DELETE FROM roundcube_users WHERE user_id = '$id';", array());
	}
}
