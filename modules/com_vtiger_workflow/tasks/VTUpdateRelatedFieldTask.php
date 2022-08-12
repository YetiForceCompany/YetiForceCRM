<?php
/**
 * Update Related Field Task Handler Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTUpdateRelatedFieldTask extends VTTask
{
	public $executeImmediately = false;

	/** {@inheritdoc} */
	public function getFieldNames()
	{
		return ['field_value_mapping', 'conditions'];
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
		if (!\App\Json::isEmpty($this->conditions)) {
			$conditions = \App\Json::decode($this->conditions) ?: [];
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
							if ('true' == $fieldValue) {
								$fieldValue = '1';
							} else {
								$fieldValue = '0';
							}
						}
						break;
				}
				$relatedData = explode('::', $fieldInfo['fieldname']);
				if (2 === \count($relatedData)) {
					if (!empty($fieldValue) || 0 == $fieldValue) {
						$this->updateRecords($recordModel, $relatedData, $fieldValue);
					}
				} else {
					$recordId = $recordModel->get($relatedData[0]);
					if ($recordId && \App\Record::isExists($recordId)) {
						$relRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $relatedData[1]);
						if (($condition = $conditions[$fieldInfo['fieldname']] ?? []) && !\App\Condition::checkConditions($condition, $relRecordModel)) {
							continue;
						}
						$fieldModel = $relRecordModel->getField($relatedData[2]);
						if ($fieldModel->isEditable()) {
							$fieldModel->getUITypeModel()->validate($fieldValue);
							$relRecordModel->set($relatedData[2], $fieldValue);
							if (false !== $relRecordModel->getPreviousValue($relatedData[2])) {
								$relRecordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
								$relRecordModel->save();
							}
						} else {
							\App\Log::warning('No permissions to edit field: ' . $fieldModel->getName());
						}
					} else {
						\App\Log::warning('Record not found: ' . $recordId);
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
		$queryGenerator = $targetModel->getRelationQuery(true);
		if (!\App\Json::isEmpty($this->conditions) && ($conditions = \App\Json::decode($this->conditions)[implode('::', $relatedData)] ?? [])) {
			$queryGenerator->setConditions($conditions);
		}
		$dataReader = $queryGenerator->clearFields()->setFields(['id'])->createQuery()->select(['vtiger_crmentity.crmid'])
			->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $relatedModuleName);
			$fieldModel = $recordModel->getField($relatedFieldName);
			if ($fieldModel->isEditable()) {
				$fieldModel->getUITypeModel()->validate($fieldValue);
				$recordModel->set($relatedFieldName, $fieldValue);
				if (false !== $recordModel->getPreviousValue($relatedFieldName)) {
					$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
					$recordModel->save();
				}
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

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getFieldNames() as $fieldName) {
			if ($request->has($fieldName)) {
				switch ($fieldName) {
					case 'conditions':
						$data = $request->getArray($fieldName, \App\Purifier::TEXT);
						foreach ($data as &$condition) {
							$condition = \App\Condition::getConditionsFromRequest($condition);
						}
						$value = \App\Json::encode($data);
						break;
					case 'field_value_mapping':
						$values = \App\Json::decode($request->getRaw($fieldName));
						if (\is_array($values)) {
							$value = \App\Json::encode($values);
						} else {
							$value = $request->getRaw($fieldName);
						}
						break;
					default:
						$value = '';
						break;
				}
				$this->{$fieldName} = $value;
			}
		}
	}
}
