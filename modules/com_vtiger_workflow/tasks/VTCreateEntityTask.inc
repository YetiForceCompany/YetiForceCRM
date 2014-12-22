<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTCreateEntityTask extends VTTask {

	public $executeImmediately = true;

	public function getFieldNames() {
		return array('entity_type', 'reference_field', 'field_value_mapping');
	}

	public function doTask($entity) {
		global $adb, $current_user;
		$util = new VTWorkflowUtils();

		$admin = $util->adminUser();
		$moduleName = $entity->getModuleName();
		$entityId = $entity->getId();
		$recordId = vtws_getIdComponents($entityId);
		$recordId = $recordId[1];

		$entityType = $this->entity_type;		
		if(!vtlib_isModuleActive($entityType)) {
			return;
		}
        
		$fieldValueMapping = array();
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = Zend_Json::decode($this->field_value_mapping);
		}

		if (!empty($entityType) && !empty($fieldValueMapping) && count($fieldValueMapping) > 0) {
			require_once('data/CRMEntity.php');

			$newEntity = CRMEntity::getInstance($entityType);
			$newEntity->mode = '';
			$newEntityData = VTEntityData::fromCRMEntity($newEntity);
			$entityModuleHandler = vtws_getModuleHandlerFromName($entityType, $current_user);
			$handlerMeta = $entityModuleHandler->getMeta();
			$ownerFields = $handlerMeta->getOwnerFields();

			$focus = CRMEntity::getInstance($moduleName);
			$focus->id = $recordId;
			$focus->mode = 'edit';
			$focus->retrieve_entity_info($recordId, $moduleName);

			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$referenceModule = $fieldInfo['modulename'];
				$fieldType = '';
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);

				if ($fieldValueType == 'fieldname') {
					if ($referenceModule == $entityType) {
						$fieldValue = $newEntity->column_fields[$fieldValue];
					} else {
						$fieldValue = $focus->column_fields[$fieldValue];
					}
				} elseif ($fieldValueType == 'expression') {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					if ($referenceModule == $entityType) {
						$fieldValue = $exprEvaluater->evaluate($newEntityData);
					} else {
						$fieldValue = $exprEvaluater->evaluate($entity);
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
					$userId = getUserId_Ol($fieldValue);
					$groupId = getGrpId($fieldValue);

					if ($userId == 0 && $groupId == 0) {
						$fieldValue = $focus->column_fields[$fieldName];
					} else {
						$fieldValue = ($userId == 0) ? $groupId : $userId;
					}
				}

				$newEntity->column_fields[$fieldName] = $fieldValue;
			}
			$newEntity->column_fields[$this->reference_field] = $focus->id;
			// To handle cyclic process
			$newEntity->_from_workflow = true;
            
			$newEntity->save($entityType);
			relateEntities($focus, $moduleName, $recordId, $entityType, $newEntity->id);
		}

		$util->revertUser();
	}

}

?>