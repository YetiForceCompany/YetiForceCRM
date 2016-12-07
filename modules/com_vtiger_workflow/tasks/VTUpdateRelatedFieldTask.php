<?php
/**
 * Update Related Field Task Handler Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTUpdateRelatedFieldTask extends VTTask
{

	public $executeImmediately = false;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$util = new VTWorkflowUtils();
		$util->adminUser();

		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!empty($fieldValueMapping)) {
			$util->loggedInUser();
			foreach ($fieldValueMapping as $fieldInfo) {
				$reletedData = $fieldInfo['fieldname'];
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
				if (!empty($fieldValue) || $fieldValue == 0) {
					$this->updateRecords($recordModel, $reletedData, $fieldValue);
				}
			}
		}
		$util->revertUser();
	}

	function updateRecords($recordModel, $relatedData, $fieldValue)
	{
		$reletedDataEx = explode('::', $relatedData);
		$reletedModuleName = $reletedDataEx[0];
		$reletedFieldName = $reletedDataEx[1];
		$targetModel = Vtiger_RelationListView_Model::getInstance($recordModel, $reletedModuleName);
		if (!$targetModel->getRelationModel()) {
			return false;
		}
		$dataReader = $targetModel->getRelationQuery()->select(['vtiger_crmentity.crmid'])
				->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $reletedModuleName);
			$recordModel->setHandlerExceptions(['disableWorkflow' => true]);
			$recordModel->set($reletedFieldName, $fieldValue);
			$recordModel->save();
		}
	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($recordModel)
	{
		$this->contents = true;
		return $this->contents;
	}
}
