<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_ModuleField_Model extends Vtiger_Field_Model {
	
	public function getFieldName() {
		$fieldName = parent::getFieldName();
		return 'selectedFieldsData['. $fieldName .'][defaultvalue]';
	}
	
	public function isMandatory($forceCheck = false) {
		if($forceCheck) {
			return parent::isMandatory();
		}
		return false;
	}
	
	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldInfo =  array(
			'mandatory' => $this->isMandatory(),
			'type' => $this->getFieldDataType(),
			'name' => $this->getFieldName(),
			'label' => vtranslate($this->get('label'), $this->getModuleName()),
			'defaultValue' => $this->getEditViewDisplayValue($this->getDefaultFieldValue()),
			'customField' => Settings_Webforms_Record_Model::isCustomField($this->get('name')),
			'specialValidator' => $this->getValidator()
		);

		$pickListValues = $this->getPicklistValues();
		if(!empty($pickListValues)) {
			$fieldInfo['picklistvalues'] = $pickListValues;
		}

		if($this->getFieldDataType() == 'date' || $this->getFieldDataType() == 'datetime'){
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$fieldInfo['date-format'] = $currentUser->get('date_format');
		}

		if($this->getFieldDataType() == 'currency') {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$fieldInfo['currency_symbol'] = $currentUser->get('currency_symbol');
			$fieldInfo['decimalSeperator'] = $currentUser->get('currency_decimal_separator');
			$fieldInfo['groupSeperator'] = $currentUser->get('currency_grouping_separator');
		}

		if($this->getFieldDataType() == 'owner') {
			$userList = $currentUser->getAccessibleUsers();
			$groupList = $currentUser->getAccessibleGroups();
			$pickListValues = array();
			$pickListValues[vtranslate('LBL_USERS', $this->getModuleName())] = $userList;
			$pickListValues[vtranslate('LBL_GROUPS', $this->getModuleName())] = $groupList;
			$fieldInfo['picklistvalues'] = $pickListValues;
		}
		
		if($this->getFieldDataType() == 'reference') {
			$referenceList = $this->getReferenceList();
			$fieldInfo['referencemodules']= $referenceList;
		}

		return $fieldInfo;
	}
	
	public function getPicklistValues() {
		$fieldDataType = $this->getFieldDataType();

		if ($fieldDataType != 'picklist') {
			return parent::getPicklistValues();
		}
		$pickListValues = array();
		$pickListValues[""] = vtranslate("LBL_SELECT_OPTION", 'Settings:Webforms');
		return ($pickListValues + parent::getPicklistValues());
	}
	
	/**
     * Function which will check if empty piclist option should be given
     */
    public function isEmptyPicklistOptionAllowed() {
        return false;
    }

	public static function getInstanceFromFieldObject(Vtiger_Field $fieldObj) {
		$objectProperties = get_object_vars($fieldObj);
		$fieldModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$fieldModel->$properName = $propertyValue;
		}
		return $fieldModel;
	}
}