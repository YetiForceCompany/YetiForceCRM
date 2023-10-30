<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTCreateEntityTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['entity_type', 'reference_field', 'field_value_mapping', 'mappingPanel', 'verifyIfExists', 'relationId'];
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
		if (!$this->mappingPanel && !empty($entityType) && !empty($fieldValueMapping) && \count($fieldValueMapping) > 0) {
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$ownerFields = array_keys($newRecordModel->getModule()->getFieldsByType('owner'));
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$destinyModuleName = $this->getDestinyModuleName($fieldInfo['modulename']);
				$sourceModuleName = $destinyModuleName ?? $fieldInfo['modulename'];
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);
				if ('fieldname' === $fieldValueType) {
					if ($this->relationId) {
						$fieldValue = $destinyModuleName ? $newRecordModel->get($fieldValue) : $recordModel->get($fieldValue);
					} else {
						$fieldValue = $sourceModuleName === $entityType ? $newRecordModel->get($fieldValue) : $fieldValue = $recordModel->get($fieldValue);
					}
				} elseif ('expression' === $fieldValueType) {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					if ($sourceModuleName === $entityType) {
						$fieldValue = $exprEvaluater->evaluate($newRecordModel);
					} else {
						$fieldValue = $exprEvaluater->evaluate($recordModel);
					}
				} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
					$fieldValue = $match[1];
					if ('true' == $fieldValue) {
						$fieldValue = '1';
					} else {
						$fieldValue = '0';
					}
				} elseif (!\in_array($fieldName, $ownerFields)) {
					$fieldValue = $newRecordModel->getField($fieldName)->getUITypeModel()->getDBValue($fieldValue);
				}
				if (\in_array($fieldName, $ownerFields)) {
					if ('triggerUser' === $fieldValue) {
						$fieldValue = $recordModel->executeUser;
					} elseif (!is_numeric($fieldValue)) {
						$userId = App\User::getUserIdByName($fieldValue);
						$groupId = \App\Fields\Owner::getGroupId($fieldValue);
						if (!$userId && !$groupId) {
							$fieldValue = $recordModel->get($fieldName);
						} else {
							$fieldValue = (!$userId) ? $groupId : $userId;
						}
					}
				}
				$newRecordModel->set($fieldName, $fieldValue);
			}
			$newRecordModel->set($this->reference_field, $recordId);
			// To handle cyclic process
			$newRecordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
			$newRecordModel->save();

			$relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), $newRecordModel->getModule(), $this->relationId);
			if ($relationModel) {
				$relationModel->addRelation($recordModel->getId(), $newRecordModel->getId());
			}
		} elseif ($this->mappingPanel && $entityType) {
			if (!empty($this->verifyIfExists) && ($relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $entityType, false)) && (int) $relationListView->getRelatedEntriesCount() > 0) {
				return true;
			}
			$saveContinue = true;
			$newRecordModel = Vtiger_Record_Model::getCleanInstance($entityType);
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$newRecordModel->setRecordFieldValues($parentRecordModel);
			$mandatoryFields = $newRecordModel->getModule()->getMandatoryFieldModels();
			if (!empty($fieldValueMapping) && \is_array($fieldValueMapping)) {
				$newRecordModel = $this->setFieldMapping($fieldValueMapping, $newRecordModel, $parentRecordModel);
			}
			foreach ($mandatoryFields as $field) {
				if ('' === $newRecordModel->get($field->getName()) || null === $newRecordModel->get($field->getName())) {
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
			if ('owner' === $fieldModel->getFieldDataType()) {
				$ownerFields[] = $name;
			}
		}
		foreach ($fieldValueMapping as $fieldInfo) {
			$fieldName = $fieldInfo['fieldname'];
			$fieldValueType = $fieldInfo['valuetype'];
			$fieldValue = trim($fieldInfo['value']);
			$destinyModuleName = $this->getDestinyModuleName($fieldInfo['modulename']);
			$sourceModuleName = $destinyModuleName ?? $fieldInfo['modulename'];
			if ('fieldname' === $fieldValueType) {
				if ($this->relationId) {
					$fieldValue = $destinyModuleName ? $parentRecordModel->get($fieldValue) : $recordModel->get($fieldValue);
				} else {
					$fieldValue = $sourceModuleName === $entityType ? $fieldValue = $recordModel->get($fieldValue) : $fieldValue = $parentRecordModel->get($fieldValue);
				}
			} elseif ('expression' == $fieldValueType) {
				require_once 'modules/com_vtiger_workflow/expression_engine/include.php';

				$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
				$expression = $parser->expression();
				$exprEvaluater = new VTFieldExpressionEvaluater($expression);
				if ($sourceModuleName === $entityType) {
					$fieldValue = $exprEvaluater->evaluate($recordModel);
				} else {
					$fieldValue = $exprEvaluater->evaluate($parentRecordModel);
				}
			} elseif (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
				$fieldValue = $match[1];
				if ('true' == $fieldValue) {
					$fieldValue = 1;
				} else {
					$fieldValue = 0;
				}
			} elseif (!\in_array($fieldName, $ownerFields)) {
				$fieldValue = $recordModel->getField($fieldName)->getUITypeModel()->getDBValue($fieldValue);
			}
			if (\in_array($fieldName, $ownerFields) && !is_numeric($fieldValue)) {
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

	/**
	 * Get destiny module name.
	 *
	 * @param string $destinyModuleName
	 *
	 * @return string|null
	 */
	private function getDestinyModuleName(string $destinyModuleName): ?string
	{
		$moduleName = null;
		if (strpos($destinyModuleName, 'destinyModule') > 0) {
			$moduleName = explode('::', $destinyModuleName)[1];
		}
		return $moduleName;
	}
}
