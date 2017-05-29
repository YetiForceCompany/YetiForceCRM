<?php

/**
 * Mail scanner action creating mail
 * @package YetiForce.MailScanner
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
		$type = $mail->getTypeEmail();
		$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
		if (!empty($exceptionsAll['crating_mails'])) {
			$exceptions = explode(',', $exceptionsAll['crating_mails']);
			$mailForExceptions = ($type === 0) ? $mail->get('toaddress') : $mail->get('fromaddress');
			foreach ($exceptions as $exception) {
				if (strpos($mailForExceptions, $exception) !== false) {
					return $id;
				}
			}
		}
		if ($mail->getMailCrmId() === false) {
			$fromIds = array_merge($mail->findEmailAdress('fromaddress'), $mail->findEmailAdress('reply_toaddress'));
			$toIds = array_merge($mail->findEmailAdress('toaddress'), $mail->findEmailAdress('ccaddress'), $mail->findEmailAdress('bccaddress'));
			$account = $mail->getAccount();
			$record = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$record->set('assigned_user_id', $mail->getAccountOwner());
			$record->set('subject', $mail->isEmpty('subject') ? '-' : \App\Purifier::purify($mail->get('subject')));
			$record->set('to_email', \App\Purifier::purify($mail->get('toaddress')));
			$record->set('from_email', \App\Purifier::purify($mail->get('fromaddress')));
			$record->set('reply_to_email', \App\Purifier::purify($mail->get('reply_toaddress')));
			$record->set('cc_email', \App\Purifier::purify($mail->get('ccaddress')));
			$record->set('bcc_email', \App\Purifier::purify($mail->get('bccaddress')));
			$record->set('fromaddress', \App\Purifier::purify($mail->get('from')));
			$record->set('orginal_mail', \App\Purifier::purifyHtml($mail->get('clean')));
			$record->set('uid', \App\Purifier::purify($mail->get('message_id')))->set('rc_user', $account['user_id']);
			$record->set('ossmailview_sendtype', $mail->getTypeEmail(true));
			$record->set('mbox', $mail->getFolder())->set('type', $type)->set('mid', $mail->get('id'));
			$record->set('from_id', implode(',', array_unique($fromIds)))->set('to_id', implode(',', array_unique($toIds)));
			$record->set('created_user_id', $mail->getAccountOwner())->set('createdtime', $mail->get('udate_formated'));
			$record->set('content', $this->parseContent($mail));
			if ($mail->get('isAttachments') || $mail->get('attachments')) {
				$record->set('attachments_exist', 1);
			}
			$record->setHandlerExceptions(['disableHandlers' => true]);
			$record->save();
			$record->setHandlerExceptions([]);
			if ($id = $record->getId()) {
				$mail->setMailCrmId($id);
				$mail->saveAttachments();
				App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', [
					'date' => $mail->get('udate_formated'),
					'cid' => $mail->getUniqueId()
					], ['ossmailviewid' => $id]
				)->execute();
			}
		}
		return $id;
	}

	/**
	 * Treatment mail content with all images and unnecessary trash
	 * @param OSSMail_Mail_Model $mail
	 * @return string
	 */
	public function parseContent(OSSMail_Mail_Model $mail)
	{
		$html = $mail->get('body');
		$html = preg_replace('/<html[^>]+\>/', '', $html);
		$html = preg_replace('/<body[^>]+\>/', '', $html);
		$html = str_replace(['<html>', '<body>', '</html>', '</body>'], '', $html);
		$doc = new \DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(true);
		/*
		 * Alternative when coding problems
		 * $doc->loadHTML(mb_convert_encoding($mail->get('body'), 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		 */
		$doc->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);
		$params = [
			'created_user_id' => $mail->getAccountOwner(),
			'assigned_user_id' => $mail->getAccountOwner(),
			'modifiedby' => $mail->getAccountOwner(),
			'createdtime' => $mail->get('udate_formated'),
			'modifiedtime' => $mail->get('udate_formated')
		];
		$attachments = $mail->get('attachments');
		$files = [];
		foreach ($doc->getElementsByTagName('img') as $img) {
			$src = trim($img->getAttribute('src'), '\'');
			if (substr($src, 0, 5) === 'data:') {
				if ($ids = App\Fields\File::saveFromString($src, $params)) {
					$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsid']}&show=true");
					$img->setAttribute('alt', '-');
					$files[] = $ids;
					continue;
				}
			} elseif (filter_var($src, FILTER_VALIDATE_URL)) {
				if ($ids = App\Fields\File::saveFromUrl($src, $params)) {
					$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsid']}&show=true");
					$img->setAttribute('alt', '-');
					$files[] = $ids;
					continue;
				}
			} elseif (substr($src, 0, 4) === 'cid:') {
				$src = substr($src, 4);
				if (isset($attachments[$src])) {
					if ($ids = App\Fields\File::saveFromContent($attachments[$src]['attachment'], $attachments[$src]['filename'], false, $params)) {
						$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsid']}&show=true");
						if (!$img->hasAttribute('alt')) {
							$img->setAttribute('alt', $attachments[$src]['filename']);
						}
						$files[] = $ids;
						unset($attachments[$src]);
						continue;
					}
				}
			}
			$img->removeAttribute('src');
		}
		$mail->set('files', $files);
		$mail->set('attachments', $attachments);
		return \App\Purifier::purifyHtml(str_replace('<?xml encoding="utf-8"?>', '', $doc->saveHTML()));
	}
}
