<?php

/**
 * The file contains: CalculateSumOfExecutionTime class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * CalculateSumOfExecutionTime class.
 */
class OSSTimeControl_TimeCounting_Model
{
	/**
	 * Recalculate status.
	 */
	const RECALCULATE_STATUS = 'Accepted';

	/**
	 * Column sum time.
	 */
	const COLUMN_SUM_TIME = 'sum_time';

	/**
	 * Column sum time subordinate.
	 */
	const COLUMN_SUM_TIME_SUBORDINATE = 'sum_time_subordinate';

	/**
	 * Module name.
	 *
	 * @var string
	 */
	private $moduleName;

	/**
	 * Field name parent id.
	 *
	 * @var string
	 */
	private $columnNameParentId;

	/**
	 * Is active total execution time.
	 *
	 * @var bool
	 */
	private $isActiveSumTimeSubordinate = false;

	/**
	 * Is active sum of time.
	 *
	 * @var bool
	 */
	private $isActiveSumTime = false;

	/**
	 * Primary key of table.
	 *
	 * @var string
	 */
	private $primaryKey;

	/**
	 * Module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	private $moduleModel;

	/**
	 * Record ID.
	 *
	 * @var int
	 */
	private $recordId;

	/**
	 * Relation field.
	 *
	 * @var string
	 */
	private $relationField;

	/**
	 * Construct.
	 *
	 * @param string $moduleName
	 * @param int    $recordId
	 * @param string $relationField
	 */
	public function __construct(string $moduleName, int $recordId, string $relationField)
	{
		$this->recordId = $recordId;
		$this->relationField = $relationField;
		$this->moduleName = $moduleName;
		$this->moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->isActiveSumTime = $this->getFieldModel(static::COLUMN_SUM_TIME) && $this->getFieldModel(static::COLUMN_SUM_TIME)->isActiveField();
		if ($this->isActiveSumTime) {
			$this->primaryKey = $this->moduleModel->getEntityInstance()->table_index;
			$this->columnNameParentId = \App\Field::getRelatedFieldForModule($moduleName, $moduleName)['columnname'] ?? null;
			if ($this->columnNameParentId) {
				$this->isActiveSumTimeSubordinate = $this->getFieldModel(static::COLUMN_SUM_TIME_SUBORDINATE) && $this->getFieldModel(static::COLUMN_SUM_TIME_SUBORDINATE)->isActiveField();
			}
		}
	}

	/**
	 * Recalculate time control.
	 *
	 * @return void
	 */
	public function recalculateTimeControl()
	{
		if ($this->isActiveSumTime) {
			\App\Db::getInstance()
				->createCommand()
				->update(
					$this->getFieldModel(static::COLUMN_SUM_TIME)->getTableName(),
					[static::COLUMN_SUM_TIME => $this->getSumTime($this->recordId, $this->relationField)],
					[$this->primaryKey => $this->recordId]
				)->execute();
			if ($this->isActiveSumTimeSubordinate) {
				$this->calculate($this->recordId);
			}
		}
	}

	/**
	 * Get field model.
	 *
	 * @param string $columnName
	 *
	 * @return Vtiger_Field_Model|null
	 */
	private function getFieldModel(string $columnName): ?Vtiger_Field_Model
	{
		$fieldModel = $this->moduleModel->getFieldByColumn($columnName);
		return $fieldModel && $fieldModel->isActiveField() ? $fieldModel : null;
	}

	/**
	 * Get sum of time.
	 *
	 * @return float
	 */
	private function getSumTime(): float
	{
		return round(
			(float) (new \App\QueryGenerator('OSSTimeControl'))
				->createQuery()
				->andWhere(['osstimecontrol_status' => static::RECALCULATE_STATUS, $this->relationField => $this->recordId])
				->sum($this->getFieldModel(static::COLUMN_SUM_TIME)->getColumnName()),
			2
		);
	}

	/**
	 * Calculate.
	 *
	 * @param int   $recordId
	 * @param float $initialValue
	 *
	 * @return void
	 */
	private function calculate(int $recordId, float $initialValue = 0)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName);
		$this->update(
			$recordId,
			round(($initialValue + (float) $recordModel->get(static::COLUMN_SUM_TIME)), 2)
		);
		$parentId = $recordModel->get($this->columnNameParentId);
		if (!empty($parentId)) {
			$this->calculate($parentId, $this->getSumTimeOfChildren($parentId));
		}
	}

	/**
	 * Update.
	 *
	 * @param int   $recordId
	 * @param float $sumTime
	 *
	 * @return void
	 */
	private function update(int $recordId, float $sumTime)
	{
		\App\Db::getInstance()
			->createCommand()
			->update(
				$this->getFieldModel(static::COLUMN_SUM_TIME_SUBORDINATE)->getTableName(),
			[$this->getFieldModel(static::COLUMN_SUM_TIME_SUBORDINATE)->getColumnName() => $sumTime],
			[$this->primaryKey => $recordId]
		)->execute();
	}

	/**
	 * Get sum time of children.
	 *
	 * @param int $recordId
	 *
	 * @return float
	 */
	private function getSumTimeOfChildren(int $recordId): float
	{
		return (float) (new \App\QueryGenerator($this->moduleName))
			->createQuery()
			->andWhere([$this->columnNameParentId => $recordId])
			->sum($this->getFieldModel(static::COLUMN_SUM_TIME_SUBORDINATE)->getColumnName());
	}
}
