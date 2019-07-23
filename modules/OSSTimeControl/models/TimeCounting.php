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
	const RECALCULATE_STATUS = 'Accepted';

	const COLUMN_SUM_TIME = 'sum_time';

	/**
	 * Module name.
	 *
	 * @var string
	 */
	private $moduleName;

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

	private $moduleModel;

	/**
	 * Calculate and update.
	 *
	 * @param int    $recordId
	 * @param string $relationField
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public static function calculateAndUpdate(int $recordId, string $moduleName, string $relationField)
	{
		$instance = new self($moduleName);
		$instance->recalculateTimeControl($recordId, $relationField);
	}

	/**
	 * Construct.
	 *
	 * @param string $moduleName
	 */
	public function __construct(string $moduleName)
	{
		$this->moduleName = $moduleName;
		$this->moduleModel = $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->fieldModelSumTime = $moduleModel->getFieldByColumn(static::COLUMN_SUM_TIME);
		$this->isActiveSumTime = $this->fieldModelSumTime && $this->fieldModelSumTime->isActiveField();
		if ($this->isActiveSumTime) {
			$this->primaryKey = $moduleModel->getEntityInstance()->table_index;
			$this->columnNameParentId = \App\Field::getRelatedFieldForModule($moduleName, $moduleName)['columnname'] ?? null;
			if ($this->columnNameParentId) {
				$this->fieldModelSumTimeSubordinate = $moduleModel->getFieldByColumn('sum_time_subordinate');
				$this->isActiveSumTimeSubordinate = $this->fieldModelSumTimeSubordinate && $this->fieldModelSumTimeSubordinate->isActiveField();
			}
		}
	}

	/**
	 * Recalculate time control.
	 *
	 * @param int    $recordId
	 * @param string $relationField
	 *
	 * @return void
	 */
	public function recalculateTimeControl(int $recordId, string $relationField)
	{
		if ($this->isActiveSumTime) {
			\App\Db::getInstance()
				->createCommand()
				->update(
					$this->fieldModelSumTime->getTableName(),
					[static::COLUMN_SUM_TIME => $this->getSumTime($recordId, $relationField)],
					[$this->primaryKey => $recordId]
				)->execute();
			if ($this->isActiveSumTimeSubordinate) {
				$this->calculate($recordId);
			}
		}
	}

	/**
	 * Get sum of time.
	 *
	 * @param int    $recordId
	 * @param string $relationField
	 *
	 * @return float
	 */
	private function getSumTime(int $recordId, string $relationField): float
	{
		return round(
			(float) (new \App\Db\Query())
				->from('vtiger_osstimecontrol')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid')
				->where(['vtiger_crmentity.deleted' => 0, 'osstimecontrol_status' => static::RECALCULATE_STATUS, $relationField => $recordId])
				->sum(static::COLUMN_SUM_TIME),
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
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$queryGenerator->setFields([$this->fieldModelSumTime->getFieldName(), $this->columnNameParentId]);
		$queryGenerator->addCondition('id', $recordId, 'e');
		$row = $queryGenerator->createQuery()->one();
		$this->update(
			$recordId,
			round(($initialValue + $row[$this->fieldModelSumTime->getColumnName()]), 2)
		);
		$parentId = $row[$this->columnNameParentId];
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
