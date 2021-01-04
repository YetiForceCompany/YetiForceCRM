<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * QuickCreate Record Structure Model class.
 */
class Vtiger_QuickCreateRecordStructure_Model extends Vtiger_RecordStructure_Model
{
	/**
	 * Function to get the values in structured format.
	 *
	 * @return Vtiger_Field_Model[] Field instances.
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		Vtiger_Field_Model::$tabIndexDefaultSeq = 1000;
		$fieldsDependency = \App\FieldsDependency::getByRecordModel('QuickCreate', $this->record);
		$fieldModelList = $this->getModule()->getQuickCreateFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if ($fieldsDependency['hide']['backend'] && \in_array($fieldName, $fieldsDependency['hide']['backend'])) {
				continue;
			}
			$recordModelFieldValue = $this->record->get($fieldName);
			if (!empty($recordModelFieldValue)) {
				$fieldModel->set('fieldvalue', $recordModelFieldValue);
			} elseif ('activitystatus' === $fieldName) {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaulteventstatus = $currentUserModel->get('defaulteventstatus');
				$fieldValue = $defaulteventstatus;
				$fieldModel->set('fieldvalue', $fieldValue);
			} elseif ('activitytype' === $fieldName) {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaultactivitytype = $currentUserModel->get('defaultactivitytype');
				$fieldValue = $defaultactivitytype;
				$fieldModel->set('fieldvalue', $fieldValue);
			} else {
				$defaultValue = $fieldModel->getDefaultFieldValue();
				if ($defaultValue) {
					$fieldModel->set('fieldvalue', $defaultValue);
				}
			}
			if ($fieldModel->get('tabindex') > Vtiger_Field_Model::$tabIndexLastSeq) {
				Vtiger_Field_Model::$tabIndexLastSeq = $fieldModel->get('tabindex');
			}
			if ($fieldsDependency['hide']['frontend'] && \in_array($fieldName, $fieldsDependency['hide']['frontend'])) {
				$fieldModel->set('hideField', true);
			}
			if ($fieldsDependency['mandatory'] && \in_array($fieldName, $fieldsDependency['mandatory'])) {
				$fieldModel->set('isMandatory', true);
			}
			$this->structuredValues[$fieldName] = $fieldModel;
		}
		++Vtiger_Field_Model::$tabIndexLastSeq;
		return $this->structuredValues;
	}
}
