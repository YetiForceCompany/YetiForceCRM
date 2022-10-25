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

		$record = \OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$record->set('assigned_user_id', $owner);
		$record->set('created_user_id', \App\User::getCurrentUserRealId());
		$record->setFromUserValue('subject', \App\TextUtils::textTruncate($this->message->getHeader('subject'), $record->getField('subject')->getMaxValue(), false));
		$record->set('to_email', implode(',', $this->message->getEmail('to')));
		$record->set('from_email', implode(',', $this->message->getEmail('from')));
		$record->set('cc_email', implode(',', $this->message->getEmail('cc')));
		$record->set('bcc_email', implode(',', $this->message->getEmail('bcc')));
		$record->set('reply_to_email', implode(',', $this->message->getEmail('reply_to')));

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
			$this->message->saveAttachments([
				'assigned_user_id' => $owner,
				'modifiedby' => $owner,
			]);
		}

		$record->set('content', \App\TextUtils::htmlTruncate($this->message->getBody(true), $record->getField('content')->getMaxValue()));
		// $record->setHandlerExceptions(['disableHandlers' => true]);

		$record->set('from_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($this->message->getEmail('from'), $this->getEmailsFields())))));
		$toEmails = array_merge($this->message->getEmail('to'), $this->message->getEmail('cc'), $this->message->getEmail('bcc'));
		$record->set('to_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($toEmails, $this->getEmailsFields())))));
		$record->save();

		$db = \App\Db::getInstance();
		foreach ($this->message->getDocuments() as $file) {
			$db->createCommand()->insert('vtiger_ossmailview_files', [
				'ossmailviewid' => $record->getId(),
				'documentsid' => $file['crmid'],
				'attachmentsid' => $file['attachmentsId'],
			])->execute();
		}

		$this->message->setMailCrmId($record->getId());
		$this->message->setProcessData($this->getName(), ['mailViewId' => $record->getId(), 'attachments' => $this->message->getDocuments()]);
	}
}
