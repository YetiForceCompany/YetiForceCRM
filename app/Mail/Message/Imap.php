<?php
/**
 * Mail outlook message file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail\Message;

/**
 * Mail outlook message class.
 */
class Imap extends Base
{
	/**
	 * Scanner engine name.
	 *
	 * @var string
	 */
	public $name = 'Imap';
	public $processData = [];
	protected $actions = [];
	protected $body;
	protected $mailCrmId;
	protected $documents = [];
	protected $attachments = [];
	/**
	 * @var int Mail type
	 *
	 * @see App\Mail\Message\Imap::MAIL_TYPES,
	 */
	protected $mailType;

	/**
	 * Get instance by crm mail ID.
	 *
	 * @param int $crmId
	 *
	 * @return \self
	 */
	public static function getInstanceById(int $crmId)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($crmId);
		$instance = new static();
		$instance->set('uid', $recordModel->get('uid'))
			->set('message_id', $recordModel->get('msgid'))
			->set('date', $recordModel->get('date'))
			->set('from', explode(',', $recordModel->get('from_email')))
			->set('to', explode(',', $recordModel->get('to_email')))
			->set('cc', array_filter(explode(',', $recordModel->get('cc_email'))))
			->set('bcc', array_filter(explode(',', $recordModel->get('bcc_email'))))
			->set('reply_to', array_filter(explode(',', $recordModel->get('reply_to_email'))))
			->set('cid', $recordModel->get('cid'));
		$instance->mailType = $recordModel->get('type');
		$instance->mailCrmId = $crmId;
		$instance->body = $recordModel->get('content');

		return $instance;
	}

	/**
	 * Set third-party message object.
	 *
	 * @param object $message
	 *
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/** {@inheritdoc} */
	public function getMailCrmId(int $mailAccountId)
	{
		if (!$this->mailCrmId) {
			if (empty($this->getMsgId()) || \Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
				$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1);
			} else {
				$queryGenerator = new \App\QueryGenerator('OSSMailView');
				$queryGenerator->permissions = false;
				$query = $queryGenerator->setFields(['id'])->addNativeCondition(['vtiger_ossmailview.cid' => $this->getUniqueId()])
					->addCondition('rc_user', $mailAccountId, 'e')->setLimit(1)->createQuery();
			}
			$this->mailCrmId = $query->scalar() ?: null;
		}

		return $this->mailCrmId;
	}

	/**
	 * Find crm ID by cid.
	 *
	 * @return int|null
	 */
	public function getMailCrmIdByCid()
	{
		return (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1)->scalar() ?: null;
	}

	/**
	 * Set mail record ID.
	 *
	 * @param int $mailCrmId
	 *
	 * @return $this
	 */
	public function setMailCrmId(int $mailCrmId)
	{
		$this->mailCrmId = $mailCrmId;
		return $this;
	}

	/**
	 * Set process data.
	 *
	 * @param string $action
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setProcessData(string $action, $value)
	{
		$this->processData[$action] = $value;
		return $this;
	}

	/**
	 * Get process data.
	 *
	 * @param string $action
	 *
	 * @return mixed
	 */
	public function getProcessData(string $action = '')
	{
		return $action ? $this->processData[$action] ?? [] : [];
	}

	/**
	 * Generation crm unique id.
	 *
	 * @return string
	 */
	public function getUniqueId()
	{
		if (!$this->has('cid')) {
			$uid = hash('sha256', implode(',', $this->getEmail('from')) . '|' . $this->getDate() . '|' . $this->getSubject() . '|' . $this->getMsgId());
			$this->set('cid', $uid);
		}

		return $this->get('cid');
	}

	/**
	 * Get message_id from header.
	 *
	 * @return string
	 */
	public function getMsgId(): string
	{
		if (!$this->has('message_id')) {
			$attr = $this->message->header->get('message_id');
			$this->set('message_id', $attr ? $attr->first() : '');
		}

		return $this->get('message_id');
	}

	/**
	 * Get uid.
	 *
	 * @return int
	 */
	public function getMsgUid(): int
	{
		if (!$this->has('uid')) {
			$this->set('uid', $this->message->getUid());
		}

		return $this->get('uid');
	}

	/**
	 * Get subject.
	 *
	 * @return string
	 */
	public function getSubject(): string
	{
		if (!$this->has('subject')) {
			$this->set('subject', $this->getHeader('subject'));
		}

		return $this->get('subject');
	}

	/**
	 * Get header data.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getHeader(string $key): string
	{
		$attr = $this->message->header->get($key);
		return $attr ? $attr->__toString() : '';
	}

	/**
	 * Get emials by key.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getEmail(string $key): array
	{
		if (!$this->has($key)) {
			$attr = $this->message->header->get($key);
			$this->set($key, $attr ? array_map(fn ($data) => $data->mail, $attr->all()) : []);
		}

		return $this->get($key);
	}

	/**
	 * Get array header data by key.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getHeaderAsArray(string $key): array
	{
		$attr = $this->message->header->get($key);
		return $attr ? $attr->toArray() : [];
	}

	/**
	 * Get header war data.
	 *
	 * @return string
	 */
	public function getHeaderRaw(): string
	{
		return $this->message->header->raw;
	}

	/**
	 * Get date.
	 *
	 * @return string
	 */
	public function getDate()
	{
		if (!$this->has('date')) {
			$attr = $this->message->header->get('date');
			$this->set('date', $attr ? $attr->toDate()->toDateTimeString() : '');
		}

		return $this->get('date');
	}

	/**
	 * Set a given flag.
	 *
	 * @param string $flag
	 *
	 * @return $this
	 */
	public function setFlag(string $flag)
	{
		$this->message->setFlag($flag);
		return $this;
	}

	/** {@inheritdoc} */
	public function getMailType(): int
	{
		if (null === $this->mailType) {
			$to = false;
			$from = (bool) \App\Mail\RecordFinder::findUserEmail($this->getEmail('from'));
			foreach (['to', 'cc', 'bcc'] as $header) {
				if ($emails = $this->getEmail($header)) {
					$to = (bool) \App\Mail\RecordFinder::findUserEmail($emails);
					break;
				}
			}

			$key = self::MAIL_TYPE_RECEIVED;
			if ($from && $to) {
				$key = self::MAIL_TYPE_INTERNAL;
			} elseif ($from) {
				$key = self::MAIL_TYPE_SENT;
			}
			$this->mailType = $key;
		}

		return $this->mailType;
	}

	/**
	 * Get first letter form email.
	 *
	 * @return void
	 */
	public function getFirstLetter()
	{
		return strtoupper(\App\TextUtils::textTruncate(trim(implode(',', $this->getEmail('from'))), 1, false));
	}

	/**
	 * Check if the Message has a html body.
	 *
	 * @return bool
	 */
	public function hasHTMLBody(): bool
	{
		return $this->message->hasHTMLBody();
	}

	/**
	 * Get the Message  body.
	 *
	 * @param bool $purify
	 *
	 * @return string
	 */
	public function getBody(bool $purify = true)
	{
		if (null === $this->body) {
			if ($this->hasHTMLBody()) {
				$this->body = $this->message->getHTMLBody();
				$this->parseBody();
			} else {
				$this->body = $this->message->getTextBody() ?? '';
			}
		}

		return $purify ? \App\Purifier::decodeHtml(\App\Purifier::purifyHtml($this->body)) : $this->body;
	}

	/**
	 * Get body raw.
	 *
	 * @return string
	 */
	public function getBodyRaw()
	{
		return $this->hasHTMLBody() ? $this->message->getHTMLBody() : $this->message->getTextBody();
	}

	/**
	 * Set body.
	 *
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body)
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * Get forlder name (utf8).
	 *
	 * @return string
	 */
	public function getFolderName(): string
	{
		return $this->message->getFolder()->full_name;
	}

	/**
	 * Treatment mail content with all images and unnecessary trash.
	 */
	private function parseBody()
	{
		$html = $this->body;
		$html = preg_replace(
			[':<(head|style|script).+?</\1>:is', // remove <head>, <styleand <scriptsections
				':<!\[[^]<]+\]>:', // remove <![if !mso]and friends
				':<!DOCTYPE[^>]+>:', // remove <!DOCTYPE ... >
				':<\?[^>]+>:', // remove <?xml version="1.0" ... >
				'~</?html[^>]*>~', // remove html tags
				'~</?body[^>]*>~', // remove body tags
				'~</?o:[^>]*>~', // remove mso tags
				'~\sclass=[\'|\"][^\'\"]+[\'|\"]~i', // remove class attributes
			], ['', '', '', '', '', '', '', ''], $html);
		$doc = new \DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(true);
		$doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);

		$imgs = $doc->getElementsByTagName('img');
		$lenght = $imgs->length;
		while ($imgs->length && $lenght) {
			--$lenght;
			$this->getFileFromImage($imgs->item(0));
		}

		$previousValue = libxml_use_internal_errors(true);
		$html = $doc->saveHTML();
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);
		$html = str_replace('<?xml encoding="utf-8"?>', '', $html);

		$this->body = $html;
	}

	/**
	 * Check if mail has attachments.
	 *
	 * @return bool
	 */
	public function hasAttachments(): bool
	{
		$this->getBody(false);
		return !empty($this->files) || $this->message->hasAttachments();
	}

	/**
	 * Get attachments.
	 *
	 * @return array
	 */
	public function getAttachments(): array
	{
		foreach ($this->message->getAttachments() as $attachment) {
			$this->files[$attachment->id] = \App\Fields\File::loadFromContent($attachment->getContent(), $attachment->getName(), ['validateAllCodeInjection' => true, 'id' => $attachment->id]);
			$this->message->getAttachments()->forget($attachment->id);
		}

		return $this->files;
	}

	/**
	 * Get documents.
	 *
	 * @return array
	 */
	public function getDocuments(): array
	{
		return $this->documents;
	}

	/**
	 * Add attachemtns to CRM.
	 *
	 * @param array $docData
	 *
	 * @return void
	 */
	public function saveAttachments(array $docData)
	{
		$this->getBody(false);
		$useTime = $this->getDate();
		$userId = \App\User::getCurrentUserRealId();

		$params = array_merge([
			'created_user_id' => $userId,
			'assigned_user_id' => $userId,
			'modifiedby' => $userId,
			'createdtime' => $useTime,
			'modifiedtime' => $useTime,
			'folderid' => 'T2',
		], $docData);

		$maxSize = \App\Config::getMaxUploadSize();
		foreach ($this->getAttachments() as $key => $file) {
			if ($maxSize < ($size = $file->getSize())) {
				\App\Log::error("Error - downloaded the file is too big '{$file->getName()}', size: {$size}, in mail: {$this->getDate()} | Folder: {$this->getFolderName()} | ID: {$this->getMsgUid()}", __CLASS__);
				continue;
			}
			if ($file->validateAndSecure() && ($id = \App\Fields\File::saveFromContent($file, $params))) {
				$this->documents[$key] = $id;
				$this->setBody(str_replace(["crm-id=\"{$key}\"", "attachment-id=\"{$key}\""], ["crm-id=\"{$id['crmid']}\"", "attachment-id=\"{$id['attachmentsId']}\""], $this->getBody(false)));
			} else {
				\App\Log::error("Error downloading the file '{$file->getName()}' in mail: {$this->getDate()} | Folder: {$this->getFolderName()} | ID: {$this->getMsgUid()}", __CLASS__);
			}
		}
	}

	/**
	 * Get file from image.
	 *
	 * @param DOMElement $element
	 *
	 * @return array
	 */
	private function getFileFromImage(\DOMElement $element)
	{
		$src = trim($element->getAttribute('src'), '\'');
		$element->removeAttribute('src');
		$file = [];
		if ('data:' === substr($src, 0, 5)) {
			$file = \App\Fields\File::saveFromString($src, ['validateAllowedFormat' => 'image']);
		} elseif (filter_var($src, FILTER_VALIDATE_URL)) {
			if (\Config\Modules\OSSMailScanner::$attachMailBodyGraphicUrl ?? true) {
				$file = \App\Fields\File::loadFromUrl($src, ['validateAllowedFormat' => 'image']);
				if (!$file->validateAndSecure()) {
					$file = [];
				}
			} else {
				$file = ['url' => $src];
			}
		} elseif ('cid:' === substr($src, 0, 4)) {
			$src = substr($src, 4);
			if ($this->message->getAttachments()->has($src)) {
				$attachment = $this->message->getAttachments()->get($src);
				$fileInstance = \App\Fields\File::loadFromContent($attachment->getContent(), $attachment->getName(), ['validateAllowedFormat' => 'image']);
				if ($fileInstance && $fileInstance->validateAndSecure()) {
					$file = $fileInstance;
					$this->message->getAttachments()->forget($src);
				}
			} else {
				\App\Log::warning("There is no attachment with ID: $src , in mail: {$this->getDate()} | Folder: {$this->getFolderName()} | ID: {$this->message->getMsgUid()}", __CLASS__);
			}
		} else {
			\App\Log::warning("Unsupported photo type, requires verification. ID: $src , in mail: {$this->getDate()} | Folder: {$this->getFolderName()} | ID: {$this->message->getMsgUid()}", __CLASS__);
		}
		if ($file) {
			$yetiforceTag = $element->ownerDocument->createElement('yetiforce');
			if ($file instanceof \App\Fields\File) {
				$key = sha1($file->getPath());
				$yetiforceTag->setAttribute('type', 'Documents');
				$yetiforceTag->setAttribute('crm-id', $key);
				$yetiforceTag->setAttribute('attachment-id', $key);
				if ($element->hasAttribute('width')) {
					$yetiforceTag->setAttribute('width', \App\Purifier::encodeHtml($element->getAttribute('width')));
				}
				if ($element->hasAttribute('height')) {
					$yetiforceTag->setAttribute('height', \App\Purifier::encodeHtml($element->getAttribute('height')));
				}
				$this->files[$key] = $file;
			} else {
				$yetiforceTag->textContent = $file['url'];
			}
		} else {
			$yetiforceTag = $element->cloneNode(true);
		}

		$element->parentNode->replaceChild($yetiforceTag, $element);
	}
}
