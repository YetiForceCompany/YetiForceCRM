<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTCreateEntityTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['entity_type', 'reference_field', 'field_value_mapping', 'mappingPanel'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		$entityType = $this->entity_type;
		if (!\App\Module::isModuleActive($entityType)) {
			return;
		}
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!empty($entityType) && !empty($fieldValueMapping) && count($fieldValueMapping) > 0 && !$this->mappingPanel) {
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$ownerFields = array_keys($newRecordModel->getModule()->getFieldsByType('owner'));
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$referenceModule = $fieldInfo['modulename'];
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);

				if ($fieldValueType === 'fieldname') {
					if ($referenceModule === $entityType) {
						$fieldValue = $newRecordModel->get($fieldValue);
					} else {
						$fieldValue = $recordModel->get($fieldValue);
					}
				} elseif ($fieldValueType === 'expression') {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					if ($referenceModule === $entityType) {
						$fieldValue = $exprEvaluater->evaluate($newRecordModel);
					} else {
						$fieldValue = $exprEvaluater->evaluate($recordModel);
					}
				} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
					$fieldValue = $match[1];
					if ($fieldValue == 'true') {
						$fieldValue = '1';
					} else {
						$fieldValue = '0';
					}
				}
				if (in_array($fieldName, $ownerFields) && !is_numeric($fieldValue)) {
					$userId = App\User::getUserIdByName($fieldValue);
					$groupId = \App\Fields\Owner::getGroupId($fieldValue);
					if (!$userId && !$groupId) {
						$fieldValue = $recordModel->get($fieldName);
					} else {
						$fieldValue = (!$userId) ? $groupId : $userId;
					}
				}
				$newRecordModel->set($fieldName, $fieldValue);
			}
			$newRecordModel->set($this->reference_field, $recordId);
			// To handle cyclic process
			$newRecordModel->setHandlerExceptions(['disableWorkflow' => true]);
			$newRecordModel->save();
			vtlib\Deprecated::relateEntities($recordModel->getEntity(), $moduleName, $recordId, $entityType, $newRecordModel->getId());
		} elseif ($entityType && $this->mappingPanel) {
			$saveContinue = true;
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$newRecordModel->setRecordFieldValues($parentRecordModel);
			$mandatoryFields = $newRecordModel->getModule()->getMandatoryFieldModels();
			if (!empty($fieldValueMapping) && is_array($fieldValueMapping)) {
				$newRecordModel = $this->setFieldMapping($fieldValueMapping, $newRecordModel, $parentRecordModel);
			}
			foreach ($mandatoryFields as $field) {
				if (empty($newRecordModel->get($field->getName()))) {
					$saveContinue = false;
				}
			}
			if ($saveContinue) {
				$newRecordModel->save();
			}
		}
	}

	public function setFieldMapping($fieldValueMapping, $recordModel, $parentRecordModel)
	{
		$ownerFields = [];
		$entityType = $this->entity_type;
		foreach ($recordModel->getModule()->getFields() as $name => $fieldModel) {
			if ($fieldModel->getFieldDataType() === 'owner') {
				$ownerFields[] = $name;
			}
		}
		foreach ($fieldValueMapping as $fieldInfo) {
			$fieldName = $fieldInfo['fieldname'];
			$referenceModule = $fieldInfo['modulename'];
			$fieldValueType = $fieldInfo['valuetype'];
			$fieldValue = trim($fieldInfo['value']);

			if ($fieldValueType === 'fieldname') {
				if ($referenceModule === $entityType) {
					$fieldValue = $recordModel->get($fieldValue);
				} else {
					$fieldValue = $parentRecordModel->get($fieldValue);
				}
			} elseif ($fieldValueType == 'expression') {
				require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

				$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
				$expression = $parser->expression();
				$exprEvaluater = new VTFieldExpressionEvaluater($expression);
				if ($referenceModule === $entityType) {
					$fieldValue = $exprEvaluater->evaluate($recordModel);
				} else {
					$fieldValue = $exprEvaluater->evaluate($parentRecordModel);
				}
			} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
				$fieldValue = $match[1];
				if ($fieldValue == 'true') {
					$fieldValue = 1;
				} else {
					$fieldValue = 0;
				}
			}
			if (in_array($fieldName, $ownerFields) && !is_numeric($fieldValue)) {
				$userId = App\User::getUserIdByName($fieldValue);
				$groupId = \App\Fields\Owner::getGroupId($fieldValue);
				if (!$userId && !$groupId) {
					$fieldValue = $parentRecordModel->get($fieldName);
				} else {
					$fieldValue = (!$userId) ? $groupId : $userId;
				}
			}
			$recordModel->set($fieldName, $fieldValue);
		}
		return $recordModel;
	}
}
