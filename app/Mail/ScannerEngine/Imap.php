<?php
/**
 * Mail imap message file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerEngine;

/**
 * Mail imap message class.
 */
class Imap extends Base
{
	/**
	 * Scanner engine name.
	 *
	 * @var string
	 */
	public $name = 'Imap';

	/** {@inheritdoc} */
	public function getActions(): array
	{
		return [];
	}

	/** {@inheritdoc} */
	public function findRelatedRecords(bool $onlyId = false): array
	{
		return [];
	}

	/** {@inheritdoc} */
	public function findRelatedRecordsByEmail(): array
	{
		return [];
	}

	/** {@inheritdoc} */
	public function findRelatedRecordsBySubject(): array
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getExceptions(): array
	{
		return [];
	}

	/** {@inheritdoc} */
	public function getUserId(): int
	{
		return 0;
	}

	/** {@inheritdoc} */
	public function getMailType(): int
	{
		return 0;
	}

	/** {@inheritdoc} */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		if (empty($this->get('message_id')) || \Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['cid' => $this->getUniqueId()])->limit(1);
		} else {
			$query = (new \App\Db\Query())->select(['ossmailviewid'])
				->from('vtiger_ossmailview')
				->where(['uid' => $this->get('message_id'), 'rc_user' => $this->getAccountOwner()])->limit(1);
		}
		$mailCrmId = $query->scalar();
		$this->set('mailCrmId', $mailCrmId);
		return $mailCrmId;
	}

	/** {@inheritdoc} */
	public function getEmailsFields(?string $searchModuleName = null): array
	{
		$cacheKey = $searchModuleName ?? '-';
		if (isset($this->emailsFieldsCache[$cacheKey])) {
			return $this->emailsFieldsCache[$cacheKey];
		}
		$return = [];
		foreach (\OSSMailScanner_Record_Model::getEmailSearchList() as $field) {
			$field = explode('=', $field);
			if (empty($field[2])) {
				$fieldModel = \Vtiger_Module_Model::getInstance($field[1])->getField($field[0]);
				$field[2] = $fieldModel->getUIType();
			}
			if ($searchModuleName && $searchModuleName !== $field[1]) {
				continue;
			}
			$return[$field[1]][$field[2]][] = $field[0];
		}
		$this->emailsFieldsCache[$cacheKey] = $return;
		return $return;
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
	}
}
