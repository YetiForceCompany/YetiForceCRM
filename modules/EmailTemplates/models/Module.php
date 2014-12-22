<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class EmailTemplates_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get Alphabet Search Field 
	 */
	public function getAlphabetSearchField() {
		return 'templatename';
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getCreateRecordUrl() {
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName();
	}

	/**
	 * Function to save a given record model of the current module
	 * @param EmailtTemplates_Record_Model $recordModel
	 * @return <integer> template id
	 */
	public function saveRecord($recordModel) {
		$db = PearDatabase::getInstance();
		$templateid = $recordModel->getId();
		if(empty($templateid)){
			$templateid = $db->getUniqueID('vtiger_emailtemplates');
			$sql = "INSERT INTO vtiger_emailtemplates(templatename, subject, description, body, deleted, templateid) VALUES (?,?,?,?,?,?)";
		}else{
			$sql = "UPDATE vtiger_emailtemplates SET templatename=?, subject=?, description=?, body=?, deleted=? WHERE templateid = ?";
		}
		$params = array(decode_html($recordModel->get('templatename')), decode_html($recordModel->get('subject')),
				decode_html($recordModel->get('description')), $recordModel->get('body'), 0, $templateid);
		$db->pquery($sql, $params);
		return $recordModel->setId($templateid);
	}

	/**
	 * Function to delete the email template
	 * @param type $recordIds
	 */
	public function deleteRecord($recordModel) {
		$recordId = $recordModel->getId();
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_emailtemplates WHERE templateid = ? ', array($recordId));
	}
	
	/**
	 * Function to delete all the email templates
	 * @param type $recordIds
	 */
	public function deleteAllRecords() {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_emailtemplates', array());
	}

	/**
	 * Function to get Email template fields from modules
	 * @return <array> template fields
	 */
	public function getAllModuleEmailTemplateFields() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$allModuleList = $this->getAllModuleList();
		$allRelFields = array();
		foreach ($allModuleList as $index => $module) {
			if($module == 'Users'){
				$fieldList = $this->getRelatedModuleFieldList($module, $currentUserModel);
			}else{
				$fieldList = $this->getRelatedFields($module, $currentUserModel);
			}
			foreach ($fieldList as $key => $field) {
				$option = array(vtranslate($field['module'], $field['module']) . ':' . vtranslate($field['fieldlabel'], $field['module']), "$" . strtolower($field['module']) . "-" . $field['columnname'] . "$");
				$allFields[] = $option;
				if (!empty($field['referencelist'])) {
					foreach ($field['referencelist'] as $key => $relField) {
						$relOption = array(vtranslate($field['fieldlabel'], $field['module']) . ':' . '(' . vtranslate($relField['module'], $relField['module']) . ')' . vtranslate($relField['fieldlabel'],$relField['module']), "$" . strtolower($field['module']) . "-" . $field['columnname'] . ":" . $relField['columnname'] . "$");
						$allRelFields[] = $relOption;
					}
				}
			}
			if(is_array($allFields) && is_array($allRelFields)){
				$allFields = array_merge($allFields, $allRelFields);
				$allRelFields="";
			}
			$allOptions[vtranslate($module, $module)] = $allFields;
			$allFields = "";
		}
		
		$option = array('Current Date', '$custom-currentdate$');
		$allFields[] = $option;
		$option = array('Current Time', '$custom-currenttime$');
		$allFields[] = $option;
		$allOptions['generalFields'] = $allFields;
		return $allOptions;
	}
	
	/**
	 * Function to get module fields
	 * @param type $module
	 * @param type $currentUserModel
	 * @return <arrau>
	 */
	function getRelatedFields($module, $currentUserModel) {
		$handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
		$meta = $handler->getMeta();
		$moduleFields = $meta->getModuleFields();

		$returnData = array();
		foreach ($moduleFields as $key => $field) {
			$referencelist = array();
			$relatedField = $field->getReferenceList();
			if ($field->getFieldName() == 'assigned_user_id') {
				$relModule = 'Users';
				$referencelist = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
			}
			if (!empty($relatedField)) {
				foreach ($relatedField as $ind => $relModule) {
					$referencelist = $this->getRelatedModuleFieldList($relModule, $currentUserModel);
				}
			}
			$returnData[] = array('module' => $module, 'fieldname' => $field->getFieldName(), 'columnname' => $field->getColumnName(), 'fieldlabel' => $field->getFieldLabelKey(), 'referencelist' => $referencelist);
			
		}
		return $returnData;
	}
	
	/**
	 * Function to get related module fields
	 * @param type $relModule
	 * @param type $user
	 * @return null
	 */
	
	function getRelatedModuleFieldList($relModule, $user) {
		$handler = vtws_getModuleHandlerFromName($relModule, $user);
		$relMeta = $handler->getMeta();
		if (!$relMeta->isModuleEntity()) {
			return null;
		}
		$relModuleFields = $relMeta->getModuleFields();
		$relModuleFieldList = array();
		foreach ($relModuleFields as $relind => $relModuleField) {
			if($relModule == 'Users') {
				if($relModuleField->getFieldDataType() == 'string' || $relModuleField->getFieldDataType() == 'email' || $relModuleField->getFieldDataType() == 'phone') {
					$skipFields = array(98,115,116,31,32);
					if(!in_array($relModuleField->getUIType(), $skipFields) && $relModuleField->getFieldName() != 'asterisk_extension'){
						$relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
					}
				}
			} else {
				$relModuleFieldList[] = array('module' => $relModule, 'fieldname' => $relModuleField->getFieldName(), 'columnname' => $relModuleField->getColumnName(), 'fieldlabel' => $relModuleField->getFieldLabelKey());
			}
		}
		return $relModuleFieldList;
	}

	/**
	 * Function to get module list which has the email field.
	 * @return type
	 */
	public function getAllModuleList(){
		$db = PearDatabase::getInstance();
		
		$query = 'SELECT DISTINCT(name) AS modulename FROM vtiger_tab 
				  LEFT JOIN vtiger_field ON vtiger_field.tabid = vtiger_tab.tabid
				  WHERE vtiger_field.uitype = ?';
		$result = $db->pquery($query, array(13));
		$num_rows = $db->num_rows($result);
		$moduleList = array();
		for($i=0; $i<$num_rows; $i++){
			$moduleList[] = $db->query_result($result, $i, 'modulename');
		}
		return $moduleList;
	}
	
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getDefaultUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}
	
	public function getRecordIds($skipRecords){
		$db = PearDatabase::getInstance();
		
		$query = 'SELECT templateid FROM vtiger_emailtemplates WHERE templateid NOT IN ('.generateQuestionMarks($skipRecords).')';
		$result = $db->pquery($query, $skipRecords);
		$num_rows = $db->num_rows($result);
		$recordIds = array();
		for($i; $i<$num_rows; $i++){
			$recordIds[] = $db->query_result($result, $i, 'templateid');
		}
		return $recordIds;
	}
    
    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled() {
        return false;
    }
}