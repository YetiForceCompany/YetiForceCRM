<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Leads_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function returns the url for converting lead
	 */
	public function getConvertLeadUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=ConvertLead&record=' . $this->getId();
	}

	/**
	 * Function returns Account fields for Lead Convert
	 * @return Array
	 */
	public function getAccountFieldsForLeadConvert()
	{
		$accountsFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Accounts';

		if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
			//Fields that need to be shown
			$complusoryFields = array(); //Field List in the conversion lead
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if ($fieldModel->isMandatory() && $fieldName != 'assigned_user_id') {

					$keyIndex = array_search($fieldName, $complusoryFields);
					if ($keyIndex !== false) {
						unset($complusoryFields[$keyIndex]);
					}
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					if ($leadMappedField) {
						$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					}
					if ($fieldModel->get('fieldvalue') == '') {
						$fieldModel->set('fieldvalue', $fieldModel->getDefaultFieldValue());
					}
					$accountsFields[] = $fieldModel;
				}
			}
			foreach ($complusoryFields as $complusoryField) {
				$fieldModel = Vtiger_Field_Model::getInstance($complusoryField, $moduleModel);
				if ($fieldModel->getPermissions(false)) {
					$industryFieldModel = $moduleModel->getField($complusoryField);
					$industryLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
					if ($industryLeadMappedField) {
						$industryFieldModel->set('fieldvalue', $this->get($industryLeadMappedField));
					} else {
						$industryFieldModel->set('fieldvalue', $fieldModel->get('defaultvalue'));
					}
					$accountsFields[] = $industryFieldModel;
				}
			}
		}
		return $accountsFields;
	}

	/**
	 * Function returns field mapped to Leads field, used in Lead Convert for settings the field values
	 * @param string $fieldName
	 * @return string
	 */
	public function getConvertLeadMappedField($fieldName, $moduleName)
	{
		$mappingFields = $this->get('mappingFields');

		if (!$mappingFields) {
			$db = PearDatabase::getInstance();
			$mappingFields = array();

			$result = $db->pquery('SELECT * FROM vtiger_convertleadmapping', array());
			$numOfRows = $db->num_rows($result);

			$accountInstance = Vtiger_Module_Model::getInstance('Accounts');
			$accountFieldInstances = $accountInstance->getFieldsById();

			$leadInstance = Vtiger_Module_Model::getInstance('Leads');
			$leadFieldInstances = $leadInstance->getFieldsById();

			for ($i = 0; $i < $numOfRows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				if (empty($row['leadfid']))
					continue;

				$leadFieldInstance = $leadFieldInstances[$row['leadfid']];
				if (!$leadFieldInstance)
					continue;

				$leadFieldName = $leadFieldInstance->getName();
				$accountFieldInstance = $accountFieldInstances[$row['accountfid']];
				if ($row['accountfid'] && $accountFieldInstance) {
					$mappingFields['Accounts'][$accountFieldInstance->getName()] = $leadFieldName;
				}
			}
			$this->set('mappingFields', $mappingFields);
		}
		return $mappingFields[$moduleName][$fieldName];
	}

	/**
	 * Function returns the fields required for Lead Convert
	 * @return <Array of Vtiger_Field_Model>
	 */
	public function getConvertLeadFields()
	{
		$convertFields = array();
		$accountFields = $this->getAccountFieldsForLeadConvert();
		if (!empty($accountFields)) {
			$convertFields['Accounts'] = $accountFields;
		}
		return $convertFields;
	}

	/**
	 * Function returns the url for create event
	 * @return string
	 */
	public function getCreateEventUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl() . '&link=' . $this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return string
	 */
	public function getCreateTaskUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl() . '&link=' . $this->getId();
	}
}
