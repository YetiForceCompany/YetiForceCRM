<?php
/**
 * Update Related Field Task Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTUpdateRelatedFieldTask extends VTTask
{
	public $executeImmediately = false;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!empty($fieldValueMapping)) {
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldValue = trim($fieldInfo['value']);
				switch ($fieldInfo['valuetype']) {
					case 'fieldname':
						$fieldValue = $recordModel->get($fieldValue);
						break;
					case 'expression':
						require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

						$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
						$expression = $parser->expression();
						$exprEvaluater = new VTFieldExpressionEvaluater($expression);
						$fieldValue = $exprEvaluater->evaluate($recordModel);
						break;
					default:
						if (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
							$fieldValue = $match[1];
							if ($fieldValue == 'true') {
								$fieldValue = '1';
							} else {
								$fieldValue = '0';
							}
						}
						break;
				}
				$relatedData = explode('::', $fieldInfo['fieldname']);
				if (count($relatedData) === 2) {
					if (!empty($fieldValue) || $fieldValue == 0) {
						$this->updateRecords($recordModel, $relatedData, $fieldValue);
					}
				} else {
					$recordId = $recordModel->get($relatedData[0]);
					if ($recordId) {
						$relRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $relatedData[1]);
						$fieldModel = $relRecordModel->getField($relatedData[2]);
						if ($fieldModel->isEditable()) {
							$fieldModel->getUITypeModel()->validate($fieldValue);
							$relRecordModel->setHandlerExceptions(['disableWorkflow' => true]);
							$relRecordModel->set($relatedData[2], $fieldValue);
							$relRecordModel->save();
						} else {
							\App\Log::warning('No permissions to edit field: ' . $fieldModel->getName());
						}
					}
				}
			}
		}
	}

	/**
	 * Update related records by releted module.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 * @param string[]            $relatedData
	 * @param string              $fieldValue
	 *
	 * @return bool
	 */
	private function updateRecords($recordModel, $relatedData, $fieldValue)
	{
		$relatedModuleName = $relatedData[0];
		$relatedFieldName = $relatedData[1];
		$targetModel = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);
		if (!$targetModel || !$targetModel->getRelationModel()) {
			return false;
		}
		$dataReader = $targetModel->getRelationQuery()->select(['vtiger_crmentity.crmid'])
			->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $relatedModuleName);
			$fieldModel = $recordModel->getField($relatedFieldName);
			if ($fieldModel->isEditable()) {
				$fieldModel->getUITypeModel()->validate($fieldValue);
				$recordModel->setHandlerExceptions(['disableWorkflow' => true]);
				$recordModel->set($relatedFieldName, $fieldValue);
				$recordModel->save();
			} else {
				\App\Log::warning('No permissions to edit field: ' . $fieldModel->getName());
			}
		}
	}

	/**
	 * Function to get contents of this task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return bool contents
	 */
	public function getContents($recordModel)
	{
		$this->contents = true;

		return $this->contents;
	}
}
