<?php

/**
 * Class intended for status synchronization.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

use App\Rules\RulesPicklist as Rules;

/**
 * Class SyncStatus.
 */
class SyncStatus
{
	/**
	 * Name of the module for superior elemnt.
	 *
	 * @var string
	 */
	protected $baseModuleName;

	/**
	 * The name of the table in the database for the superior element.
	 *
	 * @var string
	 */
	protected $baseTable;

	/**
	 * The name of the "primary key" column for the superior element.
	 *
	 * @var string
	 */
	protected $baseTableId;

	/**
	 * The name of the "status" column for the superior element.
	 *
	 * @var string
	 */
	protected $baseColumnStatus;

	/**
	 * Name of the module for current elemnt.
	 *
	 * @var string
	 */
	protected $baseColumnParentId;

	/**
	 * The name of the table in the database for the current element.
	 *
	 * @var string
	 */
	protected $currentModuleName;

	/**
	 * The name of the "primary key" column for the current element.
	 *
	 * @var string
	 */
	protected $currentBaseTable;

	/**
	 * The name of the "primary key" column for the current element.
	 *
	 * @var string
	 */
	protected $currentBaseTableId;

	/**
	 * The name of the "status" column for the current element.
	 *
	 * @var string
	 */
	protected $currentColumnStatus;

	/**
	 * The name of the "parent ID" column for the current element.
	 *
	 * @var string
	 */
	protected $currentColumnParentId;

	/**
	 * The name of the sub-module.
	 *
	 * @var string
	 */
	protected $subModuleName;

	/**
	 * Rules configuration.
	 *
	 * @var array
	 */
	private $config = [];

	/**
	 * Status synchronization.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	public static function sync(int $recordId)
	{
		(new static())->updateStatus($recordId);
	}

	/**
	 * Method called in Handler during the event "entityAfterSave".
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public function entityAfterSave(\Vtiger_Record_Model $recordModel)
	{
		$syncMethod = static::class . '::sync';
		if (!$recordModel->isNew()) {
			$subSyncMethod = "{$this->subModuleName}_SyncStatus_Model::sync";
			if (!empty($this->baseTableId) && $recordModel->getPreviousValue($this->currentColumnStatus)) {
				(new BatchMethod(['method' => $syncMethod, 'params' => [$recordModel->get($this->baseTableId)]]))->save();
			}
			if (!empty($this->currentColumnParentId) && false !== ($value = $recordModel->getPreviousValue($this->currentColumnParentId))) {
				if (!empty($value)) {
					(new BatchMethod(['method' => $subSyncMethod, 'params' => [$value]]))->save();
				}
				if (!$recordModel->isEmpty($this->currentColumnParentId)) {
					(new BatchMethod(['method' => $subSyncMethod, 'params' => [$recordModel->get($this->currentColumnParentId)]]))->save();
				}
			}
		} elseif (!empty($this->baseTableId)) {
			(new BatchMethod(['method' => $syncMethod, 'params' => [$recordModel->get($this->baseTableId)]]))->save();
		}
	}

	/**
	 * Method called in Handler during the event "entityAfterDelete".
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public function entityAfterDelete(\Vtiger_Record_Model $recordModel)
	{
		if (!empty($this->baseTableId)) {
			(new BatchMethod(['method' => static::class . '::sync', 'params' => [$recordModel->get($this->baseTableId)]]))->save();
		}
	}

	/**
	 * Method called in Handler during the event "entityChangeState".
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public function entityChangeState(\Vtiger_Record_Model $recordModel)
	{
		if (!empty($this->baseTableId)) {
			(new BatchMethod(['method' => static::class . '::sync', 'params' => [$recordModel->get($this->baseTableId)]]))->save();
		}
	}

	/**
	 * Get config.
	 *
	 * @return array
	 */
	private function getConfig(): array
	{
		return empty($this->config) ? ($this->config = \AppConfig::module($this->baseModuleName, 'STATUS_RULES')) : $this->config;
	}

	/**
	 * Get default value.
	 *
	 * @return mixed
	 */
	private function getDefaultValue()
	{
		return \AppConfig::module($this->baseModuleName, 'STATUS_DEFAULT_VALUE', false);
	}

	/**
	 * Check primary config.
	 *
	 * @return bool
	 */
	private function checkPrimaryConfig(): bool
	{
		return !(
			empty($this->baseModuleName) ||
			empty($this->baseColumnStatus) ||
			empty($this->currentBaseTable) ||
			empty($this->baseTableId) ||
			empty($this->currentBaseTableId) ||
			empty($this->currentColumnStatus)
		);
	}

	/**
	 * Update the status of the record.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	private function updateStatus(int $recordId)
	{
		if (!$this->checkPrimaryConfig()) {
			Log::error('No primary configuration');
			return;
		}
		if (!$this->getConfig()) {
			Log::error("Module: '{$this->baseModuleName}'. The definition of rules for changing statuses has not been configured.");
			return;
		}
		if (!Record::isExists($recordId, $this->baseModuleName)) {
			Log::error("The record does not exist recordId: {$recordId} module: {$this->baseModuleName}.");
			return;
		}
		$itmes = array_merge($this->getCurrentRecordsStatus($recordId), $this->getChildrenStatus($recordId));
		$status = (new Rules($this->getConfig(), $this->getDefaultValue()))->getValue($itmes);
		if (false === $status) {
			Log::warning(
				"There is no rule defined for this case recordId: {$recordId} module: {$this->baseModuleName}. Statuses:" .
				implode(',', array_map(function ($item) {
					return $item['status'];
				}, $itmes))
			);
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->baseModuleName);
		if ($recordModel->get($this->baseColumnStatus) !== $status) {
			$recordModel->set($this->baseColumnStatus, $status);
			$recordModel->save();
			if (!empty($this->baseColumnParentId) && !$recordModel->isEmpty($this->baseColumnParentId)) {
				$this->updateStatus($recordModel->get($this->baseColumnParentId));
			}
		}
	}

	/**
	 * Get the status of children.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	private function getChildrenStatus(int $recordId): array
	{
		$items = [];
		if (!empty($this->baseColumnParentId)) {
			$columnId = "{$this->baseTable}.{$this->baseTableId}";
			$items = $this->getStatusFromPicklist(
				(new Db\Query())
					->select(['id' => $columnId, 'status' => "{$this->baseTable}.{$this->baseColumnStatus}"])
					->from($this->baseTable)
					->innerJoin('vtiger_crmentity', "{$columnId} = vtiger_crmentity.crmid")
					->where(['vtiger_crmentity.deleted' => [0, 2]])
					->andWhere(["{$this->baseTable}.{$this->baseColumnParentId}" => $recordId])
					->all()
			);
		}
		return $items;
	}

	/**
	 * Get statuses of subordinate records.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	private function getCurrentRecordsStatus(int $recordId): array
	{
		$columnId = "{$this->currentBaseTable}.{$this->currentBaseTableId}";
		return $this->getStatusFromPicklist(
			(new Db\Query())
				->select(['id' => $columnId, 'status' => "{$this->currentBaseTable}." . $this->currentColumnStatus])
				->from($this->currentBaseTable)
				->innerJoin('vtiger_crmentity', "{$columnId} = vtiger_crmentity.crmid")
				->where(['vtiger_crmentity.deleted' => [0, 2]])
				->andWhere(["{$this->currentBaseTable}.{$this->baseTableId}" => $recordId])
				->all()
		);
	}

	/**
	 * Get status from picklist.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	private function getStatusFromPicklist(array $items): array
	{
		$picklist = [];
		foreach (Fields\Picklist::getValues($this->currentColumnStatus) as $item) {
			$picklist[$item['picklistValue']] = $item;
		}
		foreach ($items as &$item) {
			$item['automation'] = $picklist[$item['status']]['automation'];
		}
		return $items;
	}
}
