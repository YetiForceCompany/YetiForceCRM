<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Leads_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get fields of this model
	 * @return <Array> list of field models <Settings_Leads_Field_Model>
	 */
	public function getFields()
	{
		if (!$this->fields) {
			$fieldModelsList = array();
			$fieldIds = $this->getMappingSupportedFieldIdsList();

			foreach ($fieldIds as $fieldId) {
				$fieldModel = Settings_Leads_Field_Model::getInstance($fieldId, $this);
				$fieldModelsList[$fieldModel->getFieldDataType()][$fieldId] = $fieldModel;
			}
			$this->fields = $fieldModelsList;
		}
		return $this->fields;
	}

	/**
	 * Function to get mapping supported field ids list
	 * @return <Array> list of field ids
	 */
	public function getMappingSupportedFieldIdsList()
	{
		if (!$this->supportedFieldIdsList) {
			$selectedTabidsList[] = getTabid($this->getName());
			$presense = array(0, 2);
			$restrictedFieldNames = array('campaignrelstatus');
			$restrictedUitypes = $this->getRestrictedUitypes();
			$selectedGeneratedTypes = array(1, 2);

			$db = PearDatabase::getInstance();
			$query = 'SELECT fieldid FROM vtiger_field
						WHERE presence IN (' . generateQuestionMarks($presense) . ')
						AND tabid IN (' . generateQuestionMarks($selectedTabidsList) . ')
						AND uitype NOT IN (' . generateQuestionMarks($restrictedUitypes) . ')
						AND fieldname NOT IN (' . generateQuestionMarks($restrictedFieldNames) . ')
						AND generatedtype IN (' . generateQuestionMarks($selectedGeneratedTypes) . ')';

			$params = array_merge($presense, $selectedTabidsList, $restrictedUitypes, $restrictedFieldNames, $selectedGeneratedTypes);

			$result = $db->pquery($query, $params);
			$numOfRows = $db->num_rows($result);

			$fieldIdsList = array();
			for ($i = 0; $i < $numOfRows; $i++) {
				$fieldIdsList[] = $db->query_result($result, $i, 'fieldid');
			}
			$this->supportedFieldIdsList = $fieldIdsList;
		}
		return $this->supportedFieldIdsList;
	}

	/**
	 * Function to get the Restricted Ui Types
	 * @return <array> Restricted ui types
	 */
	public function getRestrictedUitypes()
	{
		return array(4, 51, 52, 53, 57, 58, 69, 70);
	}

	/**
	 * Function to get instance of module
	 * @param <String> $moduleName
	 * @return <Settings_Leads_Module_Model>
	 */
	public static function getInstance($moduleName)
	{
		$moduleModel = parent::getInstance($moduleName);
		$objectProperties = get_object_vars($moduleModel);

		$moduleModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}
}
