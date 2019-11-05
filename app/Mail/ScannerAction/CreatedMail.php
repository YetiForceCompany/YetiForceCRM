<?php
/**
 * Base mail scanner action file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
class CreatedMail extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static $priority = 2;

	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		$scanner = $this->scannerEngine;
		if (false === $scanner->getMailCrmId()) {
			$record = \OSSMailView_Record_Model::getCleanInstance('OSSMailView');
			$record->set('assigned_user_id', $scanner->getUserId());
			$record->set('created_user_id', $scanner->getUserId());
			$record->set('subject', $scanner->isEmpty('subject') ? '-' : $scanner->get('subject'));
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
			$maxLengthContent = $record->getField('content')->get('maximumlength');
			$record->set('content', $maxLengthContent ? \App\TextParser::htmlTruncate($scanner->get('body'), $maxLengthContent, false) : $scanner->get('body'));
			$record->setHandlerExceptions(['disableHandlers' => true]);
			$record->setDataForSave(['vtiger_ossmailview' => [
				'cid' => $scanner->getCid(),
			]]);
			$record->save();
			$record->setHandlerExceptions([]);
			if ($id = $record->getId()) {
				$scanner->set('mailCrmId', $id);
			}
		}
	}
}
