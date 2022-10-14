<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
class CreatedMail extends Base
{
	/** {@inheritdoc} */
	public static $priority = 2;
	/** @var App\Mail\Message\Imap */
	protected $message;
	protected $attachments = [];

	/** {@inheritdoc} */
	public function process(): void
	{
		if ($this->checkExceptions()) {
			return;
		}

		$owner = $this->account->getSource()->get('assigned_user_id');
		if ($mailCrmId = $this->message->getMailCrmId($owner)) {
			$record = \OSSMailView_Record_Model::getInstanceById($mailCrmId, 'OSSMailView');
			if ($owner !== $record->get('assigned_user_id') && !\in_array($owner, explode(',', $record->get('shownerid')))) {
				$fieldModel = $record->getField('shownerid');
				$record->set($fieldModel->getName(), $owner)->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $owner]])->save();
			}

			$this->message->setMailCrmId($record->getId());
			$this->message->setProcessData($this->getName(), ['mailViewId' => $record->getId()]);
			return;
		}

		$this->message->getBody();
		$record = \OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$record->set('assigned_user_id', $owner);
		$record->set('created_user_id', \App\User::getCurrentUserRealId());
		$record->setFromUserValue('subject', \App\TextUtils::textTruncate($this->message->getHeader('subject'), $record->getField('subject')->getMaxValue(), false));
		$record->set('to_email', implode(',', $this->message->getHeaderAsArray('to')));
		$record->set('from_email', $this->message->getHeader('from'));
		$record->set('cc_email', implode(',', $this->message->getHeaderAsArray('cc')));
		$record->set('bcc_email', implode(',', $this->message->getHeaderAsArray('bcc')));
		$record->set('reply_to_email', implode(',', $this->message->getHeaderAsArray('reply_to')));

		$record->set('date', $this->message->getDate());
		$record->set('createdtime', $this->message->getDate());
		$record->set('uid', $this->message->getMsgId());
		$type = $this->message->getMailType();
		$record->set('type', $type);
		$record->set('mid', $this->message->getMsgUid());
		$record->set('mbox', $this->message->getFolderName());
		$record->set('ossmailview_sendtype', \App\Mail\Message\Base::MAIL_TYPES[$type]);
		$record->set('orginal_mail', \App\TextUtils::htmlTruncate($this->message->getHeaderRaw(), $record->getField('orginal_mail')->getMaxValue()));
		$record->set('attachments_exist', (int) $this->message->hasAttachments());
		$record->setDataForSave(['vtiger_ossmailview' => ['cid' => $this->message->getUniqueId()]]);

		if ($this->message->hasAttachments()) {
			$this->saveAttachments();
		}
		$record->set('content', \App\TextUtils::htmlTruncate($this->message->getBody(), $record->getField('content')->getMaxValue()));
		// $record->setHandlerExceptions(['disableHandlers' => true]);
		// $toEmails = $this->message->getHeaderAsArray('to');
		// $toEmails = array_merge($toEmails, $this->message->getHeaderAsArray('cc'));
		// $toEmails = array_merge($toEmails, $this->message->getHeaderAsArray('bcc'));

		// $record->set('from_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail([$this->message->getHeaderAsArray('from')], $scanner->getEmailsFields())))));
		// $record->set('to_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($toEmails, $scanner->getEmailsFields())))));
		$record->save();

		$db = \App\Db::getInstance();
		foreach ($this->attachments as $file) {
			$db->createCommand()->insert('vtiger_ossmailview_files', [
				'ossmailviewid' => $record->getId(),
				'documentsid' => $file['crmid'],
				'attachmentsid' => $file['attachmentsId'],
			])->execute();
		}

		$this->message->setMailCrmId($record->getId());
		$this->message->setProcessData($this->getName(), ['mailViewId' => $record->getId(), 'attachments' => $this->attachments]);
	}

	public function saveAttachments()
	{
		$userId = $this->account->getSource()->get('assigned_user_id');
		$useTime = $this->message->getDate();

		$params = [
			'created_user_id' => \App\User::getCurrentUserRealId(),
			'assigned_user_id' => $userId,
			'modifiedby' => $userId,
			'createdtime' => $useTime,
			'modifiedtime' => $useTime,
			'folderid' => 'T2',
		];
		$maxSize = \App\Config::getMaxUploadSize();
		foreach ($this->message->getAttachments() as $key => $file) {
			if ($maxSize < ($size = $file->getSize())) {
				\App\Log::error("Error - downloaded the file is too big '{$file->getName()}', size: {$size}, in mail: {$this->message->getDate()} | Folder: {$this->message->getFolderName()} | ID: {$this->message->getMsgUid()}", __CLASS__);
				continue;
			}
			if ($file->validateAndSecure() && ($id = \App\Fields\File::saveFromContent($file, $params))) {
				$this->attachments[$key] = $id;
				$this->message->setBody(str_replace(["crm-id=\"{$key}\"", "attachment-id=\"{$key}\""], ["crm-id=\"{$id['crmid']}\"", "attachment-id=\"{$id['attachmentsId']}\""], $this->message->getBody()));
			} else {
				\App\Log::error("Error downloading the file '{$file->getName()}' in mail: {$this->message->getDate()} | Folder: {$this->message->getFolderName()} | ID: {$this->message->getMsgUid()}", __CLASS__);
			}
		}
	}

	public function checkExceptions(): bool
	{
		$domainExceptions = array_filter(explode(',', $this->account->getSource()->get('domain_exceptions') ?: ''));
		$emailExceptions = array_column(\App\Json::decode($this->account->getSource()->get('email_exceptions') ?: '[]'), 'e');
		$mail = (0 === $this->message->getMailType()) ? $this->message->getHeader('to') : $this->message->getHeader('from');

		return ($domainExceptions || $emailExceptions) && (\in_array(substr(strrchr($mail, '@'), 1), $domainExceptions) || \in_array($mail, $emailExceptions));
	}
}
