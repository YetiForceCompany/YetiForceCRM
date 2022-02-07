<?php
/**
 * Add filed values from related module fields Handler Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** {@inheritdoc} */
	public function getFieldNames()
	{
		return ['targetField', 'sourceField', 'conditions'];
	}

	/** {@inheritdoc} */
	public function doTask($recordModel)
	{
		[$referenceField, $moduleName, $fieldName] = explode('::', $this->targetField);
		$relationFieldModel = $recordModel->getModule()->getFieldByName($referenceField);
		$ids = [];
		if (!$recordModel->isEmpty($referenceField)) {
			$ids[] = $recordModel->get($referenceField);
		}
		if ($oldId = $recordModel->getPreviousValue($referenceField)) {
			$ids[] = $oldId;
		}
		foreach ($ids as $id) {
			if (\App\Record::isExists($id, $moduleName)) {
				$queryGenerator = new \App\QueryGenerator($recordModel->getModuleName());
				$queryGenerator->setField($this->sourceField);
				if (!empty($this->conditions)) {
					$queryGenerator->setConditions($this->conditions);
				}
				$queryGenerator->permissions = false;
				$query = $queryGenerator->createQuery();
				$query->andWhere([$relationFieldModel->getTableName() . '.' . $relationFieldModel->getColumnName() => $id, 'vtiger_crmentity.deleted' => 0]);
				$sourceFieldModel = $recordModel->getModule()->getFieldByName($this->sourceField);
				$columnSumValue = $query->sum($sourceFieldModel->getTableName() . '.' . $sourceFieldModel->getColumnName());
				$relatedModel = \Vtiger_Record_Model::getInstanceById($id, $moduleName);
				$relatedModel->set($fieldName, $columnSumValue ?? 0);
				$relatedModel->save();
			}
		}
	}
}
