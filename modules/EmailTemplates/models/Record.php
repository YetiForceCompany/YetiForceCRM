<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_Record_Model extends Vtiger_Record_Model {
	
	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId() {
		return $this->get('templateid');
	}
	
	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value) {
		return $this->set('templateid',$value);
	}
	
	/**
	 * Function to delete the email template
	 * @param type $recordIds
	 */
	public function delete(){
		$this->getModule()->deleteRecord($this);
	}
	
	/**
	 * Function to delete all the email templates
	 * @param type $recordIds
	 */
	public function deleteAllRecords(){
		$this->getModule()->deleteAllRecords();
	}
	
	/**
	 * Function to get template fields
	 * To get the fields from module, which has the email field
	 * @return <arrray> template fields
	 */
	public function getEmailTemplateFields(){
		return $this->getModule()->getAllModuleEmailTemplateFields();
	}
	
	/**
	 * Function to get the Email Template Record
	 * @param type $record
	 * @return <EmailTemplate_Record_Model>
	 */
	
	public function getTemplateData($record){
		return $this->getModule()->getTemplateData($record);
	}
	
	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
	}
	
	/**
	 * Function to get the instance of Custom View module, given custom view id
	 * @param <Integer> $cvId
	 * @return CustomView_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($templateId, $module=null) {
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_emailtemplates WHERE templateid = ?';
		$params = array($templateId);
		$result = $db->pquery($sql, $params);
		if($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result, 0);
			$recordModel = new self();
			return $recordModel->setData($row)->setModule('EmailTemplates');
		}
		return null;
	}
	
}
