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
class OSSTimeControl_CalculateSumOfExecutionTime_Model
{
	const RECALCULATE_STATUS = 'Accepted';

	/**
	 * Module name.
	 *
	 * @var string
	 */
	private $moduleName;

	/**
	 * Field name sum of time.
	 *
	 * @var string
	 */
	private $fieldNameSumOfTime;

	/**
	 * Field name parent id.
	 *
	 * @var string
	 */
	private $fieldNameParentId;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $tableName;

	/**
	 * Column name.
	 *
	 * @var string
	 */
	private $columnName;

	/**
	 * Is active total execution time.
	 *
	 * @var bool
	 */
	private $isActiveTotalExecutionTime = false;

	/**
	 * Is active sum of time.
	 *
	 * @var bool
	 */
	private $isActiveSumTime = false;

	/**
	 * Table index.
	 *
	 * @var string
	 */
	private $tableIndex;

	/**
	 * Calculate and update.
	 *
	 * @param int    $recordId
	 * @param string $name
	 *
	 * @return void
	 */
	public static function calculateAndUpdate(int $recordId, string $name)
	{
		$moduleName = 'HelpDesk';
		$instance = new self($moduleName);
		$instance->recalculateTimeControl($recordId, $name);
	}

	/**
	 * Construct.
	 *
	 * @param string $moduleName
	 */
	public function __construct(string $moduleName)
	{
		$this->moduleName = $moduleName;
		$this->fieldNameSumOfTime = 'sum_time';
		$this->fieldNameParentId = 'parentid';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->tableName = $moduleModel->basetable;
		$fieldModelTotalExecutionTime = \Vtiger_Field_Model::getInstance('total_execution_time', $moduleModel);
		if ($fieldModelTotalExecutionTime && $fieldModelTotalExecutionTime->isActiveField()) {
			$this->isActiveTotalExecutionTime = true;
			$this->columnName = $fieldModelTotalExecutionTime->getColumnName();
		}
		$fieldModelSumTime = \Vtiger_Field_Model::getInstance('sum_time', $moduleModel);
		$this->isActiveSumTime = $fieldModelSumTime && $fieldModelSumTime->isActiveField();
		$this->tableIndex = $moduleModel->getEntityInstance()->table_index;
	}

	/**
	 * Recalculate time control.
	 *
	 * @param int    $recordId
	 * @param string $name
	 *
	 * @return void
	 */
	public function recalculateTimeControl(int $recordId, string $name)
	{
		if ($this->isActiveSumTime) {
			\App\Db::getInstance()
				->createCommand()
				->update(
					$this->tableName,
					['sum_time' => $this->getSumTime($recordId, $name)],
					[$this->tableIndex => $recordId]
				)->execute();
			if ($this->isActiveTotalExecutionTime) {
				$this->calculate($recordId);
			}
		}
	}

	/**
	 * Get sum of time.
	 *
	 * @param int    $recordId
	 * @param string $name
	 *
	 * @return float
	 */
	private function getSumTime(int $recordId, string $name): float
	{
		return round(
			(float) (new \App\Db\Query())
				->from('vtiger_osstimecontrol')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid')
				->where(['vtiger_crmentity.deleted' => 0, 'osstimecontrol_status' => static::RECALCULATE_STATUS, $name => $recordId])
				->sum('sum_time'),
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
		$queryGenerator->setFields([$this->fieldNameSumOfTime, $this->fieldNameParentId]);
		$queryGenerator->addCondition('id', $recordId, 'e');
		$row = $queryGenerator->createQuery()->one();
		$this->update(
			$recordId,
			round(($initialValue + $row[$this->fieldNameSumOfTime]), 2)
		);
		$parentId = $row[$this->fieldNameParentId];
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
				$this->tableName,
			[$this->columnName => $sumTime],
			['ticketid' => $recordId]
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
			->andWhere([$this->fieldNameParentId => $recordId])
			->sum($this->columnName);
	}
}
