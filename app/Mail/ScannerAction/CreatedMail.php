<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
class CreatedMail extends Base
{
	/** {@inheritdoc} */
	public static $priority = 2;

	/** {@inheritdoc} */
	public function process(): void
	{
		$scanner = $this->scannerEngine;
		if ($this->checkExceptions('CreatedMail') || false !== $scanner->getMailCrmId()) {
			return;
		}
		$record = \OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$record->set('assigned_user_id', $scanner->getUserId());
		$record->set('created_user_id', $scanner->getUserId());
		$record->setFromUserValue('subject', \App\TextUtils::textTruncate($scanner->get('subject'), $record->getField('subject')->getMaxValue(), false));
		$record->set('to_email', implode(',', $scanner->get('to_email')));
		$record->set('from_email', $scanner->get('from_email'));
		if ($scanner->has('cc_email')) {
			$record->set('cc_email', implode(',', $scanner->get('cc_email')));
		}
		if ($scanner->has('bcc_email')) {
			$record->set('bcc_email', implode(',', $scanner->get('bcc_email')));
		}
		$record->set('date', $scanner->get('date'));
		$record->set('createdtime', $scanner->get('date'));
		$record->set('uid', $scanner->get('message_id'));
		$type = $scanner->getMailType();
		$record->set('type', $type);
		$record->set('ossmailview_sendtype', \App\Mail\ScannerEngine\Base::MAIL_TYPES[$type]);
		$record->set('content', \App\TextUtils::htmlTruncate($scanner->get('body'), $record->getField('content')->getMaxValue()));
		$record->set('orginal_mail', \App\TextUtils::htmlTruncate($scanner->get('headers'), $record->getField('orginal_mail')->getMaxValue()));
		$record->setHandlerExceptions(['disableHandlers' => true]);
		$record->setDataForSave(['vtiger_ossmailview' => [
			'cid' => $scanner->getCid(),
		]]);
		$toEmails = $scanner->get('to_email') ?? [];
		if ($scanner->has('cc_email')) {
			$toEmails = array_merge($toEmails, $scanner->get('cc_email'));
		}
		if ($scanner->has('bcc_email')) {
			$toEmails = array_merge($toEmails, $scanner->get('bcc_email'));
		}
		$record->set('from_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail([$scanner->get('from_email')], $scanner->getEmailsFields())))));
		$record->set('to_id', implode(',', array_unique(\App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($toEmails, $scanner->getEmailsFields())))));
		$record->save();
		$record->setHandlerExceptions([]);
		if ($id = $record->getId()) {
			$scanner->set('mailCrmId', $id);
		}
		$scanner->processData['CreatedMail'] = $id;
	}
}
