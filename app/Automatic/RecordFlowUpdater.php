<?php
/**
 * A file with class RecordFlowUpdater.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Automatic;

/**
 * Class RecordFlowUpdater - Automation of reference fields.
 */
class RecordFlowUpdater
{
	/**
	 * Source/sub-module module name.
	 *
	 * @var string
	 */
	private $sourceModuleName;

	/**
	 * Name of the module for superior/target elemnt.
	 *
	 * @var string
	 */
	protected $targetModuleName;

	/**
	 * The name of the table in the database for the superior/target element.
	 *
	 * @var string
	 */
	protected $targetTable;

	/**
	 * The name of the "primary key" column for the superior/target element.
	 *
	 * @var string
	 */
	protected $targetTableId;

	/**
	 * The name of the "value" column/field for the superior/target element.
	 *
	 * @var string
	 */
	protected $targetField;

	/**
	 * Name of the module for current elemnt.
	 *
	 * @var string
	 */
	protected $targetColumnParentId;

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
	protected $sourceTable;

	/**
	 * The name of the "primary key" column for the current element.
	 *
	 * @var string
	 */
	protected $sourceTableId;

	/**
	 * The name of the "status" column for the current element.
	 *
	 * @var string
	 */
	protected $sourceField;

	/**
	 * The name of the "parent ID" column for the current element.
	 *
	 * @var string
	 */
	protected $currentColumnParentId;

	/**
	 * The default value is returned if the rules do not specify this case.
	 *
	 * @var bool
	 */
	private $defaultValue = false;

	/**
	 * Definition of rules for automatic status change.
	 *
	 * @var array
	 */
	private $rules = [];

	/**
	 * Is configured.
	 *
	 * @var bool
	 */
	private $isConfigured = false;

	/**
	 * Update the value of the field.
	 *
	 * @param int    $recordId
	 * @param string $sourceModuleName
	 *
	 * @return void
	 */
	public static function update(string $sourceModuleName, int $recordId)
	{
		(new static($sourceModuleName))->updateFieldValue($recordId);
	}

	/**
	 * Construct.
	 *
	 * @param string $sourceModuleName
	 */
	public function __construct(string $sourceModuleName)
	{
		$this->sourceModuleName = $sourceModuleName;
		$this->isConfigured = false;
		$config = $this->getConfig();
		if (false !== $config) {
			$sourceModuleModel = \Vtiger_Module_Model::getInstance($this->sourceModuleName);
			$targetModuleModel = \Vtiger_Module_Model::getInstance($config['target_module']);
			$this->sourceTable = $sourceModuleModel->basetable;
			$this->sourceTableId = $sourceModuleModel->basetableid;
			$this->targetModuleName = \App\Module::getModuleName($config['target_module']);
			$this->targetTable = $targetModuleModel->basetable;
			$this->targetTableId = $targetModuleModel->basetableid;
			$this->targetColumnParentId = $config['relation_field'];
			$this->sourceField = $config['source_field'];
			$this->targetField = $config['target_field'];
			$this->defaultValue = $config['default_value'];
			$this->rules = \App\Json::decode($config['rules']);
			$this->isConfigured = true;
		}
	}

	/**
	 * Check is configured.
	 *
	 * @return bool
	 */
	public function checkIsConfigured(): bool
	{
		return $this->isConfigured;
	}

	/**
	 * Update field value.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	public function updateFieldValue(int $recordId)
	{
		if (!$this->checkIsConfigured()) {
			return;
		}
		if (!\App\Record::isExists($recordId, $this->targetModuleName)) {
			\App\Log::error("The record does not exist recordId: {$recordId} module: {$this->targetModuleName}.");
			return;
		}
		$returnedValue = $this->executeRules($recordId);
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->targetModuleName);
		if ($recordModel->get($this->targetField) !== $returnedValue) {
			$recordModel->set($this->targetField, $returnedValue);
			$recordModel->save();
			if (!empty($this->targetColumnParentId) && !$recordModel->isEmpty($this->targetColumnParentId)) {
				$this->updateFieldValue($recordModel->get($this->targetColumnParentId));
			}
		}
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
		if (!$this->checkIsConfigured()) {
			return;
		}
		if (!$recordModel->isNew()) {
			$this->addToQueueWhenSourceFieldHasBeenModified($recordModel);
			$this->addToQueueWhenParentHasBeenModified($recordModel);
		} elseif (!empty($this->targetTableId)) {
			$this->addToQueue($recordModel->get($this->targetTableId));
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
		if ($this->checkIsConfigured() && !empty($this->targetTableId)) {
			$this->addToQueue($recordModel->get($this->targetTableId));
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
		if ($this->checkIsConfigured() && !empty($this->targetTableId)) {
			$this->addToQueue($recordModel->get($this->targetTableId));
		}
	}

	/**
	 * Add to the queue when the source field has been modified.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	private function addToQueueWhenSourceFieldHasBeenModified(\Vtiger_Record_Model $recordModel)
	{
		if (!empty($this->targetTableId) && $recordModel->getPreviousValue($this->sourceField)) {
			$this->addToQueue($recordModel->get($this->targetTableId));
		}
	}

	/**
	 * Add to the queue when the parent has been modified.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	private function addToQueueWhenParentHasBeenModified(\Vtiger_Record_Model $recordModel)
	{
		if (!empty($this->targetColumnParentId) && false !== ($previousValue = $recordModel->getPreviousValue($this->targetColumnParentId))) {
			if (!empty($previousValue)) {
				$this->addToQueueSubordinateModule($previousValue);
			}
			if (!$recordModel->isEmpty($this->targetColumnParentId)) {
				$this->addToQueueSubordinateModule($recordModel->get($this->targetColumnParentId));
			}
		}
	}

	/**
	 * Add to queue subordinate module.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	private function addToQueueSubordinateModule(int $recordId)
	{
		$config = $this->getConfig('target_module');
		if (false !== $config) {
			(new \App\BatchMethod([
				'method' => static::class . '::update',
				'params' => [\App\Module::getModuleName($config['source_module']), $recordId],
			]))->save();
		}
	}

	/**
	 * Add to queue.
	 *
	 * @param int $recordId
	 *
	 * @return void
	 */
	private function addToQueue(int $recordId)
	{
		(new \App\BatchMethod(['method' => static::class . '::update', 'params' => [$this->sourceModuleName, $recordId]]))->save();
	}

	/**
	 * Get config.
	 *
	 * @param string $column
	 * @param mixed  $value
	 *
	 * @return array|false
	 */
	private function getConfig(string $column = 'source_module')
	{
		$config = false;
		$cacheKey = "{$this->sourceModuleName}.{$column}";
		if (\App\Cache::has('RecordFlowUpdater.getConfig', $cacheKey)) {
			$config = \App\Cache::get('RecordFlowUpdater.getConfig', $cacheKey);
		} else {
			$config = (new \App\Db\Query())
				->from('s_#__auto_record_flow_updater')
				->where(['status' => 1])
				->andWhere([$column => \App\Module::getModuleId($this->sourceModuleName)])
				->one();
			\App\Cache::save('RecordFlowUpdater.getConfig', $column, $cacheKey);
		}
		return $config;
	}

	/**
	 * Execute rules and return the processed value.
	 *
	 * @param int $recordId
	 *
	 * @return bool|string
	 */
	private function executeRules(int $recordId)
	{
		$itmes = array_merge($this->getValuesFromSource($recordId), $this->getChildrenValues($recordId));
		$returnedValue = (new RulesPicklist($this->rules, $this->defaultValue))->getValue($itmes);
		if (false === $returnedValue) {
			\App\Log::warning(
				"There is no rule defined for this case recordId: {$recordId} module: {$this->sourceModuleName}. Statuses:" .
				implode(',', array_map(function ($item) {
					return $item['status'];
				}, $itmes))
			);
		}
		return $returnedValue;
	}

	/**
	 * Get the values of children.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	private function getChildrenValues(int $recordId): array
	{
		$items = [];
		if (empty($this->targetColumnParentId)) {
			$items = [];
		} elseif (\App\Cache::staticHas('RecordFlowUpdater.getChildrenValues', $recordId)) {
			$items = \App\Cache::staticGet('RecordFlowUpdater.getChildrenValues', $recordId);
		} else {
			$columnId = "{$this->targetTable}.{$this->targetTableId}";
			$items = $this->getStatusFromPicklist(
				(new \App\Db\Query())
					->select(['id' => $columnId, 'status' => "{$this->targetTable}.{$this->targetField}"])
					->from($this->targetTable)
					->innerJoin('vtiger_crmentity', "{$columnId} = vtiger_crmentity.crmid")
					->where(['vtiger_crmentity.deleted' => [0, 2]])
					->andWhere(["{$this->targetTable}.{$this->targetColumnParentId}" => $recordId])
					->all()
			);
			\App\Cache::staticSave('RecordFlowUpdater.getChildrenValues', $recordId, $items);
		}
		return $items;
	}

	/**
	 * Get values from the source.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	private function getValuesFromSource(int $recordId): array
	{
		$columnId = "{$this->sourceTable}.{$this->sourceTableId}";
		return $this->getStatusFromPicklist(
				(new \App\Db\Query())
					->select(['id' => $columnId, 'status' => "{$this->sourceTable}." . $this->sourceField])
					->from($this->sourceTable)
					->innerJoin('vtiger_crmentity', "{$columnId} = vtiger_crmentity.crmid")
					->where(['vtiger_crmentity.deleted' => [0, 2]])
					->andWhere(["{$this->sourceTable}.{$this->targetTableId}" => $recordId])
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
		foreach (\App\Fields\Picklist::getValues($this->sourceField) as $item) {
			$picklist[$item['picklistValue']] = $item;
		}
		foreach ($items as &$item) {
			$item['automation'] = $picklist[$item['status']]['automation'];
		}
		return $items;
	}
}
