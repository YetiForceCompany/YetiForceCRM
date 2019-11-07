<?php
/**
 * Mail outlook message file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		foreach ($this->getActions() as $action) {
			$class = "App\\Mail\\ScannerAction\\{$action}";
			(new $class($this))->process();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActions(): array
	{
		$user = \App\User::getCurrentUserModel();
		return array_filter(explode(',', $user->getDetail('mail_scanner_actions')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		$mailCrmId = \App\Mail\Message::findByCid($this->getCid());
		$this->set('mailCrmId', $mailCrmId);
		return $mailCrmId;
	}

	/**
	 * {@inheritdoc}
	 */
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
		$this->set('subject', $request->isEmpty('mailSubject') ? '-' : \App\TextParser::textTruncate($request->getByType('mailSubject', 'Text'), 65535, false));
		$this->set('from_email', $request->getByType('mailFrom', 'Email'));
		$this->set('date', $request->getByType('mailDateTimeCreated', 'DateTimeInIsoFormat'));
		$this->set('message_id', $request->getByType('mailMessageId', 'MailId'));
		if ($request->has('mailTo')) {
			$this->set('to_email', $request->getArray('mailTo', 'Email'));
		}
		if ($request->has('mailCc')) {
			$this->set('cc_email', $request->getArray('mailCc', 'Email'));
		}
		if ($request->has('mailBody')) {
			$this->set('body', $request->getForHtml('mailBody'));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function findRelatedRecords(bool $onlyId = false): array
	{
		$emails = $this->get('to_email');
		$emails[] = $this->get('from_email');
		if ($this->has('cc_email')) {
			$emails = array_merge($emails, $this->get('cc_email'));
		}
		if ($this->has('bcc_email')) {
			$emails = array_merge($emails, $this->get('bcc_email'));
		}
		$ids = array_flatten(\App\Mail\RecordFinder::findByEmail($emails, $this->getEmailsFields()));
		if ($idsBySubject = \App\Mail\RecordFinder::findBySubject($this->get('subject'), $this->getNumberFields())) {
			$ids = array_merge($ids, $idsBySubject);
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

	/**
	 * {@inheritdoc}
	 */
	public function getEmailsFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->emailsFieldsCache[$cacheKey])) {
			return $this->emailsFieldsCache[$cacheKey];
		}
		$user = \App\User::getCurrentUserModel();
		$fields = [];
		foreach (array_filter(explode(',', $user->getDetail('mail_scanner_fields'))) as $field) {
			$field = explode('|', $field);
			if (($searchModuleName && $searchModuleName !== $field[1]) || !\in_array($field[3], [13, 319])) {
				continue;
			}
			$fields[$field[1]][$field[3]][] = $field[2];
		}
		$this->emailsFieldsCache[$cacheKey] = $fields;
		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNumberFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->numberFieldsCache[$cacheKey])) {
			return $this->numberFieldsCache[$cacheKey];
		}
		$user = \App\User::getCurrentUserModel();
		$fields = [];
		foreach (array_filter(explode(',', $user->getDetail('mail_scanner_fields'))) as $field) {
			$field = explode('|', $field);
			if (($searchModuleName && $searchModuleName !== $field[1]) || 4 !== (int) $field[3]) {
				continue;
			}
			$fields[$field[1]][$field[3]][] = $field[2];
		}
		$this->numberFieldsCache[$cacheKey] = $fields;
		return $fields;
	}
}
