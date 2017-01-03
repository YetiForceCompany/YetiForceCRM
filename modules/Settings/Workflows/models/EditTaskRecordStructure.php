<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Workflows_EditTaskRecordStructure_Model extends Settings_Workflows_RecordStructure_Model
{

	protected $no_skip = true;

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure($fieldDataType = false)
	{
		if ($fieldDataType) {
			$this->no_skip = false;
		}
		if (!empty($this->structuredValues) && $this->no_skip === true) {
			return $this->structuredValues;
		}

		$recordModel = $this->getWorkFlowModel();
		$recordId = $recordModel->getId();
		$values = [];

		$baseModuleModel = $moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					$dataType = true;
					if ($fieldDataType && ($fieldModel->getFieldDataType() != $fieldDataType)) {
						$dataType = false;
					}
					if ($fieldModel->isViewable() && $dataType) {
						if ($moduleModel->getName() == "Documents" && $fieldName == "filename") {
							continue;
						}
						if (in_array($moduleModel->getName(), array('Calendar', 'Events')) && $fieldModel->getDisplayType() == 3) {
							/* Restricting the following fields(Event module fields) for "Calendar" module
							 * time_start, time_end, eventstatus, activitytype,	visibility, duration_hours,
							 * duration_minutes, reminder_time, recurringtype, notime
							 */
							continue;
						}
						if (!empty($recordId)) {
							//Set the fieldModel with the valuetype for the client side.
							$fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
							$fieldInfo = $fieldModel->getFieldInfo();
							$fieldInfo['workflow_valuetype'] = $fieldValueType;
							$fieldModel->setFieldInfo($fieldInfo);
						}
						$name = "$(record : $fieldName)$";
						// This will be used during editing task like email, sms etc
						$fieldModel->set('workflow_columnname', $name)->set('workflow_columnlabel', vtranslate($moduleModel->getName(), $moduleModel->getName()) . ': ' . vtranslate($fieldModel->get('label'), $moduleModel->getName()));
						// This is used to identify the field belongs to source module of workflow
						$fieldModel->set('workflow_sourcemodule_field', true);
						$fieldModel->set('selectOption', $fieldName);
						$values[$blockLabel][$name] = clone $fieldModel;
					}
				}
			}
		}
		if ($moduleModel->isCommentEnabled()) {
			$commentFieldModelsList = Settings_Workflows_Field_Model::getCommentFieldsListForTasks($moduleModel);
			$labelName = vtranslate($moduleModel->getSingularLabelKey(), $moduleModel->getName()) . ' ' . vtranslate('LBL_COMMENTS', $moduleModel->getName());
			foreach ($commentFieldModelsList as $commentFieldName => $commentFieldModel) {
				$commentFieldModel->set('workflow_columnname', $commentFieldName)
					->set('workflow_columnlabel', vtranslate($commentFieldModel->get('label'), $moduleModel->getName()))
					->set('workflow_sourcemodule_field', true);

				$values[$labelName][$commentFieldName] = $commentFieldModel;
			}
		}
		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(array('reference', 'owner', 'multireference'));
		foreach ($fields as $parentFieldName => $field) {
			$type = $field->getFieldDataType();
			$referenceModules = $field->getReferenceList();
			if ($type == 'owner') {
				$referenceModules = array('Users');
			}
			foreach ($referenceModules as $refModule) {
				$moduleModel = Vtiger_Module_Model::getInstance($refModule);
				$blockModelList = $moduleModel->getBlocks();
				foreach ($blockModelList as $blockLabel => $blockModel) {
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							$DataType = true;
							if ($fieldDataType && ($fieldModel->getFieldDataType() != $fieldDataType)) {
								$DataType = false;
							}
							if ($fieldModel->isViewable() && $DataType) {
								$name = "$(reletedRecord : $parentFieldName|$fieldName|$refModule)$";
								$label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ': (' .
									vtranslate($refModule, $refModule) . ') ' . vtranslate($fieldModel->get('label'), $refModule);
								$fieldModel->set('workflow_columnname', $name)->set('workflow_columnlabel', $label);
								if (!empty($recordId)) {
									$fieldValueType = $recordModel->getFieldFilterValueType($name);
									$fieldInfo = $fieldModel->getFieldInfo();
									$fieldInfo['workflow_valuetype'] = $fieldValueType;
									$fieldModel->setFieldInfo($fieldInfo);
								}
								$fieldModel->set('selectOption', $parentFieldName . '=' . $refModule . '=' . $fieldName);
								$values[$field->get('label')][$name] = clone $fieldModel;
							}
						}
					}
				}
				$commentFieldModelsList = [];
				if ($moduleModel->isCommentEnabled() && $fieldDataType === false) {
					$labelName = vtranslate($moduleModel->getSingularLabelKey(), $moduleModel->getName()) . ' ' . vtranslate('LBL_COMMENTS', $moduleModel->getName());
					$commentFieldModelsList = Settings_Workflows_Field_Model::getCommentFieldsListForTasks($moduleModel);
					foreach ($commentFieldModelsList as $commentFieldName => $commentFieldModel) {
						$name = "$(reletedRecord : $parentFieldName|$commentFieldName|$refModule)$";
						$label = vtranslate($field->get('label'), $baseModuleModel->getName()) . ': (' .
							vtranslate($refModule, $refModule) . ') ' .
							vtranslate($commentFieldModel->get('label'), $refModule);
						$commentFieldModel->set('workflow_columnname', $name)->set('workflow_columnlabel', $label);
						$values[$labelName][$name] = $commentFieldModel;
					}
				}
			}
		}
		if ($fieldDataType === false) {
			$this->no_skip = true;
		}
		$this->structuredValues = $values;
		return $values;
	}
}
