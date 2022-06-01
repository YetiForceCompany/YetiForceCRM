<?php
/**
 * Mail outlook message file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerEngine;

/**
 * Mail outlook message class.
 */
class Outlook extends Base
{
	/**
	 * Scanner engine name.
	 *
	 * @var string
	 */
	public $name = 'Outlook';

	/** {@inheritdoc} */
	public function getActions(): array
	{
		return array_filter(explode(',', \App\User::getCurrentUserModel()->getDetail('mail_scanner_actions')));
	}

	/** {@inheritdoc} */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		$mailCrmId = \App\Mail\Message::findByCid($this->getCid());
		$this->set('mailCrmId', $mailCrmId);
		return $mailCrmId;
	}

	/** {@inheritdoc} */
	public function getMailType(): int
	{
		if ($this->has('mailType')) {
			return $this->get('mailType');
		}
		$to = false;
		$from = (bool) \App\Mail\RecordFinder::findUserEmail([$this->get('from_email')]);
		if ($this->has('to_email')) {
			$to = (bool) \App\Mail\RecordFinder::findUserEmail($this->get('to_email'));
		} elseif ($this->has('cc_email')) {
			$to = (bool) \App\Mail\RecordFinder::findUserEmail($this->get('cc_email'));
		} elseif ($this->has('bcc_email')) {
			$to = (bool) \App\Mail\RecordFinder::findUserEmail($this->get('bcc_email'));
		}
		$key = self::MAIL_TYPE_RECEIVED;
		if ($from && $to) {
			$key = self::MAIL_TYPE_INTERNAL;
		} elseif ($from) {
			$key = self::MAIL_TYPE_SENT;
		}
		$this->set('mailType', $key);
		return $key;
	}

	/** {@inheritdoc} */
	public function getUserId(): int
	{
		return \App\User::getCurrentUserRealId();
	}

	/**
	 * Initialize with request data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function initFromRequest(\App\Request $request)
	{
		$this->set('subject', $request->isEmpty('mailSubject') ? '-' : \App\TextUtils::textTruncate($request->getByType('mailSubject', 'Text'), 65535, false));
		$this->set('headers', $request->isEmpty('mailHeaders') ? '' : \App\TextUtils::textTruncate($request->getRaw('mailHeaders'), 16777215, false));
		$this->set('from_email', $request->getByType('mailFrom', 'Email'));
		$this->set('date', $request->getByType('mailDateTimeCreated', 'DateTimeInIsoFormat'));
		$this->set('message_id', $request->getByType('mailMessageId', 'MailId'));
		if (!$request->isEmpty('mailTo')) {
			$this->set('to_email', $request->getArray('mailTo', 'Email'));
		}
		if (!$request->isEmpty('mailCc', true)) {
			$this->set('cc_email', $request->getArray('mailCc', 'Email'));
		}
		if (!$request->isEmpty('mailBcc', true)) {
			$this->set('bcc_email', $request->getArray('mailBcc', 'Email'));
		}
		if (!$request->isEmpty('mailBody', true)) {
			$this->set('body', $request->getForHtml('mailBody'));
		}
	}

	/** {@inheritdoc} */
	public function findRelatedRecords(bool $onlyId = false): array
	{
		$ids = $this->findRelatedRecordsByEmail();
		if ($idsBySubject = $this->findRelatedRecordsBySubject()) {
			$ids[] = current($idsBySubject);
		}
		if (!$onlyId) {
			foreach ($ids as &$id) {
				$id = [
					'id' => $id,
					'module' => \App\Record::getType($id),
					'label' => \App\Record::getLabel($id),
				];
			}
		}
		return $ids;
	}

	/** {@inheritdoc} */
	public function findRelatedRecordsByEmail(): array
	{
		if (isset($this->processData['findByEmail'])) {
			return $this->processData['findByEmail'];
		}
		$emails = $this->get('to_email');
		$emails[] = $this->get('from_email');
		if ($this->has('cc_email')) {
			$emails = array_merge($emails, $this->get('cc_email'));
		}
		if ($this->has('bcc_email')) {
			$emails = array_merge($emails, $this->get('bcc_email'));
		}
		return $this->processData['findByEmail'] = \App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($emails, $this->getEmailsFields()));
	}

	/** {@inheritdoc} */
	public function findRelatedRecordsBySubject(): array
	{
		if (isset($this->processData['findBySubject'])) {
			return $this->processData['findBySubject'];
		}
		return $this->processData['findBySubject'] = \App\Mail\RecordFinder::findBySubject($this->get('subject'), $this->getNumberFields());
	}

	/** {@inheritdoc} */
	public function getEmailsFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->emailsFieldsCache[$cacheKey])) {
			return $this->emailsFieldsCache[$cacheKey];
		}
		$user = \App\User::getCurrentUserModel();
		$fields = [];
		if ($mailScannerFields = $user->getDetail('mail_scanner_fields')) {
			foreach (explode(',', trim($mailScannerFields, ',')) as $field) {
				$field = explode('|', $field);
				if (($searchModuleName && $searchModuleName !== $field[1]) || !\in_array($field[3], [13, 319])) {
					continue;
				}
				$fields[$field[1]][$field[3]][] = $field[2];
			}
		}
		$this->emailsFieldsCache[$cacheKey] = $fields;
		return $fields;
	}

	/** {@inheritdoc} */
	public function getNumberFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->numberFieldsCache[$cacheKey])) {
			return $this->numberFieldsCache[$cacheKey];
		}
		$user = \App\User::getCurrentUserModel();
		$fields = [];
		if ($mailScannerFields = $user->getDetail('mail_scanner_fields')) {
			foreach (explode(',', trim($mailScannerFields, ',')) as $field) {
				$field = explode('|', $field);
				if (($searchModuleName && $searchModuleName !== $field[1]) || 4 !== (int) $field[3]) {
					continue;
				}
				$fields[$field[1]][$field[3]][] = $field[2];
			}
		}
		$this->numberFieldsCache[$cacheKey] = $fields;
		return $fields;
	}

	/** {@inheritdoc} */
	public function getExceptions(): array
	{
		return [];
	}
}
