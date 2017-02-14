<?php
namespace App;

/**
 * Mailer basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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

	/** @var \PHPMailer PHPMailer instance */
	protected $mailer;

	/** @var array SMTP configuration */
	protected $smtp;

	/** @var array Error logs */
	protected $error;

	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->mailer = new \PHPMailer();
		if (\AppConfig::debug('MAILER_DEBUG')) {
			$this->mailer->SMTPDebug = 2;
			$this->mailer->Debugoutput = function($str, $level) {
				if (strpos(strtolower($str), 'error') !== false || strpos(strtolower($str), 'failed') !== false) {
					Log::error(trim($str), 'Mailer');
				} else {
					Log::trace(trim($str), 'Mailer');
				}
			};
		}
		$this->mailer->XMailer = 'YetiForceCRM mailer';
		$this->mailer->Hostname = 'YetiForceCRM';
	}

	/**
	 * Load configuration smtp by id
	 * @param int $smtpId Smtp ID
	 * @return $this mailer object itself
	 */
	public function loadSmtpByID($smtpId)
	{
		$this->smtp = Mail::getSmtpById($smtpId);
		$this->setSmtp();
		return $this;
	}

	/**
	 * Load configuration smtp
	 * @param array $smtpInfo
	 * @return $this mailer object itself
	 */
	public function loadSmtp($smtpInfo)
	{
		$this->smtp = $smtpInfo;
		$this->setSmtp();
		return $this;
	}

	/**
	 * 
	 * @param array $params
	 * @return boolean
	 */
	public static function sendFromTemplate($params)
	{
		if (empty($params['template'])) {
			return false;
		}
		$recordModel = false;
		if (empty($params['recordModel'])) {
			$moduleName = isset($params['moduleName']) ? $params['moduleName'] : null;
			if (isset($params['recordId'])) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($params['recordId'], $moduleName);
			}
		} else {
			$recordModel = $params['recordModel'];
		}
		$template = Mail::getTemplete($params['template']);
		if (!$template) {
			return false;
		}

		$textParser = $recordModel ? TextParser::getInstanceByModel($recordModel) : TextParser::getInstance(isset($params['moduleName']) ? $params['moduleName'] : '');
		if (!empty($params['language'])) {
			$textParser->setLanguage($params['language']);
		}
		$textParser->setParams(array_diff_key($params, array_flip(['subject', 'content', 'attachments', 'recordModel'])));
		$params['subject'] = $textParser->setContent($template['subject'])->parse()->getContent();
		$params['content'] = $textParser->setContent($template['content'])->parse()->getContent();
		unset($textParser);
		if (empty($params['smtp_id'])) {
			$params['smtp_id'] = $template['smtp_id'];
		}
		if (isset($template['attachments'])) {
			$params['attachments'] = array_merge(empty($params['attachments']) ? [] : $params['attachments'], $template['attachments']);
		}
		static::addMail(array_intersect_key($params, array_flip(static::$quoteColumn)));
		return true;
	}

	/**
	 * Add mail to quote for send
	 * @param array $params
	 */
	public static function addMail($params)
	{
		$params['status'] = \AppConfig::module('Mail', 'MAILER_REQUIRED_ACCEPTATION_BEFORE_SENDING') ? 0 : 1;
		if (empty($params['smtp_id'])) {
			$params['smtp_id'] = Mail::getDefaultSmtp();
		}
		if (empty($params['owner'])) {
			$owner = User::getCurrentUserRealId();
			$params['owner'] = $owner ? $owner : 0;
		}
		$params['date'] = date('Y-m-d H:i:s');
		foreach (static::$quoteJsonColumn as $key) {
			if (isset($params[$key])) {
				if (!is_array($params[$key])) {
					$params[$key] = [$params[$key]];
				}
				$params[$key] = Json::encode($params[$key]);
			}
		}
		\App\Db::getInstance('admin')->createCommand()->insert('s_#__mail_queue', $params)->execute();
	}

	/**
	 * Get configuration smtp
	 * @param string|bool $key
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
	 * Set configuration smtp in mailer
	 */
	public function setSmtp()
	{
		if (!$this->smtp) {
			throw new Exceptions\AppException('ERR_NO_SMTP_CONFIGURATION');
		}
		switch ($this->smtp['mailer_type']) {
			case 'smtp': $this->mailer->isSMTP();
				break;
			case 'sendmail': $this->mailer->isSendmail();
				break;
			case 'mail': $this->mailer->isMail();
				break;
			case 'qmail': $this->mailer->isQmail();
				break;
		}
		$this->mailer->Host = $this->smtp['host'];
		if (!empty($this->smtp['port'])) {
			$this->mailer->Port = $this->smtp['port'];
		}
		$this->mailer->SMTPSecure = $this->smtp['secure'];
		$this->mailer->SMTPAuth = (bool) $this->smtp['authentication'];
		$this->mailer->Username = $this->smtp['username'];
		$this->mailer->Password = $this->smtp['password'];
		if ($this->smtp['options']) {
			$this->mailer->SMTPOptions = $this->smtp['options'];
		}
		if ($this->smtp['from_email']) {
			$this->mailer->From = $this->smtp['from_email'];
		}
		if ($this->smtp['from_name']) {
			$this->mailer->FromName = $this->smtp['from_name'];
		}
		if ($this->smtp['replay_to']) {
			$this->mailer->addReplyTo($this->smtp['replay_to']);
		}
	}

	/**
	 * Set subject
	 * @param string $subject
	 * @return $this mailer object itself
	 */
	public function subject($subject)
	{
		$this->mailer->Subject = $subject;
		return $this;
	}

	/**
	 * Creates a message from an HTML string, making modifications for inline images and backgrounds and creates a plain-text version by converting the HTML
	 * @param text $message
	 * @see \PHPMailer::MsgHTML()
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
	 * @param string $address
	 * @param string $name
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
	 * @param string $address The email address to send to
	 * @param string $name
	 * @return $this mailer object itself
	 */
	public function to($address, $name = '')
	{
		$this->mailer->addAddress($address, $name);
		return $this;
	}

	/**
	 * Add a "CC" address.
	 * @note: This function works with the SMTP mailer on win32, not with the "mail" mailer.
	 * @param string $address The email address to send to
	 * @param string $name
	 * @return $this mailer object itself
	 */
	public function cc($address, $name = '')
	{
		$this->mailer->addCC($address, $name);
		return $this;
	}

	/**
	 * Add a "BCC" address.
	 * @note: This function works with the SMTP mailer on win32, not with the "mail" mailer.
	 * @param string $address The email address to send to
	 * @param string $name
	 * @return $this mailer object itself
	 */
	public function bcc($address, $name = '')
	{
		$this->mailer->addBCC($address, $name);
		return $this;
	}

	/**
	 * Add a "Reply-To" address.
	 * @param string $address The email address to reply to
	 * @param string $name
	 * @return $this mailer object itself
	 */
	public function replyTo($address, $name = '')
	{
		$this->mailer->addReplyTo($address, $name);
		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * @param string $path Path to the attachment.
	 * @param string $name Overrides the attachment name.
	 * @return $this mailer object itself
	 */
	public function attachment($path, $name = '')
	{
		$this->mailer->addAttachment($path, $name);
		return $this;
	}

	/**
	 * Create a message and send it.
	 * @return boolean
	 */
	public function send()
	{
		if ($this->mailer->FromName === 'Root User') {
			$this->mailer->FromName = Company::getInstanceById()->get('name');
		}
		if ($this->mailer->send()) {
			Log::trace('Mailer sent mail', 'Mailer');
			return true;
		} else {
			Log::error('Mailer Error: ' . $this->mailer->ErrorInfo, 'Mailer');
		}
		return false;
	}

	/**
	 * Check connection
	 * @return array
	 */
	public function test()
	{
		$this->mailer->SMTPDebug = 2;
		$this->error = [];
		$this->mailer->Debugoutput = function($str, $level) {
			if (strpos(strtolower($str), 'error') !== false || strpos(strtolower($str), 'failed') !== false) {
				$this->error[] = trim($str);
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
		return ['result' => $this->send(), 'error' => implode(PHP_EOL, $this->error)];
	}

	/**
	 * Send mail by row queue
	 * @param array $rowQueue
	 * @return boolean
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
		if ($rowQueue['params']) {
			foreach (Json::decode($rowQueue['params']) as $name => $param) {
				$this->sendCustomParams($name, $param, $mailer);
			}
		}
		if ($mailer->getSmtp('individual_delivery')) {
			foreach (Json::decode($rowQueue['to']) as $email => $name) {
				$separateMailer = clone $mailer;
				if (is_numeric($email)) {
					$email = $name;
					$name = '';
				}
				$separateMailer->to($email, $name);
				$status = $separateMailer->send();
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
		}
		if ($status) {
			foreach ($attachmentsToRemove as $file) {
				unlink($file);
			}
		}
		return $status;
	}

	/**
	 * Adding additional parameters
	 * @param string $name
	 * @param mixed $param
	 * @param self $mailer
	 */
	public function sendCustomParams($name, $param, $mailer)
	{
		switch ($name) {
			case 'ics':
				$mailer->mailer->Ical = $param;
				break;
		}
	}
}
