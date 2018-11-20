<?php

/**
 * Mail scanner action creating mail.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			$mailForExceptions = ($type === 0) ? $mail->get('toaddress') : $mail->get('fromaddress');
			foreach ($exceptions as $exception) {
				if (strpos($mailForExceptions, $exception) !== false) {
					return false;
				}
			}
		}
		if ($mail->getMailCrmId() === false) {
			$fromIds = array_merge($mail->findEmailAdress('fromaddress'), $mail->findEmailAdress('reply_toaddress'));
			$toIds = array_merge($mail->findEmailAdress('toaddress'), $mail->findEmailAdress('ccaddress'), $mail->findEmailAdress('bccaddress'));
			$account = $mail->getAccount();
			$record = Vtiger_Record_Model::getCleanInstance('OSSMailView');
			$record->set('assigned_user_id', $mail->getAccountOwner());
			$record->setFromUserValue('subject', $mail->isEmpty('subject') ? '-' : \App\Purifier::purify($mail->get('subject')));
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
				$attachments = $mail->saveAttachments();
				App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', [
					'date' => $mail->get('udate_formated'),
					'cid' => $mail->getUniqueId(),
				], ['ossmailviewid' => $id]
				)->execute();

				return ['mailViewId' => $id, 'attachments' => $attachments];
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

	/**
	 * Treatment mail content with all images and unnecessary trash.
	 *
	 * @param OSSMail_Mail_Model $mail
	 *
	 * @return string
	 */
	public function parseContent(OSSMail_Mail_Model $mail)
	{
		$html = $mail->get('body');
		$attachments = $mail->get('attachments');
		if (count($attachments) < 2) {
			foreach ($attachments as $key => $attachment) {
				if ((substr($attachment['filename'], -5) === '.html') || (substr($attachment['filename'], -4) === '.txt')) {
					$html .= $attachment['attachment'] . '<hr />';
					unset($attachments[$key]);
				}
			}
		}
		$encoding = mb_detect_encoding($html);
		if ($encoding && $encoding !== 'UTF-8') {
			$html = mb_convert_encoding($html, 'UTF-8', $encoding);
		}
		$html = preg_replace(
			[':<(head|style|script).+?</\1>:is', // remove <head>, <styleand <scriptsections
				':<!\[[^]<]+\]>:', // remove <![if !mso]and friends
				':<!DOCTYPE[^>]+>:', // remove <!DOCTYPE ... >
				':<\?[^>]+>:', // remove <?xml version="1.0" ... >
				'~</?html[^>]*>~', // remove html tags
				'~</?body[^>]*>~', // remove body tags
				'~</?o:[^>]*>~', // remove mso tags
				'~\sclass=[\'|\"][^\'\"]+[\'|\"]~i'// remove class attributes
			], ['', '', '', '', '', '', '', ''], $html);
		$doc = new \DOMDocument('1.0', 'UTF-8');
		$previousValue = libxml_use_internal_errors(true);
		$doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);
		$params = [
			'created_user_id' => $mail->getAccountOwner(),
			'assigned_user_id' => $mail->getAccountOwner(),
			'modifiedby' => $mail->getAccountOwner(),
			'createdtime' => $mail->get('udate_formated'),
			'modifiedtime' => $mail->get('udate_formated'),
		];

		$files = [];
		foreach ($doc->getElementsByTagName('img') as $img) {
			$src = trim($img->getAttribute('src'), '\'');
			if (substr($src, 0, 5) === 'data:') {
				if (($fileInstance = \App\Fields\File::saveFromString($src)) && ($ids = \App\Fields\File::saveFromContent($fileInstance, $params))) {
					$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsId']}&show=true");
					$img->setAttribute('alt', '-');
					$files[] = $ids;
					continue;
				}
			} elseif (filter_var($src, FILTER_VALIDATE_URL)) {
				if ($ids = App\Fields\File::saveFromUrl($src, $params)) {
					$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsId']}&show=true");
					$img->setAttribute('alt', '-');
					$files[] = $ids;
					continue;
				}
			} elseif (substr($src, 0, 4) === 'cid:') {
				$src = substr($src, 4);
				if (isset($attachments[$src])) {
					$fileInstance = App\Fields\File::loadFromContent($attachments[$src]['attachment'], $attachments[$src]['filename']);
					if ($fileInstance && $fileInstance->validate() && ($ids = App\Fields\File::saveFromContent($fileInstance, $params))) {
						$img->setAttribute('src', "file.php?module=Documents&action=DownloadFile&record={$ids['crmid']}&fileid={$ids['attachmentsId']}&show=true");
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
		$previousValue = libxml_use_internal_errors(true);
		$html = $doc->saveHTML();
		libxml_clear_errors();
		libxml_use_internal_errors($previousValue);
		return \App\Purifier::purifyHtml(str_replace('<?xml encoding="utf-8"?>', '', $html));
	}
}
