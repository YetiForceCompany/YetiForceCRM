<?php

/**
 *
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_Record_Model extends Vtiger_Record_Model
{

	static function getAccountsList($user = false, $onlyMy = false, $password = false)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$param = $users = [];
		$sql = 'SELECT * FROM roundcube_users';
		$where = false;
		if ($password) {
			$where .= " && password <> ''";
		}
		if ($user) {
			$where .= ' && user_id = ?';
			$param[] = $user;
		}
		if ($onlyMy) {
			$where .= ' && crm_user_id = ?';
			$param[] = $currentUserModel->getId();
		}
		if ($where) {
			$sql .= sprintf(' WHERE %s ', substr($where, 4));
		}
		$result = $db->pquery($sql, $param);
		if ($db->getRowCount($result) == 0) {
			return [];
		} else {
			while ($row = $db->getRow($result)) {
				$row['actions'] = empty($row['actions']) ? [] : explode(',', $row['actions']);
				$users[] = $row;
			}
			return $users;
		}
	}

	public static function load_roundcube_config()
	{
		include 'modules/OSSMail/roundcube/config/defaults.inc.php';
		include 'config/modules/OSSMail.php';
		return $config;
	}

	protected static $imapConnectCache = [];

	public static function imapConnect($user, $password, $host = false, $folder = 'INBOX', $dieOnError = true)
	{

		\App\Log::trace("Entering OSSMail_Record_Model::imapConnect($user , $password , $folder) method ...");
		$rcConfig = self::load_roundcube_config();
		$cacheName = $user . $host . $folder;
		if (isset(self::$imapConnectCache[$cacheName])) {
			return self::$imapConnectCache[$cacheName];
		}

		if (!$host) {
			$host = key($rcConfig['default_host']);
		}
		$parseHost = parse_url($host);
		$validatecert = '';
		if ($parseHost['host']) {
			$host = $parseHost['host'];
			$sslMode = (isset($a_host['scheme']) && in_array($parseHost['scheme'], ['ssl', 'imaps', 'tls'])) ? $parseHost['scheme'] : null;
			if (!empty($parseHost['port'])) {
				$port = $parseHost['port'];
			} else if ($sslMode && $sslMode != 'tls' && (!$rcConfig['default_port'] || $rcConfig['default_port'] == 143)) {
				$port = 993;
			}
		} else {
			if ($rcConfig['default_port'] == 993) {
				$sslMode = 'ssl';
			} else {
				$sslMode = 'tls';
			}
		}
		if (empty($port)) {
			$port = $rcConfig['default_port'];
		}
		if (!$rcConfig['validate_cert']) {
			$validatecert = '/novalidate-cert';
		}
		if ($rcConfig['imap_open_add_connection_type']) {
			$sslMode = '/' . $sslMode;
		} else {
			$sslMode = '';
		}

		imap_timeout(IMAP_OPENTIMEOUT, 5);
		\App\Log::trace("imap_open({" . $host . ":" . $port . "/imap" . $sslMode . $validatecert . "}$folder, $user , $password) method ...");
		$mbox = @imap_open("{" . $host . ":" . $port . "/imap" . $sslMode . $validatecert . "}$folder", $user, $password);
		if ($mbox === false && $dieOnError) {
			self::imapThrowError(imap_last_error());
		}
		self::$imapConnectCache[$cacheName] = $mbox;
		\App\Log::trace('Exit OSSMail_Record_Model::imapConnect() method ...');
		return $mbox;
	}

	public static function imapThrowError($error)
	{

		\App\Log::error("Error OSSMail_Record_Model::imapConnect(): " . $error);
		vtlib\Functions::throwNewException(vtranslate('IMAP_ERROR', 'OSSMailScanner') . ': ' . $error);
	}

	public static function updateMailBoxmsgInfo($users)
	{

		\App\Log::trace(__METHOD__ . ' - Start');
		$adb = PearDatabase::getInstance();
		if (count($users) == 0) {
			return false;
		}
		$sUsers = implode(',', $users);
		$result = $adb->pquery("SELECT count(*) AS num FROM yetiforce_mail_quantities WHERE userid IN (?) && status = 1;", [$sUsers]);
		if ($adb->query_result_raw($result, 0, 'num') > 0) {
			return false;
		}
		$adb->update('yetiforce_mail_quantities', ['status' => 1], 'userid IN (?)', [$sUsers]);
		foreach ($users as $user) {
			$account = self::getMailAccountDetail($user);
			if ($account !== false) {
				$result = $adb->pquery("SELECT count(*) AS num FROM yetiforce_mail_quantities WHERE userid = ?;", [$user]);
				$mbox = self::imapConnect($account['username'], $account['password'], $account['mail_host'], 'INBOX', false);
				if ($mbox) {
					$info = imap_mailboxmsginfo($mbox);
					if ($adb->query_result_raw($result, 0, 'num') > 0) {
						$adb->pquery('UPDATE yetiforce_mail_quantities SET `num` = ?,`status` = ? WHERE `userid` = ?;', [$info->Unread, 0, $user]);
					} else {
						$adb->pquery('INSERT INTO yetiforce_mail_quantities (`num`,`userid`) VALUES (?,?);', [$info->Unread, $user]);
					}
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' - End');
		return true;
	}

	public static function getMailBoxmsgInfo($users)
	{

		\App\Log::trace(__METHOD__ . ' - Start');
		$adb = PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM yetiforce_mail_quantities WHERE userid IN (%s);', implode(',', $users));
		$result = $adb->query($query);
		$account = [];
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$account[$adb->query_result_raw($result, $i, 'userid')] = $adb->query_result_raw($result, $i, 'num');
		}
		\App\Log::trace(__METHOD__ . ' - End');
		return $account;
	}

	public static function getMail($mbox, $id, $msgno = false)
	{
		$return = [];
		if (!$msgno) {
			$msgno = imap_msgno($mbox, $id);
		}
		if (!$id) {
			$id = imap_uid($mbox, $msgno);
		}
		if (!$msgno) {
			return false;
		}
		$header = imap_header($mbox, $msgno);
		$structure = self::_get_body_attach($mbox, $id, $msgno);

		$mail = new OSSMail_Mail_Model();
		$mail->set('header', $header);
		$mail->set('id', $id);
		$mail->set('Msgno', $header->Msgno);
		$mail->set('message_id', $header->message_id);
		$mail->set('toaddress', $mail->getEmail('to'));
		$mail->set('fromaddress', $mail->getEmail('from'));
		$mail->set('reply_toaddress', $mail->getEmail('reply_to'));
		$mail->set('ccaddress', $mail->getEmail('cc'));
		$mail->set('bccaddress', $mail->getEmail('bcc'));
		$mail->set('senderaddress', $mail->getEmail('sender'));
		$mail->set('subject', self::_decode_text($header->subject));
		$mail->set('MailDate', $header->MailDate);
		$mail->set('date', $header->date);
		$mail->set('udate', $header->udate);
		$mail->set('udate_formated', date("Y-m-d H:i:s", $header->udate));
		$mail->set('Recent', $header->Recent);
		$mail->set('Unseen', $header->Unseen);
		$mail->set('Flagged', $header->Flagged);
		$mail->set('Answered', $header->Answered);
		$mail->set('Deleted', $header->Deleted);
		$mail->set('Draft', $header->Draft);
		$mail->set('Size', $header->Size);
		$mail->set('body', $structure['body']);
		$mail->set('attachments', $structure['attachment']);

		$clean = '';
		$msgs = imap_fetch_overview($mbox, $msgno);
		foreach ($msgs as $msg) {
			$clean .= imap_fetchheader($mbox, $msg->msgno);
		}
		$mail->set('clean', $clean);
		return $mail;
	}

	protected static $usersCache = [];

	public static function getMailAccountDetail($userid)
	{
		if (isset(self::$usersCache[$userid])) {
			return self::$usersCache[$userid];
		}
		$user = false;
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT * FROM roundcube_users where user_id = ?', [$userid]);
		if ($adb->getRowCount($result)) {
			$user = $adb->getRow($result);
		}
		self::$usersCache[$userid] = $user;
		return $user;
	}

	public static function _decode_text($text)
	{
		$data = imap_mime_header_decode($text);
		$text = '';
		foreach ($data as &$row) {
			$charset = ($row->charset == 'default') ? 'ASCII' : $row->charset;
			if (function_exists('mb_convert_encoding') && in_array($charset, mb_list_encodings())) {
				$text .= mb_convert_encoding($row->text, 'utf-8', $charset);
			} else {
				$text .= iconv($charset, 'UTF-8', $row->text);
			}
		}
		return $text;
	}

	public static function get_full_name($text)
	{
		$return = '';
		foreach ($text as $row) {
			if ($return != '') {
				$return .= ',';
			}
			if ($row->personal == '') {
				$return .= $row->mailbox . '@' . $row->host;
			} else {
				$return .= self::_decode_text($row->personal) . ' - ' . $row->mailbox . '@' . $row->host;
			}
		}
		return $return;
	}

	public static function _get_body_attach($mbox, $id, $msgno)
	{
		$struct = imap_fetchstructure($mbox, $id, FT_UID);
		$i = 0;
		$mail = array('id' => $id);
		if (empty($struct->parts)) {
			$mail = self::initMailPart($mbox, $mail, $struct, 0);
		} else {
			foreach ($struct->parts as $partNum => $partStructure) {
				$mail = self::initMailPart($mbox, $mail, $partStructure, $partNum + 1);
			}
		}
		return [
			'body' => $mail['textHtml'] ? $mail['textHtml'] : $mail['textPlain'],
			'attachment' => $mail['attachments']
		];
	}

	protected static function initMailPart($mbox, $mail, $partStructure, $partNum)
	{
		$data = $partNum ? imap_fetchbody($mbox, $mail['id'], $partNum, FT_UID | FT_PEEK) : imap_body($mbox, $mail['id'], FT_UID | FT_PEEK);
		if ($partStructure->encoding == 1) {
			$data = imap_utf8($data);
		} elseif ($partStructure->encoding == 2) {
			$data = imap_binary($data);
		} elseif ($partStructure->encoding == 3) {
			$data = imap_base64($data);
		} elseif ($partStructure->encoding == 4) {
			$data = imap_qprint($data);
		}
		$params = [];
		if (!empty($partStructure->parameters)) {
			foreach ($partStructure->parameters as $param) {
				$params[strtolower($param->attribute)] = $param->value;
			}
		}
		if (!empty($partStructure->dparameters)) {
			foreach ($partStructure->dparameters as $param) {
				$paramName = strtolower(preg_match('~^(.*?)\*~', $param->attribute, $matches) ? $matches[1] : $param->attribute);
				if (isset($params[$paramName])) {
					$params[$paramName] .= $param->value;
				} else {
					$params[$paramName] = $param->value;
				}
			}
		}
		if (!empty($params['charset']) && strtolower($params['charset']) !== 'utf-8') {
			if (function_exists('mb_convert_encoding') && in_array(mb_detect_encoding($data, $params['charset']), mb_list_encodings())) {
				$encodedData = mb_convert_encoding($data, 'UTF-8', $params['charset']);
			} else {
				$encodedData = iconv($params['charset'], 'UTF-8', $data);
			}
			if ($encodedData) {
				$data = $encodedData;
			}
		}
		$attachmentId = $partStructure->ifid ? trim($partStructure->id, ' <>') : (isset($params['filename']) || isset($params['name']) ? mt_rand() . mt_rand() : null);
		if ($attachmentId) {
			if (empty($params['filename']) && empty($params['name'])) {
				$fileName = $attachmentId . '.' . strtolower($partStructure->subtype);
			} else {
				$fileName = !empty($params['filename']) ? $params['filename'] : $params['name'];
				$fileName = self::_decode_text($fileName);
				$fileName = self::decodeRFC2231($fileName);
			}
			$mail['attachments'][$attachmentId]['filename'] = $fileName;
			$mail['attachments'][$attachmentId]['attachment'] = $data;
		} elseif ($partStructure->type == 0 && $data) {
			if (preg_match('/^([a-zA-Z0-9]{76} )+[a-zA-Z0-9]{76}$/', $data) && base64_decode($data, true)) {
				$data = base64_decode($data);
			}
			if (strtolower($partStructure->subtype) == 'plain') {
				$uuDecode = self::uuDecode($data);
				if (isset($uuDecode['attachments'])) {
					$mail['attachments'] = $uuDecode['attachments'];
				}
				$mail['textPlain'] .= $uuDecode['text'];
			} else {
				$mail['textHtml'] .= $data;
			}
		} elseif ($partStructure->type == 2 && $data) {
			$mail['textPlain'] .= trim($data);
		}
		if (!empty($partStructure->parts)) {
			foreach ($partStructure->parts as $subPartNum => $subPartStructure) {
				if ($partStructure->type == 2 && $partStructure->subtype == 'RFC822') {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum);
				} else {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum . '.' . ($subPartNum + 1));
				}
			}
		}
		return $mail;
	}

	protected static function uuDecode($input)
	{
		$attachments = $parts = [];
		$pid = 0;

		$uu_regexp_begin = '/begin [0-7]{3,4} ([^\r\n]+)\r?\n/s';
		$uu_regexp_end = '/`\r?\nend((\r?\n)|($))/s';

		while (preg_match($uu_regexp_begin, $input, $matches, PREG_OFFSET_CAPTURE)) {
			$startpos = $matches[0][1];
			if (!preg_match($uu_regexp_end, $input, $m, PREG_OFFSET_CAPTURE, $startpos)) {
				break;
			}

			$endpos = $m[0][1];
			$begin_len = strlen($matches[0][0]);
			$end_len = strlen($m[0][0]);

			// extract attachment body
			$filebody = substr($input, $startpos + $begin_len, $endpos - $startpos - $begin_len - 1);
			$filebody = str_replace("\r\n", "\n", $filebody);

			// remove attachment body from the message body
			$input = substr_replace($input, '', $startpos, $endpos + $end_len - $startpos);

			// add attachments to the structure
			$attachments[] = [
				'filename' => trim($matches[1][0]),
				'attachment' => convert_uudecode($filebody)
			];
		}
		return ['attachments' => $attachments, 'text' => $input];
	}

	public function isUrlEncoded($string)
	{
		$string = str_replace('%20', '+', $string);
		$decoded = urldecode($string);
		return $decoded != $string && urlencode($decoded) == $string;
	}

	protected static function decodeRFC2231($string, $charset = 'utf-8')
	{
		if (preg_match("/^(.*?)'.*?'(.*?)$/", $string, $matches)) {
			$encoding = $matches[1];
			$data = $matches[2];
			if (self::isUrlEncoded($data)) {
				$string = iconv(strtoupper($encoding), $charset, urldecode($data));
			}
		}
		return $string;
	}

	/**
	 * Function to saving attachments
	 * @param int $relID
	 * @param OSSMail_Mail_Model $mail
	 * @return int[]
	 */
	public static function _SaveAttachments($relID, OSSMail_Mail_Model $mail)
	{
		$db = App\Db::getInstance();
		$attachments = $mail->get('attachments');
		$userid = $mail->getAccountOwner();
		$useTime = $mail->get('udate_formated');
		$setype = 'OSSMailView Attachment';

		$IDs = [];
		if ($attachments) {
			foreach ($attachments as $attachment) {
				$fileName = $attachment['filename'];
				$fileContent = $attachment['attachment'];
				$description = $fileName;
				$params = [
					'smcreatorid' => $userid,
					'smownerid' => $userid,
					'modifiedby' => $userid,
					'setype' => $setype,
					'description' => $description,
					'createdtime' => $useTime,
					'modifiedtime' => $useTime
				];
				$db->createCommand()->insert('vtiger_crmentity', $params)->execute();
				$attachId = $db->getLastInsertID('vtiger_crmentity_crmid_seq');
				$isSaved = self::saveAttachmentFile($attachId, $fileName, $fileContent);
				if ($isSaved) {
					$record = Vtiger_Record_Model::getCleanInstance('Documents');
					$record->set('assigned_user_id', $userid);
					$record->set('notes_title', $fileName);
					$record->set('filename', $fileName);
					$record->set('filestatus', 1);
					$record->set('filelocationtype', 'I');
					$record->set('folderid', 'T2');
					$record->save();
					$IDs[] = $record->getId();
					$db->createCommand()->insert('vtiger_seattachmentsrel', [
						'crmid' => $record->getId(),
						'attachmentsid' => $attachId
					])->execute();
					$db->createCommand()->update('vtiger_crmentity', [
						'createdtime' => $useTime,
						'smcreatorid' => $userid,
						'modifiedby' => $userid,
						], ['crmid' => $record->getId()]
					)->execute();
					if (!empty($relID)) {
						$dirName = vtlib\Functions::initStorageFileDirectory('OSSMailView');
						$urlToImage = "$dirName{$attachId}_$fileName";
						$db->createCommand()->insert('vtiger_ossmailview_files', [
							'ossmailviewid' => $relID,
							'documentsid' => $record->getId(),
							'attachmentsid' => $attachId
						])->execute();
						$content = (new App\Db\Query())->select(['content'])->from('vtiger_ossmailview')->where(['ossmailviewid' => $relID])->scalar();
						preg_match_all('/src="cid:(.*)"/Uims', $content, $matches);
						if (count($matches)) {
							$search = [];
							$replace = [];
							foreach ($matches[1] as $match) {
								if (strpos($fileName, $match) !== false || strpos($match, $fileName) !== false) {
									$search[] = "src=\"cid:$match\"";
									$replace[] = "src=\"$urlToImage\"";
								}
							}
							$content = str_replace($search, $replace, $content);
						}
						$db->createCommand()->update('vtiger_ossmailview', [
							'content' => $content
							], ['ossmailviewid' => $relID]
						)->execute();
					}
				}
			}
		}
		return $IDs;
	}

	/**
	 * Function to saving attachments files
	 * @param int $attachId
	 * @param string $fileName
	 * @param string $fileContent
	 * @return int
	 */
	public static function saveAttachmentFile($attachId, $fileName, $fileContent)
	{
		$dirName = vtlib\Functions::initStorageFileDirectory('OSSMailView');
		if (!is_dir($dirName)) {
			mkdir($dirName);
		}
		$fileName = str_replace([' ', ':', '/'], '-', $fileName);
		$saveAsFile = "$dirName{$attachId}_$fileName";
		if (!file_exists($saveAsFile)) {
			$fh = fopen($saveAsFile, 'wb');
			fwrite($fh, $fileContent);
			fclose($fh);
			return \App\Db::getInstance()->createCommand()->insert('vtiger_attachments', [
					'attachmentsid' => $attachId,
					'name' => $fileName,
					'description' => '',
					'type' => \App\Fields\File::getMimeContentType($saveAsFile),
					'path' => $dirName
				])->execute();
		}
		return false;
	}

	public static function getFolders($user)
	{
		$account = self::getAccountsList($user);
		$account = reset($account);
		$folders = false;
		$mbox = self::imapConnect($account['username'], $account['password'], $account['mail_host'], 'INBOX', false);
		if ($mbox) {
			$folders = [];
			$ref = '{' . $account['mail_host'] . '}';
			$list = imap_list($mbox, $ref, '*');
			foreach ($list as $mailboxname) {
				$name = str_replace($ref, '', $mailboxname);
				$folders[$name] = self::convertCharacterEncoding($name, 'UTF-8', 'UTF7-IMAP');
			}
		}
		return $folders;
	}

	public function convertCharacterEncoding($value, $toCharset, $fromCharset)
	{
		if (function_exists('mb_convert_encoding')) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($toCharset, $fromCharset, $value);
		}
		return $value;
	}

	public static function getViewableData()
	{
		$return = [];
		include 'config/modules/OSSMail.php';
		foreach ($config as $key => $value) {
			if ($key == 'skin_logo') {
				$return[$key] = $value['*'];
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	public static function setConfigData($param, $dbupdate = true)
	{
		$fileName = 'config/modules/OSSMail.php';
		$fileContent = file_get_contents($fileName);
		$Fields = self::getEditableFields();
		foreach ($param as $fieldName => $fieldValue) {
			$type = $Fields[$fieldName]['fieldType'];
			$pattern = '/(\$config\[\'' . $fieldName . '\'\])[\s]+=([^;]+);/';
			if ($type == 'checkbox' || $type == 'int') {
				$patternString = "\$config['%s'] = %s;";
			} elseif ($type == 'multipicklist') {
				if (!is_array($fieldValue)) {
					$fieldValue = [$fieldValue];
				}
				$saveValue = '[';
				foreach ($fieldValue as $value) {
					$saveValue .= "'$value' => '$value',";
				}
				$saveValue .= ']';
				$fieldValue = $saveValue;
				$patternString = "\$config['%s'] = %s;";
			} elseif ($fieldName == 'skin_logo') {
				$patternString = "\$config['%s'] = array(\"*\" => \"%s\");";
			} else {
				$patternString = "\$config['%s'] = '%s';";
			}
			$replacement = sprintf($patternString, $fieldName, $fieldValue);
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		}
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		if ($dbupdate) {
			$adb = PearDatabase::getInstance();
			$adb->pquery("update roundcube_users set language=?", array($param['language']));
		}
		return vtranslate('JS_save_config_info', 'OSSMailScanner');
	}

	public function getEditableFields()
	{
		return array(
			'product_name' => array('label' => 'LBL_RC_product_name', 'fieldType' => 'text', 'required' => 1),
			'validate_cert' => array('label' => 'LBL_RC_validate_cert', 'fieldType' => 'checkbox', 'required' => 0),
			'imap_open_add_connection_type' => array('label' => 'LBL_RC_imap_open_add_connection_type', 'fieldType' => 'checkbox', 'required' => 0),
			'default_host' => array('label' => 'LBL_RC_default_host', 'fieldType' => 'multipicklist', 'required' => 1),
			'default_port' => array('label' => 'LBL_RC_default_port', 'fieldType' => 'int', 'required' => 1),
			'smtp_server' => array('label' => 'LBL_RC_smtp_server', 'fieldType' => 'text', 'required' => 1),
			'smtp_user' => array('label' => 'LBL_RC_smtp_user', 'fieldType' => 'text', 'required' => 1),
			'smtp_pass' => array('label' => 'LBL_RC_smtp_pass', 'fieldType' => 'text', 'required' => 1),
			'smtp_port' => array('label' => 'LBL_RC_smtp_port', 'fieldType' => 'int', 'required' => 1),
			'language' => array('label' => 'LBL_RC_language', 'fieldType' => 'picklist', 'required' => 1, 'value' => array('ar_SA', 'az_AZ', 'be_BE', 'bg_BG', 'bn_BD', 'bs_BA', 'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_CH', 'de_DE', 'el_GR', 'en_CA', 'en_GB', 'en_US', 'es_419', 'es_AR', 'es_ES', 'et_EE', 'eu_ES', 'fa_AF', 'fa_IR', 'fi_FI', 'fr_FR', 'fy_NL', 'ga_IE', 'gl_ES', 'he_IL', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'id_ID', 'is_IS', 'it_IT', 'ja_JP', 'ka_GE', 'km_KH', 'ko_KR', 'lb_LU', 'lt_LT', 'lv_LV', 'mk_MK', 'ml_IN', 'mr_IN', 'ms_MY', 'nb_NO', 'ne_NP', 'nl_BE', 'nl_NL', 'nn_NO', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'si_LK', 'sk_SK', 'sl_SI', 'sq_AL', 'sr_CS', 'sv_SE', 'ta_IN', 'th_TH', 'tr_TR', 'uk_UA', 'ur_PK', 'vi_VN', 'zh_CN', 'zh_TW')),
			'username_domain' => array('label' => 'LBL_RC_username_domain', 'fieldType' => 'text', 'required' => 0),
			'skin_logo' => array('label' => 'LBL_RC_skin_logo', 'fieldType' => 'text', 'required' => 1),
			'ip_check' => array('label' => 'LBL_RC_ip_check', 'fieldType' => 'checkbox', 'required' => 0),
			'enable_spellcheck' => array('label' => 'LBL_RC_enable_spellcheck', 'fieldType' => 'checkbox', 'required' => 0),
			'identities_level' => array('label' => 'LBL_RC_identities_level', 'fieldType' => 'picklist', 'required' => 1, 'value' => array(0, 1, 2, 3, 4)),
			'session_lifetime' => array('label' => 'LBL_RC_session_lifetime', 'fieldType' => 'int', 'required' => 1),
		);
	}

	public static function getSiteUrl()
	{
		$site_URL = AppConfig::main('site_URL');
		if (substr($site_URL, -1) != '/') {
			$site_URL = $site_URL . '/';
		}
		return $site_URL;
	}

	static function getMailsFromIMAP($user = false)
	{
		$account = self::getAccountsList($user, true);
		$mails = [];
		$mailLimit = 5;
		if ($account) {
			$imap = self::imapConnect($account[0]['username'], $account[0]['password'], $account[0]['mail_host']);
			$numMessages = imap_num_msg($imap);
			if ($numMessages < $mailLimit) {
				$mailLimit = $numMessages;
			}
			for ($i = $numMessages; $i > ($numMessages - $mailLimit); $i--) {
				$header = imap_headerinfo($imap, $i);
				$mail = self::getMail($imap, false, $i);
				$mails[] = $mail;
			}
			imap_close($imap);
		}
		return $mails;
	}

	public static function getAccountByHash($hash)
	{
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM roundcube_users WHERE preferences LIKE \'%s\'', "%:\"$hash\";%");
		$result = $db->query($query);
		if ($db->getRowCount($result) > 0) {
			return $db->getRow($result);
		} else {
			return false;
		}
	}
}
