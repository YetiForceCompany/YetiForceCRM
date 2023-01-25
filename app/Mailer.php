<?php

/*
 * Mailer basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
	/** @var string[] Queue status */
	public static $statuses = [
		0 => 'LBL_PENDING_ACCEPTANCE',
		1 => 'LBL_WAITING_TO_BE_SENT',
		2 => 'LBL_ERROR_DURING_SENDING',
	];

	/** @var string[] Columns list that require JSON formatting */
	public static $quoteJsonColumn = ['from', 'to', 'cc', 'bcc', 'attachments', 'params'];

	/** @var string[] Columns list available in the database */
	public static $quoteColumn = ['smtp_id', 'date', 'owner', 'status', 'from', 'subject', 'content', 'to', 'cc', 'bcc', 'attachments', 'priority'];

	/** @var PHPMailer Instance */
	protected $mailer;

	/** @var array SMTP configuration */
	protected $smtp;

	/** @var array Default settings */
	private $default = [
		'mailer_type' => 'smtp',
		'password' => '',
		'default' => '',
		'name' => '',
		'host' => '',
		'port' => '',
		'username' => '',
		'authentication' => 1,
		'secure' => '',
		'options' => [],
		'from_email' => '',
		'from_name' => '',
		'reply_to' => '',
		'priority' => '',
		'confirm_reading_to' => '',
		'organization' => '',
		'unsubscribe' => '',
		'individual_delivery' => 1,
		'imap_username' => '',
		'imap_password' => '',
		'imap_validate_cert' => 0,
		'imap_host' => '',
		'imap_port' => 0,
		'imap_folder' => '',
		'save_send_mail' => 0,
		'authType' => '',
		'authProvider' => null,
		'mail_account' => 0
	];

	/** @var array Parameters for sending messages */
	protected $params = [];

	/** @var array Error logs */
	public static $error;
	/** @var bool Debug active */
	public $debug = false;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		static::$error = [];
		$this->debug = \App\Config::debug('MAILER_DEBUG');
		$this->mailer = new PHPMailer(false);
		$this->mailer->XMailer = 'YetiForceCRM Mailer';
		$this->mailer->Hostname = 'YetiForceCRM';
		$this->mailer->FromName = 'YetiForce Mailer';
		$this->mailer->CharSet = \App\Config::main('default_charset');
	}

	/**
	 * Load configuration smtp by ID.
	 *
	 * @param int $smtpId Smtp ID
	 *
	 * @return $this mailer object itself
	 */
	public function loadSmtpByID(int $smtpId)
	{
		$this->loadSmtp(Mail::getSmtpById($smtpId));
		return $this;
	}

	/**
	 * Load configuration smtp.
	 *
	 * @param array $smtpInfo
	 *
	 * @return $this mailer object itself
	 */
	public function loadSmtp($smtpInfo)
	{
		$this->smtp = $this->parseData($smtpInfo);
		$this->setSmtp();

		return $this;
	}

	/**
	 * Parse data.
	 *
	 * @param array $smtpInfo
	 *
	 * @return array
	 */
	public function parseData(array $smtpInfo): array
	{
		$data = array_merge($this->default, array_intersect_key($smtpInfo, $this->default));
		if (('yfsmtp' === $data['mailer_type'])) {
			if (!($account = Mail\Account::getInstanceById((int) $data['mail_account'])) || !$account->isActive()) {
				static::$error[] = 'ERR_MAIL_ACCOUNT_NOT_ACTIVE';
				return [];
			}
			$data['mailer_type'] = 'smtp';
			$data['host'] = $account->getServer()->get('smtp_host');
			$data['port'] = $account->getServer()->get('smtp_port');
			$data['username'] = $account->getLogin();
			$data['authentication'] = 1;
			$data['secure'] = $account->getServer()->get('smtp_encrypt');
			$data['imap_username'] = $account->getLogin();
			$data['imap_validate_cert'] = $account->getServer()->get('imap_encrypt');
			$data['imap_host'] = $account->getServer()->get('imap_host');
			$data['imap_port'] = $account->getServer()->get('imap_port');
			if ($account->getServer()->isOAuth()) {
				$data['authType'] = 'XOAUTH2';
				$data['authProvider'] = new \PHPMailer\PHPMailer\OAuth(
					[
						'provider' => $account->getOAuthProvider()->getClient(),
						'clientId' => $account->getServer()->get('client_id'),
						'clientSecret' => $account->getServer()->getClientSecret(),
						'refreshToken' => $account->getRefreshToken(),
						'userName' => $account->getLogin(),
					]
				);
				if (!empty($data['save_send_mail'])) {
					$data['imap_password'] = $account->getPassword();
				}
			} else {
				$data['password'] = $data['imap_password'] = $account->getPassword();
			}
		} else {
			$data['password'] = empty($data['password']) ? '' : Encryption::getInstance()->decrypt($data['password']);
			$data['imap_password'] = empty($data['imap_password']) ? '' : Encryption::getInstance()->decrypt($data['imap_password']);
		}
		$options = $data['options'];
		if ($options && !\is_array($options)) {
			$data['options'] = Json::decode($options, true) ?: [];
		}

		return $data;
	}

	/**
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function sendFromTemplate(array $params): bool
	{
		Log::trace('Send mail from template', 'Mailer');
		if (empty($params['template'])) {
			Log::warning('No template', 'Mailer');
			return false;
		}
		$recordModel = false;
		if (empty($params['recordModel'])) {
			$moduleName = $params['moduleName'] ?? null;
			if (isset($params['recordId'])) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($params['recordId'], $moduleName);
			}
		} else {
			$recordModel = $params['recordModel'];
			unset($params['recordModel']);
		}
		$template = Mail::getTemplate($params['template']);
		if (!$template) {
			Log::warning('No mail template', 'Mailer');
			return false;
		}
		$textParser = $recordModel ? TextParser::getInstanceByModel($recordModel) : TextParser::getInstance($params['moduleName'] ?? '');
		if (!empty($params['language'])) {
			$textParser->setLanguage($params['language']);
		}
		if (!empty($params['sourceRecord'])) {
			$textParser->setSourceRecord($params['sourceRecord'], $params['sourceModule']);
		}
		$textParser->setParams(array_diff_key($params, array_flip(['subject', 'content', 'attachments', 'recordModel'])));
		$subject = $params['subject'] ?? $template['subject'];
		$params['subject'] = $textParser->setContent($subject)->parse()->getContent();
		$params['content'] = $textParser->setContent(\App\Utils\Completions::decode(\App\Purifier::purifyHtml($template['content'])))->parse()->getContent();
		unset($textParser);
		if (empty($params['smtp_id']) && !empty($template['smtp_id'])) {
			$params['smtp_id'] = $template['smtp_id'];
		}
		if (isset($template['attachments'])) {
			$params['attachments'] = array_merge(empty($params['attachments']) ? [] : $params['attachments'], $template['attachments']);
		}
		if (!empty($template['email_template_priority'])) {
			$params['priority'] = $template['email_template_priority'];
		}
		$row = array_intersect_key($params, array_flip(self::$quoteColumn));
		$row['params'] = array_diff_key($params, $row);
		return static::addMail($row);
	}

	/**
	 * Add mail to quote for send.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function addMail(array $params): bool
	{
		$response = false;
		$params['date'] = date('Y-m-d H:i:s');
		if (!\array_key_exists('status', $params)) {
			$params['status'] = Config::component('Mail', 'MAILER_REQUIRED_ACCEPTATION_BEFORE_SENDING') ? 0 : 1;
		}
		if (empty($params['owner'])) {
			$owner = User::getCurrentUserRealId();
			$params['owner'] = $owner ?: 0;
		}
		if (empty($params['smtp_id'])) {
			$params['smtp_id'] = Mail::getDefaultSmtp();
		}
		if (empty($params['smtp_id'])) {
			$params['error_code'] = 1;
			static::insertMailLog($params);
			Log::warning('No SMTP configuration', 'Mailer');
		} elseif (!\App\Mail::getSmtpById($params['smtp_id'])) {
			$params['error_code'] = 2;
			static::insertMailLog($params);
			Log::warning('SMTP configuration with provided id not exists', 'Mailer');
		} elseif (empty($params['to'])) {
			$params['error_code'] = 3;
			static::insertMailLog($params);
			Log::warning('No target email address provided', 'Mailer');
		} else {
			$smpt = \App\Mail::getSmtpById($params['smtp_id']);
			if ($smpt['individual_delivery']) {
				$to = $params['to'];
				if (!\is_array($to)) {
					$to = \App\Json::isJson($to) ? \App\Json::decode($to) : [$to];
				}
				foreach ($to as $key => $value) {
					$params['to'] = [$key => $value];
					$response = static::insertMail($params);
				}
			} else {
				$response = static::insertMail($params);
			}
		}

		return $response;
	}

	/**
	 * Add mail to queue.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function insertMail(array $params): bool
	{
		$eventHandler = new EventHandler();
		$eventHandler->setParams($params);
		$eventHandler->trigger('MailerAddToQueue');
		$params = $eventHandler->getParams();

		$fields = ['smtp_id', 'date', 'owner', 'status', 'from', 'subject', 'to', 'content', 'cc', 'bcc', 'attachments', 'params', 'priority', 'error'];
		$insertData = array_intersect_key($params, array_flip($fields));
		foreach (static::$quoteJsonColumn as $key) {
			if (isset($insertData[$key]) && (!\is_string($insertData[$key]) || !\App\Json::isJson($insertData[$key]))) {
				$insertData[$key] = Json::encode(!\is_array($insertData[$key]) ? [$insertData[$key]] : $insertData[$key]);
			}
		}

		return (bool) \App\Db::getInstance('admin')->createCommand()->insert('s_#__mail_queue', $insertData)->execute();
	}

	/**
	 * Save mail log data.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function insertMailLog(array $params): void
	{
		$eventHandler = new EventHandler();
		$eventHandler->setParams($params);
		$eventHandler->trigger('MailerAddToLogs');
		$params = $eventHandler->getParams();

		$logFields = ['date', 'error_code', 'smtp_id', 'owner', 'status', 'from', 'subject', 'to', 'content', 'cc', 'bcc', 'attachments', 'params'];
		$insertData = array_intersect_key($params, array_flip($logFields));
		foreach (static::$quoteJsonColumn as $key) {
			if (isset($insertData[$key]) && (!\is_string($insertData[$key]) || !\App\Json::isJson($insertData[$key]))) {
				$insertData[$key] = Json::encode(!\is_array($insertData[$key]) ? [$insertData[$key]] : $insertData[$key]);
			}
		}

		\App\Db::getInstance('log')->createCommand()->insert('l_#__mail', $insertData)->execute();
	}

	/**
	 * Set configuration smtp in mailer.
	 */
	public function setSmtp(): void
	{
		if (!$this->smtp) {
			static::$error[] = 'ERR_NO_SMTP_CONFIGURATION';
			return;
		}
		switch ($this->smtp['mailer_type']) {
			case 'smtp':
				$this->mailer->isSMTP();
				break;
			case 'sendmail':
				$this->mailer->isSendmail();
				break;
			case 'mail':
				$this->mailer->isMail();
				break;
			case 'qmail':
				$this->mailer->isQmail();
				break;
			default:
				break;
		}

		$this->mailer->Host = $this->smtp['host'];
		if (!empty($this->smtp['port'])) {
			$this->mailer->Port = $this->smtp['port'];
		}
		$this->mailer->SMTPSecure = $this->smtp['secure'];
		$this->mailer->SMTPAuth = (bool) $this->smtp['authentication'];
		$this->mailer->Username = trim($this->smtp['username']);
		if (!empty($this->smtp['password'])) {
			$this->mailer->Password = $this->smtp['password'];
		}
		if ($options = $this->smtp['options']) {
			$this->mailer->SMTPOptions = $options;
		}
		$this->mailer->setFrom($this->smtp['from_email'] ?: $this->smtp['username'], $this->smtp['from_name'] ?? '', false);
		if ($this->smtp['reply_to']) {
			$this->mailer->addReplyTo($this->smtp['reply_to']);
		}
		if ($this->smtp['unsubscribe']) {
			$unsubscribe = '';
			foreach (\App\Json::decode($this->smtp['unsubscribe']) as $row) {
				$unsubscribe .= "<$row>,";
			}
			$unsubscribe = rtrim($unsubscribe, ',');
			$this->mailer->AddCustomHeader('List-Unsubscribe', $unsubscribe);
		}
		if ($this->smtp['priority']) {
			$priorityName = $priority = $priorityX = null;
			switch ($this->smtp['priority']) {
				case 'normal':
				case 'Normal':
					$priorityX = 3;
					$priority = $priorityName = 'Normal';
					break;
				case 'non-urgent':
				case 'Low':
					$priorityX = 5;
					$priority = 'Non-Urgent';
					$priorityName = 'Low';
					break;
				case 'urgent':
				case 'High':
						$priorityX = 1;
						$priority = 'Urgent';
						$priorityName = 'High';
					break;
				default: break;
			}
			if ($priority) {
				$this->mailer->Priority = $priorityX;
				$this->mailer->AddCustomHeader('Priority', $priority);
				$this->mailer->AddCustomHeader('X-MSMail-Priority', $priorityName);
				$this->mailer->AddCustomHeader('Importance', $priorityName);
			}
		}
		if ($this->smtp['confirm_reading_to']) {
			$this->mailer->ConfirmReadingTo = $this->smtp['confirm_reading_to'];
		}
		if ($this->smtp['organization']) {
			$this->mailer->AddCustomHeader('Organization', $this->smtp['organization']);
		}
		if ($this->smtp['authType']) {
			$this->mailer->AuthType = $this->smtp['authType'];
		}
		if ($this->smtp['authProvider']) {
			$this->mailer->setOAuth($this->smtp['authProvider']);
		}
	}

	/**
	 * Set subject.
	 *
	 * @param string $subject
	 *
	 * @return $this mailer object itself
	 */
	public function subject($subject)
	{
		$this->params['subject'] = $this->mailer->Subject = $subject;
		return $this;
	}

	/**
	 * Creates a message from an HTML string, making modifications for inline images and backgrounds and creates a plain-text version by converting the HTML.
	 *
	 * @param text $message
	 *
	 * @see \PHPMailer::MsgHTML()
	 *
	 * @return $this mailer object itself
	 */
	public function content($message)
	{
		$this->params['body'] = $message;
		// Modification of the following condition will violate the license!
		if (!\App\YetiForce\Shop::check('YetiForceDisableBranding')) {
			$message .= '<table style="font-size:9px;width:100%; margin: 0;"><tbody><tr><td style="width:50%;text-align: center;">Powered by YetiForce</td></tr></tbody></table>';
		}
		$this->mailer->isHTML(true);
		$this->mailer->msgHTML($message);
		return $this;
	}

	/**
	 * Set the From and FromName properties.
	 *
	 * @param string $address
	 * @param string $name
	 *
	 * @return $this mailer object itself
	 */
	public function from($address, $name = '')
	{
		$this->params['from'][$address] = $name;
		$this->mailer->setFrom($address, $name, false);

		return $this;
	}

	/**
	 * Add a "To" address.
	 *
	 * @param string $address The email address to send to
	 * @param string $name
	 *
	 * @return $this mailer object itself
	 */
	public function to($address, $name = '')
	{
		$this->params['to'][$address] = $name;
		$this->mailer->addAddress($address, $name);
		return $this;
	}

	/**
	 * Add a "CC" address.
	 *
	 * @note: This function works with the SMTP mailer on win32, not with the "mail" mailer.
	 *
	 * @param string $address The email address to send to
	 * @param string $name
	 *
	 * @return $this mailer object itself
	 */
	public function cc($address, $name = '')
	{
		$this->params['cc'][$address] = $name;
		$this->mailer->addCC($address, $name);
		return $this;
	}

	/**
	 * Add a "BCC" address.
	 *
	 * @note: This function works with the SMTP mailer on win32, not with the "mail" mailer.
	 *
	 * @param string $address The email address to send to
	 * @param string $name
	 *
	 * @return $this mailer object itself
	 */
	public function bcc($address, $name = '')
	{
		$this->params['bcc'][$address] = $name;
		$this->mailer->addBCC($address, $name);
		return $this;
	}

	/**
	 * Add a "Reply-To" address.
	 *
	 * @param string $address The email address to reply to
	 * @param string $name
	 *
	 * @return $this mailer object itself
	 */
	public function replyTo($address, $name = '')
	{
		$this->params['replyTo'][$address] = $name;
		$this->mailer->addReplyTo($address, $name);
		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 *
	 * @param string $path Path to the attachment
	 * @param string $name Overrides the attachment name
	 *
	 * @return $this mailer object itself
	 */
	public function attachment($path, $name = '')
	{
		$this->params['attachment'][$path] = $name;
		$this->mailer->addAttachment($path, $name);
		return $this;
	}

	/**
	 * Create a message and send it.
	 *
	 * @return bool
	 */
	public function send(): bool
	{
		if (static::$error) {
			return false;
		}
		if ($this->debug) {
			$this->mailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
			$this->mailer->Debugoutput = function ($str, $level) {
				if (false !== mb_stripos($str, 'error') || false !== mb_stripos($str, 'failed')) {
					static::$error[] = trim($str);
					Log::error(trim($str), 'Mailer');
				} else {
					Log::trace(trim($str), 'Mailer');
				}
			};
		}
		$eventHandler = new EventHandler();
		$eventHandler->setParams(['mailer' => $this]);
		$eventHandler->trigger('MailerBeforeSend');

		$toAddresses = $this->mailer->From . ' >> ' . \print_r($this->mailer->getToAddresses(), true);
		\App\Log::beginProfile("Mailer::send|{$toAddresses}", 'Mail|SMTP');

		if ($this->mailer->send()) {
			\App\Log::endProfile("Mailer::send|{$toAddresses}", 'Mail|SMTP');
			if (!empty($this->smtp['save_send_mail'])) {
				$this->saveMail();
			}
			Log::trace('Mailer sent mail', 'Mailer');
			$eventHandler->trigger('MailerAfterSend');
			return true;
		}
		\App\Log::endProfile("Mailer::send|{$toAddresses}", 'Mail|SMTP');
		Log::error('Mailer Error: ' . \print_r($this->mailer->ErrorInfo, true), 'Mailer');
		if (!empty(static::$error)) {
			static::$error[] = '########################################';
		}
		if (\is_array($this->mailer->ErrorInfo)) {
			foreach ($this->mailer->ErrorInfo as $error) {
				static::$error[] = $error;
			}
		} else {
			static::$error[] = $this->mailer->ErrorInfo;
		}
		$eventHandler->trigger('MailerAfterSendError');
		return false;
	}

	/**
	 * Send mail by row queue.
	 *
	 * @param array $rowQueue
	 *
	 * @return bool
	 */
	public static function sendByRowQueue($rowQueue)
	{
		if ('demo' === \App\Config::main('systemMode')) {
			return true;
		}
		$mailer = (new self())->loadSmtpByID($rowQueue['smtp_id'])->subject($rowQueue['subject'])->content($rowQueue['content']);
		if ($rowQueue['from']) {
			$from = Json::decode($rowQueue['from']);
			$mailer->from($from['email'], $from['name']);
		}
		foreach (['cc', 'bcc'] as $key) {
			if ($rowQueue[$key]) {
				foreach (Json::decode($rowQueue[$key]) as $email => $name) {
					if (is_numeric($email)) {
						$email = $name;
						$name = '';
					}
					$mailer->{$key}($email, $name);
				}
			}
		}
		$status = false;
		$attachmentsToRemove = $update = [];
		if ($rowQueue['attachments']) {
			$attachments = Json::decode($rowQueue['attachments']);
			if (isset($attachments['ids'])) {
				$attachments = array_merge($attachments, Mail::getAttachmentsFromDocument($attachments['ids']));
				unset($attachments['ids']);
			}
			foreach ($attachments as $path => $name) {
				if (is_numeric($path)) {
					$path = $name;
					$name = '';
				}
				$mailer->attachment($path, $name);
				if (strpos(realpath($path), 'cache' . \DIRECTORY_SEPARATOR)) {
					$attachmentsToRemove[] = $path;
				}
			}
		}
		if (!empty($rowQueue['params'])) {
			$mailer->setCustomParams(Json::decode($rowQueue['params']));
		}
		foreach (Json::decode($rowQueue['to']) as $email => $name) {
			if (is_numeric($email)) {
				$email = $name;
				$name = '';
			}
			$mailer->to($email, $name);
		}
		$status = $mailer->send();
		unset($mailer);
		$db = Db::getInstance('admin');
		if ($status) {
			$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
			foreach ($attachmentsToRemove as $file) {
				unlink($file);
			}
		} else {
			$update['status'] = 2;
			$update['error'] = implode(PHP_EOL, static::$error);
			$db->createCommand()->update('s_#__mail_queue', $update, ['id' => $rowQueue['id']])->execute();
		}
		return $status;
	}

	/**
	 * Adding additional parameters.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function setCustomParams(array $params): void
	{
		$this->params['params'] = $params;
		if (isset($this->params['ics'])) {
			$this->mailer->Ical = $this->params['ics'];
		}
	}

	/**
	 * Get additional parameters.
	 *
	 * @return array
	 */
	public function getCustomParams(): array
	{
		return $this->params;
	}

	/**
	 * Save sent email.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function saveMail()
	{
		$response = false;
		if (empty($this->smtp['imap_username']) || empty($this->smtp['imap_password']) || empty($this->smtp['imap_host']) || empty($this->smtp['imap_folder'])) {
			Log::error('Mailer Error: No imap data entered', 'Mailer');
			static::$error[] = 'Mailer Error: No imap data entered' . print_r([
				$this->smtp['imap_username'],
				$this->smtp['imap_password'],
				$this->smtp['imap_host'],
				$this->smtp['imap_folder']
			], true);
			return $response;
		}

		$folderName = $this->smtp['imap_folder'];
		try {
			if ($this->smtp['mail_account']) {
				$imap = \App\Mail\Account::getInstanceById((int) $this->smtp['mail_account'])->openImap();
			} else {
				$imap = new \App\Mail\Connections\Imap([
					'host' => $this->smtp['imap_host'],
					'port' => $this->smtp['imap_port'],
					'encryption' => $this->smtp['imap_encrypt'],
					'validate_cert' => (bool) $this->smtp['imap_validate_cert'],
					'authentication' => null,
					'username' => $this->smtp['imap_username'],
					'password' => $this->smtp['imap_password']
				]);
				$imap->connect();
			}

			\App\Log::beginProfile(__METHOD__ . '|imap_append', 'Mail|IMAP');
			$response = $imap->appendMessage($folderName, $this->mailer->getSentMIMEMessage(), ['Seen']);
			\App\Log::endProfile(__METHOD__ . '|imap_append', 'Mail|IMAP');
		} catch (\Throwable $th) {
			static::$error[] = 'IMAP error - ' . $th->getMessage();
			Log::error('Mailer Error: IMAP error - ' . $th->getMessage(), 'Mailer');
		}

		return $response;
	}
}
