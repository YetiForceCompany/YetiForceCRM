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
abstract class Base
{
	/** @var int Action priority. */
	public static $priority = 9;

	/** @var string[] Scope of availability. */
	public static $available = ['Users', 'MailAccount'];

	/** @var string Action label */
	protected $label;

	/** @var \App\Mail\Message\Imap Message instance. */
	protected $message;

	/**
	 * Get action name.
	 * Action name | File name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return substr(strrchr(static::class, '\\'), 1);
	}

	/**
	 * Main function to execute action.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Set mail account.
	 *
	 * @param \App\Mail\Account $account
	 *
	 * @return $this
	 */
	public function setAccount(\App\Mail\Account $account)
	{
		$this->account = $account;
		return $this;
	}

	public function setMessage(\App\Mail\Message\Base $message)
	{
		$this->message = $message;
		return $this;
	}

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

	public function findRelatedRecordsByEmail(): array
	{
		if (!isset($this->message->processData['findByEmail'])) {
			$emails = array_unique(array_merge($this->message->getEmail('from'), $this->message->getEmail('to'), $this->message->getEmail('cc'), $this->message->getEmail('bcc')));
			$this->message->setProcessData('findByEmail', \App\Utils::flatten(\App\Mail\RecordFinder::findByEmail($emails, $this->getEmailsFields())));
		}

		return $this->message->processData['findByEmail'];
	}

	public function findRelatedRecordsBySubject(): array
	{
		if (!isset($this->message->processData['findBySubject'])) {
			$this->message->processData['findBySubject'] = \App\Mail\RecordFinder::findBySubject($this->message->getSubject(), $this->getNumberFields());
		}

		return $this->message->processData['findBySubject'];
	}

	public function getEmailsFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->emailsFieldsCache[$cacheKey])) {
			return $this->emailsFieldsCache[$cacheKey];
		}

		$fields = [];
		if ($mailScannerFields = $this->account->getSource()->get('scanner_fields')) {
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

	public function getNumberFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->numberFieldsCache[$cacheKey])) {
			return $this->numberFieldsCache[$cacheKey];
		}

		$fields = [];
		if ($mailScannerFields = $this->account->getSource()->get('scanner_fields')) {
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

	public function checkExceptions(): bool
	{
		$domainExceptions = array_filter(explode(',', $this->account->getSource()->get('domain_exceptions') ?: ''));
		$emailExceptions = array_column(\App\Json::decode($this->account->getSource()->get('email_exceptions') ?: '[]'), 'e');
		if ($domainGlobalExceptions = \App\Mail::getConfig('scanner', 'domain_exceptions')) {
			$domainExceptions = array_merge($domainExceptions, array_filter(explode(',', $domainGlobalExceptions)));
		}
		if ($emailGlobalExceptions = \App\Mail::getConfig('scanner', 'email_exceptions')) {
			$emailExceptions = array_merge(array_column(\App\Json::decode($emailGlobalExceptions), 'e'));
		}
		$mails = (0 === $this->message->getMailType()) ? $this->message->getEmail('to') : $this->message->getEmail('from');

		return $mails && ($domainExceptions || $emailExceptions) && (
			\array_intersect($mails, $emailExceptions)
			|| \array_intersect(array_map(fn ($email) => (substr(strrchr($email, '@'), 1)), $mails), $domainExceptions)
		);
	}
}
