<?php

/**
 * Mail scanner action creating mail
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_CreatedEmail_ScannerAction
{

	/**
	 * Process
	 * @param OSSMail_Mail_Model $mail
	 * @return int
	 */
	public function process(OSSMail_Mail_Model $mail)
	{
		$id = 0;
		$folder = $mail->getFolder();
		$account = $mail->getAccount();
		$type = $mail->getTypeEmail();
		$mailId = $mail->getMailCrmId();
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');

		if ($type == 0) {
			$mailForExceptions = $mail->get('toaddress');
		} else {
			$mailForExceptions = $mail->get('fromaddress');
		}

		if (!empty($exceptionsAll['crating_mails'])) {
			$exceptions = explode(',', $exceptionsAll['crating_mails']);
			foreach ($exceptions as $exception) {
				if (strpos($mailForExceptions, $exception) !== false) {
					return $id;
				}
			}
		}
		if ($mailId === false && !empty($mail->get('message_id'))) {
			$toIds = $fromIds = [];
			$fromIds = array_merge($fromIds, $mail->findEmailAdress('fromaddress'));
			$fromIds = array_merge($fromIds, $mail->findEmailAdress('reply_toaddress'));
			$toIds = array_merge($toIds, $mail->findEmailAdress('toaddress'));
			$toIds = array_merge($toIds, $mail->findEmailAdress('ccaddress'));
			$toIds = array_merge($toIds, $mail->findEmailAdress('bccaddress'));

			$record = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$record->set('assigned_user_id', $mail->getAccountOwner());
			$record->set('subject', $mail->get('subject'));
			$record->set('to_email', $mail->get('toaddress'));
			$record->set('from_email', $mail->get('fromaddress'));
			$record->set('reply_to_email', $mail->get('reply_toaddress'));
			$record->set('cc_email', $mail->get('ccaddress'));
			$record->set('bcc_email', $mail->get('bccaddress'));
			$record->set('fromaddress', $mail->get('from'));
			$record->set('content', $mail->get('body'));
			$record->set('orginal_mail', $mail->get('clean'));
			$record->set('uid', $mail->get('message_id'));
			$record->set('ossmailview_sendtype', $mail->getTypeEmail(true));
			$record->set('mbox', $mail->getFolder());
			$record->set('type', $type);
			$record->set('mid', $mail->get('id'));
			$record->set('rc_user', $account['user_id']);
			$record->set('from_id', implode(',', array_unique($fromIds)));
			$record->set('to_id', implode(',', array_unique($toIds)));
			if (count($mail->get('attachments')) > 0) {
				$record->set('attachments_exist', 1);
			}
			$record->setHandlerExceptions(['disableHandlers' => true]);
			$record->save();
			$record->setHandlerExceptions([]);
			$id = $record->getId();

			$mail->setMailCrmId($id);
			OSSMail_Record_Model::_SaveAttachments($id, $mail);
			$db = App\Db::getInstance();
			$db->createCommand()->update('vtiger_crmentity', [
				'createdtime' => $mail->get('udate_formated'),
				'smcreatorid' => $mail->getAccountOwner(),
				'modifiedby' => $mail->getAccountOwner()
				], ['crmid' => $id]
			)->execute();
			$db->createCommand()->update('vtiger_ossmailview', [
				'date' => $mail->get('udate_formated')
				], ['ossmailviewid' => $id]
			)->execute();
		}
		return $id;
	}
}
