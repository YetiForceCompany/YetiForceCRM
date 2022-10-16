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
	 * @see self::MAIL_TYPES,
	 */
	protected $mailType;

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
	public function getMailCrmId(int $userId)
	{
		if (!$this->mailCrmId) {
			if (empty($this->getMsgId()) || \Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
				$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1);
			} else {
				$queryGenerator = new \App\QueryGenerator('OSSMailView');
				$queryGenerator->permissions = false;
				$query = $queryGenerator->setFields(['id'])->addNativeCondition(['vtiger_ossmailview.cid' => $this->getUniqueId()])
					->addCondition('assigned_user_id', $userId, 'e')->setLimit(1)->createQuery();
			}
			$this->mailCrmId = $query->scalar() ?: null;
		}

		return $this->mailCrmId;
	}

	public function setMailCrmId(int $mailCrmId)
	{
		$this->mailCrmId = $mailCrmId;
		return $this;
	}

	public function setProcessData(string $action, $value)
	{
		$this->processData[$action] = $value;
		return $this;
	}

	/**
	 * Generation crm unique id.
	 *
	 * @return string
	 */
	public function getUniqueId()
	{
		if (!$this->has('cid')) {
			$uid = hash('sha256', implode(',', $this->getEmail('from')) . '|' . $this->getDate() . '|' . $this->getHeader('subject') . '|' . $this->getMsgId());
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
		$attr = $this->message->header->get('message_id');
		return $attr ? $attr->first() : '';
	}

	/**
	 * Get uid.
	 *
	 * @return int
	 */
	public function getMsgUid(): int
	{
		return $this->message->getUid();
	}

	public function getHeader(string $key): string
	{
		$attr = $this->message->header->get($key);
		return $attr ? $attr->__toString() : '';
	}

	public function getEmail(string $key): array
	{
		$attr = $this->message->header->get($key);
		return $attr ? array_map(fn ($data) => $data->mail, $attr->all()) : [];
	}

	public function getHeaderAsArray(string $key): array
	{
		$attr = $this->message->header->get($key);
		return $attr ? $attr->toArray() : [];
	}

	public function getHeaderRaw(): string
	{
		return $this->message->header->raw;
	}

	public function getDate()
	{
		$attr = $this->message->header->get('date');
		return $attr ? $attr->toDate()->toDateTimeString() : '';
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

	public function getBodyRaw()
	{
		return $this->hasHTMLBody() ? $this->message->getHTMLBody() : $this->message->getTextBody();
	}

	public function setBody(string $body)
	{
		$this->body = $body;
		return $this;
	}

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
		// if (!\App\Utils::isHtml($html) || !$this->get('isHtml')) {
		// 	$html = nl2br($html);
		// }
		// $attachments = $this->get('attachments');
		// if (\Config\Modules\OSSMailScanner::$attachHtmlAndTxtToMessageBody && \count($attachments) < 2) {
		// 	foreach ($attachments as $key => $attachment) {
		// 		if (('.html' === substr($attachment['filename'], -5)) || ('.txt' === substr($attachment['filename'], -4))) {
		// 			$html .= $attachment['attachment'] . '<hr />';
		// 			unset($attachments[$key]);
		// 		}
		// 	}
		// }
		// $encoding = mb_detect_encoding($html, mb_list_encodings(), true);
		// if ($encoding && 'UTF-8' !== $encoding) {
		// 	$html = mb_convert_encoding($html, 'UTF-8', $encoding);
		// }
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

	public function hasAttachments(): bool
	{
		$this->getBody(false);
		return !empty($this->files) || $this->message->hasAttachments();
	}

	public function getAttachments(): array
	{
		foreach ($this->message->getAttachments() as $attachment) {
			$this->files[$attachment->id] = \App\Fields\File::loadFromContent($attachment->getContent(), $attachment->getName(), ['validateAllCodeInjection' => true, 'id' => $attachment->id]);
			$this->message->getAttachments()->forget($attachment->id);
		}

		return $this->files;
	}

	public function getDocuments(): array
	{
		return $this->documents;
	}

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
