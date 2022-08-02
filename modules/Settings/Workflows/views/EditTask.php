<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Workflows_EditTask_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('task_id') && !Settings_Workflows_TaskRecord_Model::getInstance($request->getInteger('task_id'))->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->getInteger('task_id', '');
		$workflowId = $request->getInteger('for_workflow');

		$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
		if ($recordId) {
			$taskModel = Settings_Workflows_TaskRecord_Model::getInstance($recordId);
		} else {
			$taskType = $request->getByType('type', 'Alnum');
			$taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $taskType);
		}
		$taskTypeModel = $taskModel->getTaskType();
		$viewer->assign('TASK_TYPE_MODEL', $taskTypeModel);
		$viewer->assign('TASK_TEMPLATE_PATH', $taskTypeModel->getTemplatePath());
		$moduleModel = $workflowModel->getModule();
		$sourceModule = $moduleModel->getName();
		$dateTimeFields = $moduleModel->getFieldsByType(['date', 'datetime']);

		$taskObject = $taskModel->getTaskObject();
		$taskType = \get_class($taskObject);
		if ('VTCreateEntityTask' === $taskType) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $sourceModule);
			$mfModel = new $handlerClass();
			$viewer->assign('TEMPLATES_MAPPING', $mfModel->getTemplatesByModule($sourceModule));
			$viewer->assign('REFERENCE_FIELD_NAME', $taskObject->reference_field ?? '');
			if (!empty($taskObject->entity_type) && $taskObject->field_value_mapping) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');
				$fieldMapping = \App\Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (\array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
						if ('assigned_user_id' == $mappingInfo['value']) {
							$fieldMapping[$key]['valuetype'] = 'fieldname';
						} elseif ('triggerUser' !== $mappingInfo['value']) {
							$userRecordModel = Users_Record_Model::getInstanceByName($mappingInfo['value']);
							if ($userRecordModel) {
								$ownerName = $userRecordModel->getId();
							} else {
								$groupRecordModel = Settings_Groups_Record_Model::getInstance($mappingInfo['value']);
								$ownerName = $groupRecordModel->getId();
							}
							$fieldMapping[$key]['value'] = $ownerName;
						}
					}
				}
				$taskObject->field_value_mapping = \App\Json::encode($fieldMapping);
			}
		}
		if ('VTUpdateFieldsTask' === $taskType || 'VTUpdateRelatedFieldTask' === $taskType) {
			$restrictFields = [];
			if ('Documents' === $sourceModule) {
				$restrictFields = ['folderid', 'filename', 'filelocationtype'];
			}
			$viewer->assign('RESTRICTFIELDS', $restrictFields);
		}
		if ('SumFieldFromDependent' === $taskType) {
			$recordStructureModulesField = [];
			foreach ($moduleModel->getFieldsByReference() as $referenceField) {
				foreach ($referenceField->getReferenceList() as $relatedModuleName) {
					$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
				}
			}
			$viewer->assign('ADVANCE_CRITERIA', $taskObject->conditions ?? []);
			$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
			$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel)->getStructure());
		}
		if ('VTEmailTemplateTask' === $taskType) {
			$relations = \App\Field::getRelatedFieldForModule($sourceModule);
			$documentsModel = Vtiger_Module_Model::getInstance('Documents');
			$relationsWithDocuments = [];
			foreach ($relations as $relatedModuleName => $info) {
				$documentsRelations = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($relatedModuleName), $documentsModel);
				if (false !== $documentsRelations) {
					$relationsWithDocuments[$info['fieldname']][$info['relmod']] = Vtiger_Field_Model::getInstance($info['fieldid']);
				}
			}
			$documentsRelations = Vtiger_Relation_Model::getInstance($moduleModel, $documentsModel);
			$documents = false;
			if (false !== $documentsRelations) {
				$documents = true;
			}
			$viewer->assign('DOCUMENTS_RELATED_MODULLES', $relationsWithDocuments);
			$viewer->assign('DOCUMENTS_MODULLES', $documents);
			$relationsEmails = [];
			foreach ($moduleModel->getRelations() as $relation) {
				if (!\in_array($relation->get('relatedModuleName'), [$sourceModule, 'Documents', 'OSSMailView'])) {
					foreach ($relation->getRelationModuleModel()->getFieldsByType('email') as $key => $field) {
						$label = $relationsEmails[$relation->get('relatedModuleName') . '::' . $key] = \App\Language::translate($relation->get('relatedModuleName'), $relation->get('relatedModuleName')) . ' - ' . \App\Language::translate($field->getFieldLabel(), $relation->get('relatedModuleName'));
						$relationsEmails[$relation->get('relatedModuleName') . '::' . $key . '::first'] = $label . ' [' . \App\Language::translate('LBL_ONLY_TO_FIRST_LIST', $qualifiedModuleName) . ']';
					}
				} elseif ('OSSMailView' === $relation->get('relatedModuleName')) {
					$relationsEmails['OSSMailView::from_email::first'] = \App\Language::translate('OSSMailView', 'OSSMailView') . ' - ' . \App\Language::translate('From', 'OSSMailView') . ' [' . \App\Language::translate('LBL_ONLY_TO_FIRST_LIST', $qualifiedModuleName) . ']';
				}
			}
			$viewer->assign('RELATED_RECORDS_EMAIL', $relationsEmails);
		}
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('TASK_ID', $recordId);
		$viewer->assign('WORKFLOW_ID', $workflowId);
		$viewer->assign('DATETIME_FIELDS', $dateTimeFields);
		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		$viewer->assign('TASK_MODEL', $taskModel);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		// Adding option Line Item block for Individual tax mode
		$individualTaxBlockLabel = \App\Language::translate('LBL_LINEITEM_BLOCK_GROUP', $qualifiedModuleName);
		$individualTaxBlockValue = $viewer->view('LineItemsGroupTemplate.tpl', $qualifiedModuleName, true);

		// Adding option Line Item block for group tax mode
		$groupTaxBlockLabel = \App\Language::translate('LBL_LINEITEM_BLOCK_INDIVIDUAL', $qualifiedModuleName);
		$groupTaxBlockValue = $viewer->view('LineItemsIndividualTemplate.tpl', $qualifiedModuleName, true);

		$templateVariables = [
			$individualTaxBlockValue => $individualTaxBlockLabel,
			$groupTaxBlockValue => $groupTaxBlockLabel,
		];
		$viewer->assign('TEMPLATE_VARIABLES', $templateVariables);
		$viewer->assign('TASK_OBJECT', $taskObject);
		$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
		$userModel = \App\User::getCurrentUserModel();
		$viewer->assign('dateFormat', $userModel->getDetail('date_format'));
		$viewer->assign('timeFormat', $userModel->getDetail('hour_format'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$emailFieldoptions = [];
		$textParser = App\TextParser::getInstance($sourceModule);
		foreach ($textParser->getRecordVariable('email') as $blockName => $fields) {
			$blockName = \App\Language::translate($blockName, $sourceModule);
			foreach ($fields as $field) {
				$emailFieldoptions[$blockName][$field['var_value']] = \App\Language::translate($field['label'], $sourceModule);
			}
		}
		foreach ($textParser->getRelatedVariable('email') as $modules) {
			foreach ($modules as $blockName => $fields) {
				$blockName = \App\Language::translate($blockName, $sourceModule);
				foreach ($fields as $field) {
					$emailFieldoptions[$blockName][$field['var_value']] = \App\Language::translate($field['label'], $sourceModule);
				}
			}
		}
		$fromEmailFieldOptions = array_merge(['' => ['' => \App\Language::translate('Optional', $qualifiedModuleName)]], $emailFieldoptions);
		$assignedToValues = [
			\App\Language::translate('LBL_USERS') => \App\Fields\Owner::getInstance()->getAccessibleUsers(),
			\App\Language::translate('LBL_GROUPS') => \App\Fields\Owner::getInstance()->getAccessibleGroups(),
		];
		$viewer->assign('TEXT_PARSER', $textParser);
		$viewer->assign('ASSIGNED_TO', $assignedToValues);
		$viewer->assign('EMAIL_FIELD_OPTION', $emailFieldoptions);
		$viewer->assign('FROM_EMAIL_FIELD_OPTION', $fromEmailFieldOptions);
		$viewer->view('EditTask.tpl', $qualifiedModuleName);
	}
}
