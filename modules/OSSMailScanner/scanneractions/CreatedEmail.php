<?php

/**
 * Mail scanner action creating mail.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSMailScanner_CreatedEmail_ScannerAction
{
	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return int
	 */
	public function process(OSSMail_Mail_Model $mail)
	{
		$type = $mail->getTypeEmail();
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_mails'])) {
			$exceptions = explode(',', $exceptionsAll['crating_mails']);
			$mailForExceptions = (0 === $type) ? $mail->get('to_email') : $mail->get('from_email');
			foreach ($exceptions as $exception) {
				if (false !== strpos($mailForExceptions, $exception)) {
					return false;
				}
			}
		}
		if (false === $mail->getMailCrmId()) {
			$fromIds = array_merge($mail->findEmailAddress('from_email'), $mail->findEmailAddress('reply_toaddress'));
			$toIds = array_merge($mail->findEmailAddress('to_email'), $mail->findEmailAddress('cc_email'), $mail->findEmailAddress('bcc_email'));
			$account = $mail->getAccount();
			$record = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
			$record->set('assigned_user_id', $mail->getAccountOwner());
			$record->setFromUserValue('subject', $mail->isEmpty('subject') ? '-' : $mail->get('subject'));
			$record->set('to_email', $mail->get('to_email'));
			$record->set('from_email', $mail->get('from_email'));
			$record->set('reply_to_email', $mail->get('reply_toaddress'));
			$record->set('cc_email', $mail->get('cc_email'));
			$record->set('bcc_email', $mail->get('bcc_email'));
			$record->set('orginal_mail', \App\TextUtils::htmlTruncate($mail->get('clean'), $record->getField('orginal_mail')->getMaxValue()));
			$record->set('uid', $mail->get('message_id'))->set('rc_user', $account['user_id']);
			$record->set('ossmailview_sendtype', $mail->getTypeEmail(true));
			$record->set('mbox', $mail->getFolder())->set('type', $type)->set('mid', $mail->get('id'));
			$record->set('from_id', implode(',', array_unique($fromIds)))->set('to_id', implode(',', array_unique($toIds)));
			$record->set('created_user_id', $mail->getAccountOwner())->set('createdtime', $mail->get('date'));
			$record->set('date', $mail->get('date'));
			$record->set('content', \App\TextUtils::htmlTruncate($mail->getContent(), $record->getField('content')->getMaxValue()));
			if ($mail->get('isAttachments') || $mail->get('attachments')) {
				$record->set('attachments_exist', 1);
			}
			$record->setHandlerExceptions(['disableHandlers' => true]);
			$record->setDataForSave(['vtiger_ossmailview' => [
				'cid' => $mail->getUniqueId(),
			]]);
			$record->save();
			$record->setHandlerExceptions([]);
			if ($id = $record->getId()) {
				$mail->setMailCrmId($id);
				return ['mailViewId' => $id, 'attachments' => $mail->saveAttachments()];
			}
		} else {
			App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', [
				'id' => $mail->get('id'),
			], ['ossmailviewid' => $mail->getMailCrmId()]
			)->execute();
			return ['mailViewId' => $mail->getMailCrmId()];
		}
		return false;
	}
}
