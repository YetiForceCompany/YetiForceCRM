<?php
/**
 * OSSMail record model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * OSSMail record model class.
 */
class OSSMail_Record_Model extends Vtiger_Record_Model
{
	/** @var int Mailbox Status: Active */
	const MAIL_BOX_STATUS_ACTIVE = 0;

	/** @var int Mailbox Status: Invalid access data */
	const MAIL_BOX_STATUS_INVALID_ACCESS = 1;

	/** @var int Mailbox Status: Blocked temporarily */
	const MAIL_BOX_STATUS_BLOCKED_TEMP = 2;

	/** @var int Mailbox Status: Disabled */
	const MAIL_BOX_STATUS_DISABLED = 3;

	/** @var int Mailbox Status: Blocked permanently */
	const MAIL_BOX_STATUS_BLOCKED_PERM = 4;

	/** @var string[] Mailbox status labels */
	const MAIL_BOX_STATUS_LABELS = [
		self::MAIL_BOX_STATUS_INVALID_ACCESS => 'LBL_ACCOUNT_INVALID_ACCESS',
		self::MAIL_BOX_STATUS_DISABLED => 'LBL_ACCOUNT_IS_DISABLED',
		self::MAIL_BOX_STATUS_BLOCKED_TEMP => 'LBL_ACCOUNT_IS_BLOCKED_TEMP',
		self::MAIL_BOX_STATUS_BLOCKED_PERM => 'LBL_ACCOUNT_IS_BLOCKED_PERM',
	];

	/**
	 * Get status label.
	 *
	 * @param int $status
	 *
	 * @return string
	 */
	public static function getStatusLabel(int $status): string
	{
		return self::MAIL_BOX_STATUS_LABELS[$status];
	}

	/**
	 * Return accounts array.
	 *
	 * @param int|bool $user
	 * @param bool     $onlyMy
	 * @param bool     $password
	 * @param bool     $onlyActive
	 *
	 * @return array
	 */
	public static function getAccountsList($user = false, bool $onlyMy = false, bool $password = false, bool $onlyActive = true)
	{
		$users = [];
		$query = (new \App\Db\Query())->from('roundcube_users');
		if ($onlyActive) {
			$query->where(['crm_status' => [self::MAIL_BOX_STATUS_INVALID_ACCESS, self::MAIL_BOX_STATUS_ACTIVE]]);
		}
		if ($user) {
			$query->andWhere(['user_id' => $user]);
		}
		if ($onlyMy) {
			$userModel = \App\User::getCurrentUserModel();
			$crmUsers = $userModel->getGroups();
			$crmUsers[] = $userModel->getId();
			$query->innerJoin('roundcube_users_autologin', 'roundcube_users_autologin.rcuser_id = roundcube_users.user_id');
			$query->andWhere(['roundcube_users_autologin.crmuser_id' => $crmUsers]);
		}
		if ($password) {
			$query->andWhere(['<>', 'password', '']);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['actions'] = empty($row['actions']) ? [] : explode(',', $row['actions']);
			$users[$row['user_id']] = $row;
		}
		$dataReader->close();
		return $users;
	}

	/**
	 * Returns Roundcube configuration.
	 *
	 * @return array
	 */
	public static function loadRoundcubeConfig()
	{
		$configMail = \App\Config::module('OSSMail');
		if (!\defined('RCMAIL_VERSION') && file_exists(RCUBE_INSTALL_PATH . '/program/include/iniset.php')) {
			// read rcube version from iniset
			$iniset = file_get_contents(RCUBE_INSTALL_PATH . '/program/include/iniset.php');
			if (preg_match('/define\(.RCMAIL_VERSION.,\s*.([0-9.]+[a-z-]*)?/', $iniset, $matches)) {
				$rcubeVersion = str_replace('-git', '.999', $matches[1]);
				\define('RCMAIL_VERSION', $rcubeVersion);
				\define('RCUBE_VERSION', $rcubeVersion);
			} else {
				throw new \App\Exceptions\AppException('Unable to find a Roundcube version');
			}
		}
		include 'public_html/modules/OSSMail/roundcube/config/defaults.inc.php';
		return $configMail + $config;
	}

	/**
	 * Imap connection cache.
	 *
	 * @var array
	 */
	protected static $imapConnectCache = [];

	/**
	 * $imapConnectMailbox.
	 *
	 * @var string
	 */
	public static $imapConnectMailbox = '';

	/**
	 * Return imap connection resource.
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $host
	 * @param string $folder     Character encoding UTF7-IMAP
	 * @param bool   $dieOnError
	 * @param array  $config
	 * @param array  $account
	 *
	 * @return IMAP\Connection|false
	 */
	public static function imapConnect($user, $password, $host = '', $folder = 'INBOX', $dieOnError = true, $config = [], array $account = [])
	{
		\App\Log::trace("Entering OSSMail_Record_Model::imapConnect($user , '****' , $folder) method ...");
		if (!$config) {
			$config = self::loadRoundcubeConfig();
		}
		$cacheName = $user . $host . $folder;
		if (isset(self::$imapConnectCache[$cacheName])) {
			return self::$imapConnectCache[$cacheName];
		}

		$hosts = [];
		if ($imapHost = $config['imap_host'] ?? '') {
			$hosts = \is_string($imapHost) ? [$imapHost => $imapHost] : $imapHost;
		}
		if (!$host && $hosts) {
			$host = array_key_first($hosts);
		}

		$parseHost = parse_url($host);
		if (empty($parseHost['host'])) {
			foreach ($hosts as $row) {
				if (false !== strpos($row, $host)) {
					$parseHost = parse_url($row);
					break;
				}
			}
		}
		$port = 143;
		$sslMode = 'tls';
		if (!empty($parseHost['host'])) {
			$host = $parseHost['host'];
			$sslMode = (isset($parseHost['scheme']) && \in_array($parseHost['scheme'], ['ssl', 'imaps', 'tls'])) ? $parseHost['scheme'] : null;
			if (!empty($parseHost['port'])) {
				$port = $parseHost['port'];
			} elseif ($sslMode && 'tls' !== $sslMode) {
				$port = 993;
			}
		}
		$validateCert = '';
		if (!$config['validate_cert'] && $config['imap_open_add_connection_type']) {
			$validateCert = '/novalidate-cert';
		}
		if ($config['imap_open_add_connection_type']) {
			$sslMode = '/' . $sslMode;
		} else {
			$sslMode = '';
		}
		imap_timeout(IMAP_OPENTIMEOUT, 5);
		$maxRetries = $options = 0;
		if (isset($config['imap_max_retries'])) {
			$maxRetries = $config['imap_max_retries'];
		}
		$params = [];
		if (isset($config['imap_params'])) {
			$params = $config['imap_params'];
		}
		static::$imapConnectMailbox = "{{$host}:{$port}/imap{$sslMode}{$validateCert}}{$folder}";
		\App\Log::trace('imap_open(({' . static::$imapConnectMailbox . ", $user , '****'. $options, $maxRetries, " . var_export($params, true) . ') method ...');
		\App\Log::beginProfile(__METHOD__ . '|imap_open|' . $user, 'Mail|IMAP');
		$mbox = imap_open(static::$imapConnectMailbox, $user, $password, $options, $maxRetries, $params);
		\App\Log::endProfile(__METHOD__ . '|imap_open|' . $user, 'Mail|IMAP');
		self::$imapConnectCache[$cacheName] = $mbox;
		if ($mbox) {
			if ($account) {
				\App\Db::getInstance()->createCommand()
					->update('roundcube_users', ['crm_error' => null, 'crm_status' => self::MAIL_BOX_STATUS_ACTIVE], ['user_id' => $account['user_id']])
					->execute();
			}
			\App\Log::trace('Exit OSSMail_Record_Model::imapConnect() method ...');
			register_shutdown_function(function () use ($mbox, $user) {
				try {
					\App\Log::beginProfile('OSSMail_Record_Model|imap_close|' . $user, 'Mail|IMAP');
					imap_close($mbox);
					\App\Log::endProfile('OSSMail_Record_Model|imap_close|' . $user, 'Mail|IMAP');
				} catch (\Throwable $e) {
					\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
					throw $e;
				}
			});
		} else {
			if ($account) {
				$status = self::MAIL_BOX_STATUS_ACTIVE == $account['crm_status'] ? self::MAIL_BOX_STATUS_INVALID_ACCESS : self::MAIL_BOX_STATUS_BLOCKED_TEMP;
				[$date] = explode('||', $account['crm_error'] ?: '');
				if (empty($date) || false === strtotime($date)) {
					$date = date('Y-m-d H:i:s');
				}
				if (self::MAIL_BOX_STATUS_BLOCKED_TEMP === $status && strtotime('-' . (OSSMailScanner_Record_Model::getConfig('blocked')['permanentTime'] ?? '2 day')) > strtotime($date)) {
					$status = self::MAIL_BOX_STATUS_BLOCKED_PERM;
				}
				\App\Db::getInstance()->createCommand()
					->update('roundcube_users', [
						'crm_error' => \App\TextUtils::textTruncate($date . '||' . imap_last_error(), 250),
						'crm_status' => $status,
						'failed_login' => date('Y-m-d H:i:s'),
					], ['user_id' => $account['user_id']])
					->execute();
			}
			\App\Log::error('Error OSSMail_Record_Model::imapConnect(' . static::$imapConnectMailbox . '): ' . imap_last_error());
			if ($dieOnError) {
				throw new \App\Exceptions\AppException('IMAP_ERROR' . ': ' . imap_last_error());
			}
		}
		return $mbox;
	}

	/**
	 * Update mailbox mesages info for users.
	 *
	 * @param array $users
	 *
	 * @return array
	 */
	public static function updateMailBoxCounter(array $users): array
	{
		if (empty($users)) {
			return [];
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		$config = Settings_Mail_Config_Model::getConfig('mailIcon');
		$interval = $config['timeCheckingMail'] ?? 30;
		$date = strtotime("-{$interval} seconds");
		$counter = [];
		$all = (new \App\Db\Query())->from('u_#__mail_quantities')->where(['userid' => $users])->indexBy('userid')->all();
		foreach ($users as $user) {
			if (empty($all[$user]['date']) || $date > strtotime($all[$user]['date'])) {
				if ($account = self::getMailAccountDetail($user)) {
					if (empty($all[$user])) {
						$dbCommand->insert('u_#__mail_quantities', ['userid' => $user, 'num' => 0, 'date' => date('Y-m-d H:i:s')])->execute();
					} else {
						$dbCommand->update('u_#__mail_quantities', ['date' => date('Y-m-d H:i:s')], ['userid' => $user])->execute();
					}
					try {
						$mbox = self::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], 'INBOX', false, [], $account);
						if ($mbox) {
							\App\Log::beginProfile(__METHOD__ . '|imap_status|' . $user, 'Mail|IMAP');
							$info = imap_status($mbox, static::$imapConnectMailbox, SA_UNSEEN);
							\App\Log::endProfile(__METHOD__ . '|imap_status|' . $user, 'Mail|IMAP');
							$counter[$user] = $info->unseen ?? 0;
							$dbCommand->update('u_#__mail_quantities', ['num' => $counter[$user], 'date' => date('Y-m-d H:i:s')], ['userid' => $user])->execute();
						}
					} catch (\Throwable $th) {
					}
				}
			} else {
				$counter[$user] = $all[$user]['num'] ?? 0;
			}
		}
		return $counter;
	}

	/**
	 * @param resource $mbox
	 * @param int      $id
	 * @param int      $msgno
	 * @param bool     $fullMode
	 *
	 * @return bool|\OSSMail_Mail_Model
	 */
	public static function getMail($mbox, $id, $msgno = false, bool $fullMode = true)
	{
		if (!$msgno) {
			\App\Log::beginProfile(__METHOD__ . '|imap_msgno', 'Mail|IMAP');
			$msgno = imap_msgno($mbox, $id);
			\App\Log::endProfile(__METHOD__ . '|imap_msgno', 'Mail|IMAP');
		}
		if (!$id) {
			\App\Log::beginProfile(__METHOD__ . '|imap_uid', 'Mail|IMAP');
			$id = imap_uid($mbox, $msgno);
			\App\Log::endProfile(__METHOD__ . '|imap_uid', 'Mail|IMAP');
		}
		if (!$msgno) {
			return false;
		}
		\App\Log::beginProfile(__METHOD__ . '|imap_headerinfo', 'Mail|IMAP');
		$header = imap_headerinfo($mbox, $msgno);
		\App\Log::endProfile(__METHOD__ . '|imap_headerinfo', 'Mail|IMAP');
		$messageId = '';
		if (property_exists($header, 'message_id')) {
			$messageId = $header->message_id;
		}
		$mail = new OSSMail_Mail_Model();
		$mail->set('header', $header);
		$mail->set('id', $id);
		$mail->set('Msgno', $header->Msgno);
		$mail->set('message_id', $messageId ? \App\Purifier::purifyByType($messageId, 'MailId') : '');
		$mail->set('to_email', \App\Purifier::purify($mail->getEmail('to')));
		$mail->set('from_email', \App\Purifier::purify($mail->getEmail('from')));
		$mail->set('reply_toaddress', \App\Purifier::purify($mail->getEmail('reply_to')));
		$mail->set('cc_email', \App\Purifier::purify($mail->getEmail('cc')));
		$mail->set('bcc_email', \App\Purifier::purify($mail->getEmail('bcc')));
		$mail->set('firstLetterBg', strtoupper(\App\TextUtils::textTruncate(trim(strip_tags(App\Purifier::purify($mail->getEmail('from')))), 1, false)));
		$mail->set('subject', isset($header->subject) ? \App\TextUtils::textTruncate(\App\Purifier::purify(self::decodeText($header->subject)), 65535, false) : '');
		$mail->set('date', date('Y-m-d H:i:s', $header->udate));
		if ($fullMode) {
			$structure = self::getBodyAttach($mbox, $id, $msgno);
			$mail->set('body', $structure['body']);
			$mail->set('attachments', $structure['attachment']);
			$mail->set('isHtml', $structure['isHtml']);

			$clean = '';
			\App\Log::beginProfile(__METHOD__ . '|imap_fetch_overview', 'Mail|IMAP');
			$msgs = imap_fetch_overview($mbox, $msgno);
			\App\Log::endProfile(__METHOD__ . '|imap_fetch_overview', 'Mail|IMAP');

			foreach ($msgs as $msg) {
				\App\Log::beginProfile(__METHOD__ . '|imap_fetchheader', 'Mail|IMAP');
				$clean .= imap_fetchheader($mbox, $msg->msgno);
				\App\Log::endProfile(__METHOD__ . '|imap_fetchheader', 'Mail|IMAP');
			}
			$mail->set('clean', $clean);
		}
		return $mail;
	}

	/**
	 * Users cache.
	 *
	 * @var array
	 */
	protected static $usersCache = [];

	/**
	 * Return user account detal.
	 *
	 * @param int $userid
	 *
	 * @return array
	 */
	public static function getMailAccountDetail($userid)
	{
		if (isset(self::$usersCache[$userid])) {
			return self::$usersCache[$userid];
		}
		$user = (new \App\Db\Query())->from('roundcube_users')->where(['user_id' => $userid, 'crm_status' => [self::MAIL_BOX_STATUS_INVALID_ACCESS, self::MAIL_BOX_STATUS_ACTIVE]])->one();
		self::$usersCache[$userid] = $user;
		return $user;
	}

	/**
	 * Convert text encoding.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function decodeText($text)
	{
		$data = imap_mime_header_decode($text);
		$text = '';
		foreach ($data as &$row) {
			$charset = ('default' == $row->charset) ? 'ASCII' : $row->charset;
			if (\function_exists('mb_convert_encoding') && \in_array($charset, mb_list_encodings())) {
				$text .= mb_convert_encoding($row->text, 'utf-8', $charset);
			} else {
				$text .= iconv($charset, 'UTF-8', $row->text);
			}
		}
		return $text;
	}

	/**
	 * Return full name.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function getFullName($text)
	{
		$return = '';
		foreach ($text as $row) {
			if ('' != $return) {
				$return .= ',';
			}
			if ('' == $row->personal) {
				$return .= $row->mailbox . '@' . $row->host;
			} else {
				$return .= self::decodeText($row->personal) . ' - ' . $row->mailbox . '@' . $row->host;
			}
		}
		return $return;
	}

	/**
	 * Return body and attachments.
	 *
	 * @param resource $mbox
	 * @param int      $id
	 * @param int      $msgno
	 *
	 * @return array
	 */
	public static function getBodyAttach($mbox, $id, $msgno)
	{
		\App\Log::beginProfile(__METHOD__ . '|imap_fetchstructure', 'Mail|IMAP');
		$struct = imap_fetchstructure($mbox, $id, FT_UID);
		\App\Log::endProfile(__METHOD__ . '|imap_fetchstructure', 'Mail|IMAP');
		$mail = ['id' => $id];
		if (empty($struct->parts)) {
			$mail = self::initMailPart($mbox, $mail, $struct, 0);
		} else {
			foreach ($struct->parts as $partNum => $partStructure) {
				$mail = self::initMailPart($mbox, $mail, $partStructure, $partNum + 1);
			}
		}
		$body = '';
		$body = (!empty($mail['textPlain'])) ? $mail['textPlain'] : $body;
		$body = (!empty($mail['textHtml'])) ? $mail['textHtml'] : $body;
		$attachment = (isset($mail['attachments'])) ? $mail['attachments'] : [];

		return [
			'body' => $body,
			'attachment' => $attachment,
			'isHtml' => !empty($mail['textHtml']),
		];
	}

	/**
	 * Init mail part.
	 *
	 * @param resource $mbox
	 * @param array    $mail
	 * @param object   $partStructure
	 * @param int      $partNum
	 *
	 * @return array
	 */
	protected static function initMailPart($mbox, $mail, $partStructure, $partNum)
	{
		if ($partNum) {
			\App\Log::beginProfile(__METHOD__ . '|imap_fetchbody', 'Mail|IMAP');
			$data = $orgData = imap_fetchbody($mbox, $mail['id'], $partNum, FT_UID | FT_PEEK);
			\App\Log::endProfile(__METHOD__ . '|imap_fetchbody', 'Mail|IMAP');
		} else {
			\App\Log::beginProfile(__METHOD__ . '|imap_body', 'Mail|IMAP');
			$data = $orgData = imap_body($mbox, $mail['id'], FT_UID | FT_PEEK);
			\App\Log::endProfile(__METHOD__ . '|imap_body', 'Mail|IMAP');
		}
		if (1 == $partStructure->encoding) {
			$data = imap_utf8($data);
		} elseif (2 == $partStructure->encoding) {
			$data = imap_binary($data);
		} elseif (3 == $partStructure->encoding) {
			$data = imap_base64($data);
		} elseif (4 == $partStructure->encoding) {
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
		if (!empty($params['charset']) && 'utf-8' !== strtolower($params['charset'])) {
			if (\function_exists('mb_convert_encoding') && \in_array($params['charset'], mb_list_encodings())) {
				$encodedData = mb_convert_encoding($data, 'UTF-8', $params['charset']);
			} else {
				$encodedData = iconv($params['charset'], 'UTF-8', $data);
			}
			if ($encodedData) {
				$data = $encodedData;
			}
		}
		$attachmentId = $partStructure->ifid ? trim($partStructure->id, ' <>') : (isset($params['filename']) || isset($params['name']) ? random_int(0, PHP_INT_MAX) . random_int(0, PHP_INT_MAX) : null);
		if ($attachmentId) {
			if (empty($params['filename']) && empty($params['name'])) {
				$fileName = $attachmentId . '.' . strtolower($partStructure->subtype);
			} else {
				$fileName = !empty($params['filename']) ? $params['filename'] : $params['name'];
				$fileName = self::decodeText($fileName);
				$fileName = self::decodeRFC2231($fileName);
			}
			$mail['attachments'][$attachmentId]['filename'] = $fileName;
			$mail['attachments'][$attachmentId]['attachment'] = $data;
		} elseif (0 == $partStructure->type && $data) {
			if (preg_match('/^([a-zA-Z0-9]{76} )+[a-zA-Z0-9]{76}$/', $data) && base64_decode($data, true)) {
				$data = base64_decode($data);
			}
			if ('plain' == strtolower($partStructure->subtype)) {
				$uuDecode = self::uuDecode($data);
				if (isset($uuDecode['attachments'])) {
					$mail['attachments'] = $uuDecode['attachments'];
				}
				if (!isset($mail['textPlain'])) {
					$mail['textPlain'] = '';
				}
				if (isset($params['format']) && 'flowed' === $params['format']) {
					$uuDecode['text'] = self::unfoldFlowed($uuDecode['text'], isset($params['delsp']) && 'yes' === strtolower($params['delsp']));
				}
				$mail['textPlain'] .= $uuDecode['text'];
			} else {
				if (!isset($mail['textHtml'])) {
					$mail['textHtml'] = '';
				}
				if ($data && '<' !== $data[0] && '<' === $orgData[0]) {
					$data = $orgData;
				}
				$mail['textHtml'] .= $data;
			}
		} elseif (2 == $partStructure->type && $data) {
			if (!isset($mail['textPlain'])) {
				$mail['textPlain'] = '';
			}
			$mail['textPlain'] .= trim($data);
		}
		if (!empty($partStructure->parts)) {
			foreach ($partStructure->parts as $subPartNum => $subPartStructure) {
				if (2 == $partStructure->type && 'RFC822' == $partStructure->subtype) {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum);
				} else {
					$mail = self::initMailPart($mbox, $mail, $subPartStructure, $partNum . '.' . ($subPartNum + 1));
				}
			}
		}
		return $mail;
	}

	/**
	 * Decode string.
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	protected static function uuDecode($input)
	{
		$attachments = [];
		$uu_regexp_begin = '/begin [0-7]{3,4} ([^\r\n]+)\r?\n/s';
		$uu_regexp_end = '/`\r?\nend((\r?\n)|($))/s';

		while (preg_match($uu_regexp_begin, $input, $matches, PREG_OFFSET_CAPTURE)) {
			$startpos = $matches[0][1];
			if (!preg_match($uu_regexp_end, $input, $m, PREG_OFFSET_CAPTURE, $startpos)) {
				break;
			}

			$endpos = $m[0][1];
			$begin_len = \strlen($matches[0][0]);
			$end_len = \strlen($m[0][0]);

			// extract attachment body
			$filebody = substr($input, $startpos + $begin_len, $endpos - $startpos - $begin_len - 1);
			$filebody = str_replace("\r\n", "\n", $filebody);

			// remove attachment body from the message body
			$input = substr_replace($input, '', $startpos, $endpos + $end_len - $startpos);

			// add attachments to the structure
			$attachments[] = [
				'filename' => trim($matches[1][0]),
				'attachment' => convert_uudecode($filebody),
			];
		}
		return ['attachments' => $attachments, 'text' => $input];
	}

	/**
	 * Parse format=flowed message body.
	 *
	 * @param string $text
	 * @param bool   $delSp
	 *
	 * @return string
	 */
	protected static function unfoldFlowed(string $text, bool $delSp = false): string
	{
		$text = preg_split('/\r?\n/', $text);
		$last = -1;
		$qLevel = 0;
		foreach ($text as $idx => $line) {
			if ($q = strspn($line, '>')) {
				$line = substr($line, $q);
				if (isset($line[0]) && ' ' === $line[0]) {
					$line = substr($line, 1);
				}
				if ($q == $qLevel
					&& isset($text[$last]) && ' ' == $text[$last][\strlen($text[$last]) - 1]
					&& !preg_match('/^>+ {0,1}$/', $text[$last])
				) {
					if ($delSp) {
						$text[$last] = substr($text[$last], 0, -1);
					}
					$text[$last] .= $line;
					unset($text[$idx]);
				} else {
					$last = $idx;
				}
			} else {
				if ('-- ' == $line) {
					$last = $idx;
				} else {
					if (isset($line[0]) && ' ' === $line[0]) {
						$line = substr($line, 1);
					}
					if (isset($text[$last]) && $line && !$qLevel
						&& '-- ' !== $text[$last]
						&& isset($text[$last][\strlen($text[$last]) - 1]) && ' ' === $text[$last][\strlen($text[$last]) - 1]
					) {
						if ($delSp) {
							$text[$last] = substr($text[$last], 0, -1);
						}
						$text[$last] .= $line;
						unset($text[$idx]);
					} else {
						$text[$idx] = $line;
						$last = $idx;
					}
				}
			}
			$qLevel = $q;
		}

		return implode("\r\n", $text);
	}

	/**
	 * Check if url is encoded.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isUrlEncoded($string)
	{
		$string = str_replace('%20', '+', $string);
		$decoded = urldecode($string);

		return $decoded != $string && urlencode($decoded) == $string;
	}

	/**
	 * decode RFC2231 formatted string.
	 *
	 * @param string $string
	 * @param string $charset
	 *
	 * @return string
	 */
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
	 * Return user folders.
	 *
	 * @param int $user
	 *
	 * @return array
	 */
	public static function getFolders($user)
	{
		$account = self::getAccountsList($user);
		$account = reset($account);
		$folders = false;
		$mbox = self::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], 'INBOX', false, [], $account);
		if ($mbox) {
			$folders = [];
			$ref = '{' . $account['mail_host'] . '}';
			$list = imap_list($mbox, $ref, '*');
			foreach ($list as $mailboxname) {
				$name = str_replace($ref, '', $mailboxname);
				$name = \App\Utils::convertCharacterEncoding($name, 'UTF7-IMAP', 'UTF-8');
				$folders[$name] = $name;
			}
		}
		return $folders;
	}

	/**
	 * Return site URL.
	 *
	 * @return string
	 */
	public static function getSiteUrl()
	{
		$site_URL = App\Config::main('site_URL');
		if ('/' != substr($site_URL, -1)) {
			$site_URL = $site_URL . '/';
		}
		return $site_URL;
	}

	/**
	 * Fetch mails from IMAP.
	 *
	 * @param int|null $user
	 *
	 * @return array
	 */
	public static function getMailsFromIMAP(?int $user = null)
	{
		$accounts = self::getAccountsList(false, true);
		$mails = [];
		$mailLimit = 5;
		if ($accounts) {
			if ($user && isset($accounts[$user])) {
				$account = $accounts[$user];
			} else {
				$account = reset($accounts);
			}
			$imap = self::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], 'INBOX', true, [], $account);
			\App\Log::beginProfile(__METHOD__ . '|imap_num_msg', 'Mail|IMAP');
			$numMessages = imap_num_msg($imap);
			\App\Log::endProfile(__METHOD__ . '|imap_num_msg', 'Mail|IMAP');
			if ($numMessages < $mailLimit) {
				$mailLimit = $numMessages;
			}
			for ($i = $numMessages; $i > ($numMessages - $mailLimit); --$i) {
				$mail = self::getMail($imap, false, $i);
				$mails[] = $mail;
			}
		}
		return $mails;
	}

	/**
	 * Get mail account detail by hash ID.
	 *
	 * @param string $hash
	 *
	 * @return bool|array
	 */
	public static function getAccountByHash($hash)
	{
		if (preg_match('/^[_a-zA-Z0-9.,]+$/', $hash)) {
			$result = (new \App\Db\Query())
				->from('roundcube_users')
				->where(['like', 'preferences', "%:\"$hash\";%", false])
				->one();
			if ($result) {
				return $result;
			}
		}
		return false;
	}

	/**
	 * Update user data for account.
	 *
	 * @param int   $userId
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function setAccountUserData(int $userId, array $data): bool
	{
		return \App\Db::getInstance()->createCommand()->update('roundcube_users', $data, ['user_id' => $userId])->execute();
	}
}
