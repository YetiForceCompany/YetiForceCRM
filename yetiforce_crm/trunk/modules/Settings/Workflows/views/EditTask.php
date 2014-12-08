<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Workflows_EditTask_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordId = $request->get('task_id');
		$workflowId = $request->get('for_workflow');

		$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);
		$taskTypes = $workflowModel->getTaskTypes();
		if($recordId) {
			$taskModel = Settings_Workflows_TaskRecord_Model::getInstance($recordId);
		} else {
			$taskType = $request->get('type');
			if(empty($taskType)) {
				$taskType = !empty($taskTypes[0]) ? $taskTypes[0]->getName() : 'VTEmailTask';
			}
			$taskModel = Settings_Workflows_TaskRecord_Model::getCleanInstance($workflowModel, $taskType);
		}

		$taskTypeModel = $taskModel->getTaskType();
		$viewer->assign('TASK_TYPE_MODEL', $taskTypeModel);

		$viewer->assign('TASK_TEMPLATE_PATH', $taskTypeModel->getTemplatePath());
		$recordStructureInstance = Settings_Workflows_RecordStructure_Model::getInstanceForWorkFlowModule($workflowModel,
																			Settings_Workflows_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDITTASK);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$moduleModel = $workflowModel->getModule();
		$dateTimeFields = $moduleModel->getFieldsByType(array('date', 'datetime'));

		$taskObject = $taskModel->getTaskObject();
		$taskType = get_class($taskObject);

		if ($taskType === 'VTCreateEntityTask') {
			if ($taskObject->entity_type) {
				$relationModuleModel = Vtiger_Module_Model::getInstance($taskObject->entity_type);
				$ownerFieldModels = $relationModuleModel->getFieldsByType('owner');

				$fieldMapping = Zend_Json::decode($taskObject->field_value_mapping);
				foreach ($fieldMapping as $key => $mappingInfo) {
					if (array_key_exists($mappingInfo['fieldname'], $ownerFieldModels)) {
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
				$taskObject->field_value_mapping = Zend_Json::encode($fieldMapping);
			}
		}
                 if ($taskType === 'VTUpdateFieldsTask') { 
                    if($moduleModel->getName() =="Documents"){ 
                        $restrictFields=array('folderid','filename','filelocationtype'); 
                        $viewer->assign('RESTRICTFIELDS',$restrictFields); 
                    } 
                } 
		
		$viewer->assign('SOURCE_MODULE',$moduleModel->getName());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('TASK_ID',$recordId);
		$viewer->assign('WORKFLOW_ID',$workflowId);
		$viewer->assign('DATETIME_FIELDS', $dateTimeFields);
		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		$viewer->assign('TASK_TYPES', $taskTypes);
		$viewer->assign('TASK_MODEL', $taskModel);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
        $metaVariables = Settings_Workflows_Module_Model::getMetaVariables();
        if($moduleModel->getName() == 'Invoice' || $moduleModel->getName() == 'Quotes') {
            $metaVariables['Portal Pdf Url'] = '(general : (__VtigerMeta__) portalpdfurl)';
        }
        
        // Adding option Line Item block for Individual tax mode
        $individualTaxBlockLabel = vtranslate("LBL_LINEITEM_BLOCK_GROUP", $qualifiedModuleName);
        $individualTaxBlockValue = $viewer->view('LineItemsGroupTemplate.tpl', $qualifiedModuleName, $fetch = true);

        // Adding option Line Item block for group tax mode
        $groupTaxBlockLabel = vtranslate("LBL_LINEITEM_BLOCK_INDIVIDUAL", $qualifiedModuleName);
        $groupTaxBlockValue = $viewer->view('LineItemsIndividualTemplate.tpl', $qualifiedModuleName, $fetch = true);

        $templateVariables = array(
            $individualTaxBlockValue => $individualTaxBlockLabel,
            $groupTaxBlockValue => $groupTaxBlockLabel
                );
        
		$viewer->assign('META_VARIABLES', $metaVariables);
        $viewer->assign('TEMPLATE_VARIABLES', $templateVariables);
		$viewer->assign('TASK_OBJECT', $taskObject);
		$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
		$repeat_date = $taskModel->getTaskObject()->calendar_repeat_limit_date;
		if(!empty ($repeat_date)){
		    $repeat_date = Vtiger_Date_UIType::getDisplayDateValue($repeat_date);
		}
		$viewer->assign('REPEAT_DATE',$repeat_date);
		
		$userModel = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('dateFormat',$userModel->get('date_format'));
        $viewer->assign('timeFormat', $userModel->get('hour_format'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		
		
		$emailFields = $recordStructureInstance->getAllEmailFields();
		foreach($emailFields as $metaKey => $emailField) {
			$emailFieldoptions .= '<option value=",$'.$metaKey.'">'.$emailField->get('workflow_columnlabel').'</option>';
		}

		$nameFields = $recordStructureInstance->getNameFields();
		$fromEmailFieldOptions = '<option value="">'. vtranslate('Optional', $qualifiedModuleName) .'</option>';
		$fromEmailFieldOptions .= '<option value="$(general : (__VtigerMeta__) supportName)<$(general : (__VtigerMeta__) supportEmailId)>"
									>'.vtranslate('LBL_HELPDESK_SUPPORT_EMAILID', $qualifiedModuleName).
									'</option>';

		foreach($emailFields as $metaKey => $emailField) {
			list($relationFieldName, $rest) = explode(' ', $metaKey);
			$value = '<$'.$metaKey.'>';

			if ($nameFields) {
				$nameFieldValues = '';
					foreach (array_keys($nameFields) as $fieldName) {
					if (strstr($fieldName, $relationFieldName) || (count(explode(' ', $metaKey)) === 1 && count(explode(' ', $fieldName)) === 1)) {
						$fieldName = '$'.$fieldName;
						$nameFieldValues .= ' '.$fieldName;
					}
				}
				$value = trim($nameFieldValues).$value;
			}

			$fromEmailFieldOptions .= '<option value="'.$value.'">'.$emailField->get('workflow_columnlabel').'</option>';
		}

		$structure = $recordStructureInstance->getStructure();
        // for inventory modules we shouldn't show item detail fields
        if($taskType == "VTEmailTask" && in_array($workflowModel->getModule()->name, getInventoryModules())){
            $itemsBlock = "LBL_ITEM_DETAILS";
            unset($structure[$itemsBlock]);
        }
		foreach($structure as $fields) {
			foreach($fields as $field) {
				$allFieldoptions .= '<option value="$'.$field->get('workflow_columnname').'">'.
										$field->get('workflow_columnlabel').'</option>';
			}
		}

		$userList = $currentUser->getAccessibleUsers();
		$groupList = $currentUser->getAccessibleGroups();
		$assignedToValues = array();
		$assignedToValues[vtranslate('LBL_USERS', 'Vtiger')] = $userList;
		$assignedToValues[vtranslate('LBL_GROUPS', 'Vtiger')] = $groupList;

		$viewer->assign('ASSIGNED_TO', $assignedToValues);
		$viewer->assign('EMAIL_FIELD_OPTION', $emailFieldoptions);
		$viewer->assign('FROM_EMAIL_FIELD_OPTION', $fromEmailFieldOptions);
		$viewer->assign('ALL_FIELD_OPTIONS',$allFieldoptions);
		$viewer->view('EditTask.tpl', $qualifiedModuleName);
	}
}