<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Workflows_EditTask_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->getInteger('task_id', '');
		$workflowId = $request->getInteger('for_workflow');

		$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
		$taskTypes = $workflowModel->getTaskTypes();
		if ($recordId) {
			$taskModel = Settings_Workflows_TaskRecord_Model::getInstance($recordId);
		} else {
			$taskType = $request->getByType('type', 'Alnum');
			if (empty($taskType)) {
				$taskType = !empty($taskTypes[0]) ? $taskTypes[0]->getName() : 'VTEmailTask';
			}
			$taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $taskType);
		}
		$taskTypeModel = $taskModel->getTaskType();
		$viewer->assign('TASK_TYPE_MODEL', $taskTypeModel);
		$viewer->assign('TASK_TEMPLATE_PATH', $taskTypeModel->getTemplatePath());
		$moduleModel = $workflowModel->getModule();
		$sourceModule = $moduleModel->getName();
		$dateTimeFields = $moduleModel->getFieldsByType(['date', 'datetime']);

		$taskObject = $taskModel->getTaskObject();
		$taskType = get_class($taskObject);
		if ($taskType === 'VTCreateEntityTask') {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $sourceModule);
			$mfModel = new $handlerClass();
			$viewer->assign('TEMPLATES_MAPPING', $mfModel->getTemplatesByModule($sourceModule));
			if (!empty($taskObject->entity_type) && $taskObject->field_value_mapping) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

				$fieldMapping = \App\Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
						if ($mappingInfo['value'] == 'assigned_user_id') {
							$fieldMapping[$key]['valuetype'] = 'fieldname';
						} else {
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
		if ($taskType === 'VTUpdateFieldsTask') {
			if ($sourceModule === 'Documents') {
				$restrictFields = ['folderid', 'filename', 'filelocationtype'];
				$viewer->assign('RESTRICTFIELDS', $restrictFields);
			}
		}
		if ($taskType === 'VTEmailTemplateTask') {
			$relations = \App\Field::getRelatedFieldForModule($sourceModule);
			$documentsModel = Vtiger_Module_Model::getInstance('Documents');
			$relationsWithDocuments = [];
			foreach ($relations as $relatedModuleName => $info) {
				$documentsRelations = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($relatedModuleName), $documentsModel);
				if ($documentsRelations !== false) {
					$relationsWithDocuments[$info['fieldname']][$info['relmod']]= Vtiger_Field_Model::getInstance($info['fieldid']);
				}
			}
			$documentsRelations = Vtiger_Relation_Model::getInstance($moduleModel, $documentsModel);
			$documents = false;
			if ($documentsRelations !== false) {
				$documents = true;
			}
			$viewer->assign('DOCUMENTS_RELATED_MODULLES', $relationsWithDocuments);
			$viewer->assign('DOCUMENTS_MODULLES', $documents);
		}
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('TASK_ID', $recordId);
		$viewer->assign('WORKFLOW_ID', $workflowId);
		$viewer->assign('DATETIME_FIELDS', $dateTimeFields);
		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		$viewer->assign('TASK_TYPES', $taskTypes);
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
