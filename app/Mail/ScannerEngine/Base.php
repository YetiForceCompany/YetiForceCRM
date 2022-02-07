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
abstract class Base extends \App\Base
{
	const MAIL_TYPE_SENT = 0;
	const MAIL_TYPE_RECEIVED = 1;
	const MAIL_TYPE_INTERNAL = 2;
	/**
	 * Mail types map.
	 */
	const MAIL_TYPES = [
		0 => 'Sent',
		1 => 'Received',
		2 => 'Internal',
	];
	/**
	 * Process data.
	 *
	 * @var array
	 */
	public $processData = [];
	/**
	 * Emails fields cache.
	 *
	 * @var string[]
	 */
	protected $emailsFieldsCache = [];
	/**
	 * Number fields cache.
	 *
	 * @var string[]
	 */
	protected $numberFieldsCache = [];

	/**
	 * Main function to execute scanner engine actions.
	 *
	 * @return void
	 */
	public function process(): void
	{
		foreach ($this->getActions() as $action) {
			$class = "App\\Mail\\ScannerAction\\{$action}";
			(new $class($this))->process();
		}
	}

	/**
	 * Get scanner actions.
	 *
	 * @return array
	 */
	abstract public function getActions(): array;

	/**
	 * Get mail crm id.
	 *
	 * @return array
	 */
	abstract public function getMailCrmId();

	/**
	 * Get user id.
	 *
	 * @return int
	 */
	abstract public function getUserId(): int;

	/**
	 * Get emails fields to search.
	 *
	 * @param string|null $searchModuleName
	 *
	 * @return array
	 */
	abstract public function getEmailsFields(?string $searchModuleName = null): array;

	/**
	 * Find related records.
	 *
	 * @param bool $onlyId
	 *
	 * @return int[]
	 */
	abstract public function findRelatedRecords(bool $onlyId = false): array;

	/**
	 * Find related records by emails.
	 *
	 * @return int[]
	 */
	abstract public function findRelatedRecordsByEmail(): array;

	/**
	 * Find related records by subject.
	 *
	 * @return int[]
	 */
	abstract public function findRelatedRecordsBySubject(): array;

	/**
	 * Get exceptions.
	 *
	 * @return array
	 */
	abstract public function getExceptions(): array;

	/**
	 * Initialize with request data.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	abstract public function initFromRequest(\App\Request $request);

	/**
	 * Get mail type.
	 * 0 = Sent
	 * 1 = Received
	 * 2 = Internal.
	 *
	 * @return int
	 */
	abstract public function getMailType(): int;

	/**
	 * Get related records.
	 *
	 * @return array
	 */
	public function getRelatedRecords(): array
	{
		$relations = [];
		$query = (new \App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
			->from('vtiger_ossmailview_relation')
			->innerJoin('vtiger_crmentity', 'vtiger_ossmailview_relation.crmid = vtiger_crmentity.crmid')
			->where(['vtiger_ossmailview_relation.ossmailviewid' => $this->getMailCrmId(), 'vtiger_crmentity.deleted' => 0]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$relations[] = [
				'id' => $row['crmid'],
				'module' => $row['setype'],
				'label' => \App\Record::getLabel($row['crmid']),
			];
		}
		$dataReader->close();
		return $relations;
	}

	/**
	 * Generation crm unique id.
	 *
	 * @return string
	 */
	public function getCid(): string
	{
		if ($this->has('cid')) {
			return $this->get('cid');
		}
		$cid = hash('sha256', $this->get('from_email') . '|' . $this->get('date') . '|' . $this->get('subject') . '|' . $this->get('message_id'));
		$this->set('cid', $cid);
		return $cid;
	}
}
