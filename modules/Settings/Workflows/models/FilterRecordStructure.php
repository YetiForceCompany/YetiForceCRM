<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Workflows_FilterRecordStructure_Model extends Settings_Workflows_RecordStructure_Model
{
	/**
	 * Function to get the values in stuctured format.
	 *
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$recordModel = $this->getWorkFlowModel();
		$recordId = $recordModel->getId();
		$values = [];
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewable()) {
						if ($moduleModel->getName() === 'Calendar' && $fieldModel->getDisplayType() == 3) {
							continue;
						}
						if (!empty($recordId)) {
							//Set the fieldModel with the valuetype for the client side.
							$fieldValueType = $recordModel->getFieldFilterValueType($fieldName);
							$fieldInfo = $fieldModel->getFieldInfo();
							$fieldInfo['workflow_valuetype'] = $fieldValueType;
							$fieldModel->setFieldInfo($fieldInfo);
						}
						// This will be used during editing task like email, sms etc
						$fieldModel->set('workflow_columnname', $fieldName);
						$values[$blockLabel][$fieldName] = clone $fieldModel;
					}
				}
			}
		}
		if ($moduleModel->isCommentEnabled()) {
			$commentFieldModel = Settings_Workflows_Field_Model::getCommentFieldForFilterConditions($moduleModel);
			$commentFieldModelsList = [$commentFieldModel->getName() => $commentFieldModel];
			$labelName = \App\Language::translate($moduleModel->getSingularLabelKey(), $moduleModel->getName()) . ' ' . \App\Language::translate('LBL_COMMENTS', $moduleModel->getName());
			foreach ($commentFieldModelsList as $commentFieldName => $commentFieldModel) {
				$commentFieldModel->set('workflow_columnname', $commentFieldName);
				$values[$labelName][$commentFieldName] = $commentFieldModel;
			}
		}
		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(['reference', 'owner', 'multireference']);
		foreach ($fields as $parentFieldName => $field) {
			$type = $field->getFieldDataType();
			$referenceModules = $field->getReferenceList();
			if ($type == 'owner') {
				$referenceModules = ['Users'];
			}
			foreach ($referenceModules as $refModule) {
				$moduleModel = Vtiger_Module_Model::getInstance($refModule);
				$blockModelList = $moduleModel->getBlocks();
				foreach ($blockModelList as $blockLabel => $blockModel) {
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							if ($fieldModel->isViewable()) {
								$name = "($parentFieldName : ($refModule) $fieldName)";
								$fieldModel->set('workflow_columnname', $name);
								if (!empty($recordId)) {
									$fieldValueType = $recordModel->getFieldFilterValueType($name);
									$fieldInfo = $fieldModel->getFieldInfo();
									$fieldInfo['workflow_valuetype'] = $fieldValueType;
									$fieldModel->setFieldInfo($fieldInfo);
								}
								$values[$field->get('label')][$name] = clone $fieldModel;
							}
						}
					}
				}

				$commentFieldModelsList = [];
			}
		}
		$this->structuredValues = $values;

		return $values;
	}
}
