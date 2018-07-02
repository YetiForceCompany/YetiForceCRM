<?php
/**
 * Add filed values from related module fields Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class SumFieldFromDependent extends VTTask
{
	/**
	 * @var bool
	 */
	public $executeImmediately = true;

	/**
	 * {@inheritdoc}
	 */
	public function getFieldNames()
	{
		return ['targetField', 'sourceField'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function doTask($recordModel)
	{
		list($referenceField, $moduleName, $fieldName) = explode('::', $this->targetField);
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$targetFieldModel = $moduleModel->getFieldByName($fieldName);
		$relationFieldModel = $recordModel->getModule()->getFieldByName($referenceField);
		$relationFieldValue = $recordModel->get($referenceField);
		if (!empty($relationFieldValue)) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setField($this->sourceField);
			$query = $queryGenerator->createQuery();
			$query->where([$relationFieldModel->getTableName() . '.' . $relationFieldModel->getColumnName() => $relationFieldValue, 'vtiger_crmentity.deleted' => 0]);
			$sourceFieldModel = $recordModel->getModule()->getFieldByName($this->sourceField);
			$columnSumValue = $query->sum($sourceFieldModel->getTableName() . '.' . $sourceFieldModel->getColumnName());
			\App\Db::getInstance()->createCommand()->update($targetFieldModel->getTableName(), [$targetFieldModel->getColumnName() => $columnSumValue], [$moduleModel->getEntityInstance()->tab_name_index[$targetFieldModel->getTableName()] => $relationFieldValue])->execute();
		}
	}
}
