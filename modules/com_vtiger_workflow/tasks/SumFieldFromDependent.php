<?php
/**
 * Add filed values from related module fields Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		$relationFieldModel = $recordModel->getModule()->getFieldByName($referenceField);
		$ids = [];
		if (!$recordModel->isEmpty($referenceField)) {
			$ids[] = $recordModel->get($referenceField);
		}
		if ($oldId = $recordModel->getPreviousValue($referenceField)) {
			$ids[] = $oldId;
		}
		foreach ($ids as $id) {
			$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
			$queryGenerator->setField($this->sourceField);
			$query = $queryGenerator->createQuery();
			$query->where([$relationFieldModel->getTableName() . '.' . $relationFieldModel->getColumnName() => $id, 'vtiger_crmentity.deleted' => 0]);
			$sourceFieldModel = $recordModel->getModule()->getFieldByName($this->sourceField);
			$columnSumValue = $query->sum($sourceFieldModel->getTableName() . '.' . $sourceFieldModel->getColumnName());
			$relatedModel = \Vtiger_Record_Model::getInstanceById($id, $moduleName);
			$relatedModel->set($fieldName, $columnSumValue ?? 0);
			$relatedModel->save();
		}
	}
}
