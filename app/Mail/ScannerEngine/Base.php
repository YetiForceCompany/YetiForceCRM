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
abstract class Base extends \App\Base
{
	/**
	 * Process data.
	 *
	 * @var array
	 */
	public $processData = [];

	/**
	 * Main function to execute scanner engine actions.
	 *
	 * @return void
	 */
	abstract public function process(): void;

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
}
