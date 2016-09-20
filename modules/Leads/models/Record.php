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
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $moduleName = false, $limit = false)
	{
		if (!$limit) {
			$limit = AppConfig::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
		}
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		$adb = \PearDatabase::getInstance();

		$params = ['%' . $currentUser->getId() . '%', "%$label%"];
		$queryFrom = 'SELECT u_yf_crmentity_search_label.`crmid`,u_yf_crmentity_search_label.`setype`,u_yf_crmentity_search_label.`searchlabel` FROM `u_yf_crmentity_search_label` INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = u_yf_crmentity_search_label.crmid';
		$queryWhere = ' WHERE u_yf_crmentity_search_label.`userid` LIKE ? && u_yf_crmentity_search_label.`searchlabel` LIKE ? && vtiger_leaddetails.converted = 0';
		$orderWhere = '';
		if ($moduleName !== false) {
			$multiMode = is_array($moduleName);
			if ($multiMode) {
				$queryWhere .= sprintf(' && `setype` IN (%s)', $adb->generateQuestionMarks($moduleName));
				$params = array_merge($params, $moduleName);
			} else {
				$queryWhere .= ' && `setype` = ?';
				$params[] = $moduleName;
			}
		} elseif (\AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') == 2) {
			$queryFrom .= ' LEFT JOIN vtiger_entityname ON vtiger_entityname.modulename = u_yf_crmentity_search_label.setype';
			$queryWhere .= ' && vtiger_entityname.`turn_off` = 1 ';
			$orderWhere = ' vtiger_entityname.sequence';
		}
		$query = $queryFrom . $queryWhere;
		if (!empty($orderWhere)) {
			$query .= sprintf(' ORDER BY %s', $orderWhere);
		}
		if ($limit) {
			$query .= ' LIMIT ';
			$query .= $limit;
		}
		$rows = [];
		$result = $adb->pquery($query, $params);
		while ($row = $adb->getRow($result)) {
			$rows[] = $row;
		}
		$ids = $matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \includes\Record::getLabel($ids);

		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = $labels[$row['crmid']];
			$row['smownerid'] = $recordMeta['smownerid'];
			$row['createdtime'] = $recordMeta['createdtime'];
			$row['permitted'] = \includes\Privileges::isPermitted($row['setype'], 'DetailView', $row['crmid']);
			$moduleName = $row['setype'];
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
			$recordInstance = new $modelClassName();
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
		}
		return $matchingRecords;
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
				if ($fieldModel->getPermissions('readwrite')) {
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
	 * @param <String> $fieldName
	 * @return <String>
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
	 * @return <String>
	 */
	public function getCreateEventUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl() . '&link=' . $this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	public function getCreateTaskUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl() . '&link=' . $this->getId();
	}

	/**
	 * Function to check whether the lead is converted or not
	 * @return True if the Lead is Converted false otherwise.
	 */
	public function isLeadConverted()
	{
		$db = PearDatabase::getInstance();
		$id = $this->getId();
		$sql = "select converted from vtiger_leaddetails where converted = 1 and leadid=?";
		$result = $db->pquery($sql, array($id));
		$rowCount = $db->num_rows($result);
		if ($rowCount > 0) {
			return true;
		}
		return false;
	}
}
