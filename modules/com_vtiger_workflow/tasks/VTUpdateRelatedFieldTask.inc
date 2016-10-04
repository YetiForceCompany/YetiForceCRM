<?php
/**
 * Update Related Field Task Handler Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTUpdateRelatedFieldTask extends VTTask
{

	public $executeImmediately = false;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	public function doTask($entity)
	{
		$util = new VTWorkflowUtils();
		$util->adminUser();

		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \includes\utils\Json::decode($this->field_value_mapping);
		}
		if (!empty($fieldValueMapping)) {
			$util->loggedInUser();

			$moduleName = $entity->getModuleName();
			$entityId = $entity->getId();
			$recordId = vtws_getIdComponents($entityId);
			$recordId = $recordId[1];
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			foreach ($fieldValueMapping as $fieldInfo) {
				$reletedData = $fieldInfo['fieldname'];
				$fieldValue = trim($fieldInfo['value']);
				switch ($fieldInfo['valuetype']) {
					case 'fieldname':
						$fieldValue = $recordModel->get($fieldValue);
						break;
					case 'expression':
						require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

						$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
						$expression = $parser->expression();
						$exprEvaluater = new VTFieldExpressionEvaluater($expression);
						$fieldValue = $exprEvaluater->evaluate($entity);
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

	function updateRecords($recordModel, $reletedData, $fieldValue)
	{
		$reletedDataEx = explode('::', $reletedData);
		$reletedModuleName = $reletedDataEx[0];
		$reletedFieldName = $reletedDataEx[1];

		$relatedModuleModel = Vtiger_Module_Model::getInstance($reletedModuleName);
		$targetModel = Vtiger_RelationListView_Model::getInstance($recordModel, $reletedModuleName);
		if (!$targetModel->getRelationModel()) {
			return false;
		}
		$query = $targetModel->getRelationQuery();
		$explodedQuery = explode('FROM', $query, 2);
		$relationQuery = 'SELECT vtiger_crmentity.crmid FROM' . $explodedQuery[1];

		$db = PearDatabase::getInstance();
		$result = $db->query($relationQuery);
		while (($recordId = $db->getSingleValue($result)) !== false) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $reletedModuleName);
			$recordModel->set('mode', 'edit');
			$recordModel->set($reletedFieldName, $fieldValue);
			$recordModel->save();
		}
	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($entity, $entityCache = false)
	{
		$this->contents = true;
		return $this->contents;
	}
}
