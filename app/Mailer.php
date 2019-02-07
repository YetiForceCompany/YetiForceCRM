<?php

namespace App;

/**
 * Mailer basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Mailer
{
	/** @var string[] Queue status */
	public static $statuses = [
		0 => 'LBL_PENDING_ACCEPTANCE',
		1 => 'LBL_WAITING_TO_BE_SENT',
		2 => 'LBL_ERROR_DURING_SENDING',
	];
	public static $quoteJsonColumn = ['to', 'cc', 'bcc', 'attachments', 'params'];
	public static $quoteColumn = ['smtp_id', 'date', 'owner', 'status', 'from', 'subject', 'content', 'to', 'cc', 'bcc', 'attachments', 'priority', 'params'];

	/** @var \PHPMailer\PHPMailer\PHPMailer PHPMailer instance */
	protected $mailer;

	/** @var array SMTP configuration */
	protected $smtp;

	/** @var array Error logs */
	public static $error;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		static::$error = [];
		$this->mailer = new \PHPMailer\PHPMailer\PHPMailer(false);
		if (\AppConfig::debug('MAILER_DEBUG')) {
			$this->mailer->SMTPDebug = 2;
			$this->mailer->Debugoutput = function ($str, $level) {
				if (strpos(strtolower($str), 'error') !== false || strpos(strtolower($str), 'failed') !== false) {
					Log::error(trim($str), 'Mailer');
				} else {
					Log::trace(trim($str), 'Mailer');
				}
			};
		}
		$this->mailer->XMailer = 'YetiForceCRM Mailer';
		$this->mailer->Hostname = 'YetiForceCRM';
		$this->mailer->FromName = 'YetiForce Mailer';
		$this->mailer->CharSet = \AppConfig::main('default_charset');
	}

	/**
	 * Load configuration smtp by id.
	 *
	 * @param int $smtpId Smtp ID
	 *
	 * @return $this mailer object itself
	 */
	public function loadSmtpByID($smtpId)
	{
		$this->smtp = Mail::getSmtpById($smtpId);
		$this->setSmtp();
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
		$this->smtp = $smtpInfo;
		$this->setSmtp();
		return $this;
	}

	/**
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function sendFromTemplate($params)
	{
		Log::trace('Send mail from template', 'Mailer');
		if (empty($params['template'])) {
			Log::warning('No templete', 'Mailer');
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
		}
		$template = Mail::getTemplete($params['template']);
		if (!$template) {
			Log::warning('No mail templete', 'Mailer');
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
		$params['subject'] = $textParser->setContent($template['subject'])->parse()->getContent();
		$params['content'] = $textParser->setContent($template['content'])->parse()->getContent();
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
		return static::addMail(array_intersect_key($params, array_flip(static::$quoteColumn)));
	}

	/**
	 * Add mail to quote for send.
	 *
	 * @param array $params
	 */
	public static function addMail($params)
	{
		$params['status'] = Config::component('Mail', 'MAILER_REQUIRED_ACCEPTATION_BEFORE_SENDING') ? 0 : 1;
		$params['date'] = date('Y-m-d H:i:s');
		if (empty($params['owner'])) {
			$owner = User::getCurrentUserRealId();
			$params['owner'] = $owner ? $owner : 0;
		}
		if (empty($params['smtp_id'])) {
			$params['smtp_id'] = Mail::getDefaultSmtp();
		}
		if (empty($params['smtp_id'])) {
			unset($params['priority'], $params['status']);
			$params['error_code'] = 1;
			static::insertMail($params, 'log');
			Log::warning('No SMTP configuration', 'Mailer');
			return false;
		}
		if (!\App\Mail::getSmtpById($params['smtp_id'])) {
			unset($params['priority'], $params['status']);
			$params['error_code'] = 2;
			static::insertMail($params, 'log');
			Log::warning('SMTP configuration with provided id not exists', 'Mailer');
			return false;
		}
		if (empty($params['to'])) {
			unset($params['priority'], $params['status']);
			$params['error_code'] = 3;
			static::insertMail($params, 'log');
			Log::warning('No target email address provided', 'Mailer');
			return false;
		}
		static::insertMail($params, 'admin');
		return true;
	}

	/**
	 * Save mail data in provided table.
	 *
	 * @param array  $params
	 * @param string $type   'admin' | 'log'
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function insertMail($params, $type)
	{
		foreach (static::$quoteJsonColumn as $key) {
			if (isset($params[$key])) {
				if (!\is_array($params[$key])) {
					$params[$key] = [$params[$key]];
				}
				$params[$key] = Json::encode($params[$key]);
			}
		}
		\App\Db::getInstance($type)->createCommand()->insert($type === 'admin' ? 's_#__mail_queue' : 'l_#__mail', $params)->execute();
	}

	/**
	 * Get configuration smtp.
	 *
	 * @param string|bool $key
	 *
	 * @return array
	 */
	public function getSmtp($key = false)
	{
		if ($key && isset($this->smtp[$key])) {
			return $this->smtp[$key];
		}
		return $this->smtp;
	}

	/**
	 * Set configuration smtp in mailer.
	 */
	public function setSmtp()
	{
		if (!$this->smtp) {
			throw new \App\Exceptions\AppException('ERR_NO_SMTP_CONFIGURATION');
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
		$this->mailer->SMTPAuth = isset($this->smtp['authentication']) && (bool) $this->smtp['authentication'];
		$this->mailer->Username = trim($this->smtp['username']);
		$this->mailer->Password = trim(Encryption::getInstance()->decrypt($this->smtp['password']));
		if ($this->smtp['options']) {
			$this->mailer->SMTPOptions = Json::decode($this->smtp['options'], true);
		}
		$this->mailer->From = $this->smtp['from_email'] ? $this->smtp['from_email'] : $this->smtp['username'];
		if ($this->smtp['from_name']) {
			$this->mailer->FromName = $this->smtp['from_name'];
		}
		if ($this->smtp['reply_to']) {
			$this->mailer->addReplyTo($this->smtp['reply_to']);
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
		$this->mailer->Subject = $subject;
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
		$this->mailer->From = $address;
		$this->mailer->FromName = $name;
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
		$this->mailer->addAttachment($path, $name);
		return $this;
	}

	/**
	 * Create a message and send it.
	 *
	 * @return bool
	 */
	public function send()
	{
		if ($this->mailer->send()) {
			if (empty($this->smtp['save_send_mail']) || (!empty($this->smtp['save_send_mail']) && $this->saveMail())) {
				Log::trace('Mailer sent mail', 'Mailer');
				return true;
			}
		} else {
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
		}
		return false;
	}

	/**
	 * Check connection.
	 *
	 * @return array
	 */
	public function test()
	{
		$this->mailer->SMTPDebug = 2;
		static::$error = [];
		$this->mailer->Debugoutput = function ($str, $level) {
			if (strpos(strtolower($str), 'error') !== false || strpos(strtolower($str), 'failed') !== false) {
				static::$error[] = trim($str);
				Log::error(trim($str), 'Mailer');
			} else {
				Log::trace(trim($str), 'Mailer');
			}
		};
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		$this->to($currentUser->get('email1'));
		$template = Mail::getTempleteDetail('TestMailAboutTheMailServerConfiguration');
		if (!$template) {
			return ['result' => false, 'error' => Language::translate('LBL_NO_EMAIL_TEMPLATE')];
		}
		$textParser = TextParser::getInstanceById($currentUser->getId(), 'Users');
		$this->subject($textParser->setContent($template['subject'])->parse()->getContent());
		$this->content($textParser->setContent($template['content'])->parse()->getContent());
		return ['result' => $this->send(), 'error' => implode(PHP_EOL, static::$error)];
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
		if (\AppConfig::main('systemMode') === 'demo') {
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
					$mailer->$key($email, $name);
				}
			}
		}
		$status = false;
		$attachmentsToRemove = [];
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
				if (strpos(realpath($path), 'cache' . DIRECTORY_SEPARATOR)) {
					$attachmentsToRemove[] = $path;
				}
			}
		}
		if (!empty($rowQueue['params'])) {
			foreach (Json::decode($rowQueue['params']) as $name => $param) {
				$mailer->sendCustomParams($name, $param, $mailer);
			}
		}
		if ($mailer->getSmtp('individual_delivery')) {
			foreach (Json::decode($rowQueue['to']) as $email => $name) {
				$separateMailer = $mailer->cloneMailer();
				if (is_numeric($email)) {
					$email = $name;
					$name = '';
				}
				$separateMailer->to($email, $name);
				$status = $separateMailer->send();
				unset($separateMailer);
				if (!$status) {
					return false;
				}
			}
		} else {
			foreach (Json::decode($rowQueue['to']) as $email => $name) {
				if (is_numeric($email)) {
					$email = $name;
					$name = '';
				}
				$mailer->to($email, $name);
			}
			$status = $mailer->send();
			unset($mailer);
		}
		$db = Db::getInstance('admin');
		if ($status) {
			$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
			foreach ($attachmentsToRemove as $file) {
				unlink($file);
			}
		} else {
			$db->createCommand()->update('s_#__mail_queue', [
				'status' => 2,
				'error' => implode(PHP_EOL, static::$error)
			], ['id' => $rowQueue['id']])->execute();
		}
		return $status;
	}

	/**
	 * Adding additional parameters.
	 *
	 * @param string $name
	 * @param mixed  $param
	 * @param self   $mailer
	 */
	public function sendCustomParams($name, $param, $mailer)
	{
		if ($name === 'ics') {
			$mailer->mailer->Ical = $param;
		}
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
		if (empty($this->smtp['smtp_username']) && empty($this->smtp['smtp_password']) && empty($this->smtp['smtp_host'])) {
			Log::error('Mailer Error: No smtp data entered', 'Mailer');
			return false;
		}
		$params = [
			'default_port' => $this->smtp['smtp_port'],
			'validate_cert' => !empty($this->smtp['smtp_validate_cert']),
			'imap_max_retries' => 0,
			'imap_params' => [],
			'imap_open_add_connection_type' => true,
		];
		$folder = Utils::convertCharacterEncoding($this->smtp['smtp_folder'], 'UTF-8', 'UTF7-IMAP');
		$mbox = \OSSMail_Record_Model::imapConnect($this->smtp['smtp_username'], Encryption::getInstance()->decrypt($this->smtp['smtp_password']), $this->smtp['smtp_host'], $folder, false, $params);
		if ($mbox === false && !imap_last_error()) {
			static::$error[] = 'IMAP error - ' . imap_last_error();
			Log::error('Mailer Error: IMAP error - ' . imap_last_error(), 'Mailer');
			return false;
		}
		imap_append($mbox, \OSSMail_Record_Model::$imapConnectMailbox, $this->mailer->getSentMIMEMessage(), '\\Seen');
		imap_close($mbox);
		return true;
	}

	/**
	 * Clone the mailer object for individual shipment.
	 *
	 * @return \App\Mailer
	 */
	public function cloneMailer()
	{
		$clonedThis = clone $this;
		$clonedThis->mailer = clone $this->mailer;
		return $clonedThis;
	}
}
