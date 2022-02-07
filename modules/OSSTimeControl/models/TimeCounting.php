<?php

/**
 * The file contains: CalculateSumOfExecutionTime class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * Field model name sum of time.
	 *
	 * @var \Vtiger_Field_Model
	 */
	private $fieldModelSumTime;

	/**
	 * Field model name sum of time subordinate.
	 *
	 * @var \Vtiger_Field_Model
	 */
	private $fieldModelSumTimeSubordinate;

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
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->fieldModelSumTime = $moduleModel->getFieldByColumn(static::COLUMN_SUM_TIME);
		$this->isActiveSumTime = $this->fieldModelSumTime && $this->fieldModelSumTime->isActiveField();
		if ($this->isActiveSumTime) {
			$this->primaryKey = $moduleModel->getEntityInstance()->table_index;
			$this->columnNameParentId = \App\Field::getRelatedFieldForModule($moduleName, $moduleName)['columnname'] ?? null;
			if ($this->columnNameParentId) {
				$this->fieldModelSumTimeSubordinate = $moduleModel->getFieldByColumn(static::COLUMN_SUM_TIME_SUBORDINATE);
				$this->isActiveSumTimeSubordinate = $this->fieldModelSumTimeSubordinate && $this->fieldModelSumTimeSubordinate->isActiveField();
			}
		}
	}

	/**
	 * Recalculate time control by record.
	 *
	 * @param string $moduleName
	 * @param int    $recordId
	 * @param string $relationField
	 *
	 * @return void
	 */
	public static function recalculate(string $moduleName, int $recordId, string $relationField): void
	{
		(new self($moduleName, $recordId, $relationField))->recalculateTimeControl();
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
					$this->fieldModelSumTime->getTableName(),
					[static::COLUMN_SUM_TIME => $this->getSumTime()],
					[$this->primaryKey => $this->recordId]
				)->execute();
			if ($this->isActiveSumTimeSubordinate) {
				$this->calculate($this->recordId);
			}
		}
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
				->sum($this->fieldModelSumTime->getColumnName()),
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
				$this->fieldModelSumTimeSubordinate->getTableName(),
			[$this->fieldModelSumTimeSubordinate->getColumnName() => $sumTime],
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
			->sum($this->fieldModelSumTimeSubordinate->getColumnName());
	}
}
