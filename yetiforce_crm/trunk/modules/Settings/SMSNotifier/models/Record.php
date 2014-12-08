<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_Record_Model extends Settings_Vtiger_Record_Model {

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> Id
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record instance
	 * @return <String> Name
	 */
	public function getName() {
		return '';
	}

	/**
	 * Function to get module of this record instance
	 * @return <Settings_Webforms_Module_Model> $moduleModel
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set module instance to this record instance
	 * @param <Settings_Webforms_Module_Model> $moduleModel
	 * @return <Settings_Webforms_Record_Model> this record
	 */
	public function setModule($moduleModel) {
		$this->module = $moduleModel;
		return $this;
	}

	/**
	 * Function to get Edit view url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		$moduleModel = $this->getModule();
		return 'index.php?module='.$moduleModel->getName().'&parent='.$moduleModel->getParentName().'&view=Edit&record='.$this->getId();
	}

	/**
	 * Function to get Delete url
	 * @return <String> Url
	 */
	public function getDeleteUrl() {
		$moduleModel = $this->getModule();
		return 'index.php?module='.$moduleModel->getName().'&parent='.$moduleModel->getParentName().'&action=Delete&record='.$this->getId();
	}

	/**
	 * Function to get record links
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getRecordLinks() {
		$links = array();
		$recordLinks = array(
				array(
						'linktype' => 'LISTVIEWRECORD',
						'linklabel' => 'LBL_EDIT',
						'linkurl' => "javascript:Settings_SMSNotifier_List_Js.triggerEdit(event, '".$this->getEditViewUrl()."');",
						'linkicon' => 'icon-pencil'
				),
				array(
						'linktype' => 'LISTVIEWRECORD',
						'linklabel' => 'LBL_DELETE',
						'linkurl' => "javascript:Settings_SMSNotifier_List_Js.triggerDelete(event, '".$this->getDeleteUrl()."');",
						'linkicon' => 'icon-trash'
				)
		);
		foreach($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to getDisplay value of every field
	 * @param <String> field name
	 * @return <String> field value
	 */
	public function getDisplayValue($key) {
		$value = $this->get($key);
		if ($key === 'isactive') {
			if ($value) {
				$value = 'Yes';
			} else {
				$value = 'No';
			}
		}
		return $value;
	}

	/**
	 * Function to get Editable fields for this instance
	 * @return <Array> field models list <Settings_SMSNotifier_Field_Model>
	 */
	public function getEditableFields() {
		$editableFieldsList = $this->getModule()->getEditableFields();
		return $editableFieldsList;
	}

	/**
	 * Function to save the record
	 */
	public function save() {
		$db = PearDatabase::getInstance();

		$params = array($this->get('providertype'), $this->get('isactive'), $this->get('username'), $this->get('password'), $this->get('parameters'));
		$id = $this->getId();

		if ($id) {
			$query = 'UPDATE vtiger_smsnotifier_servers SET providertype = ?, isactive = ?, username = ?, password = ?, parameters = ? WHERE id = ?';
			array_push($params, $id);
		} else {
			$query = 'INSERT INTO vtiger_smsnotifier_servers(providertype, isactive, username, password, parameters) VALUES(?, ?, ?, ?, ?)';
		}
		$db->pquery($query, $params);
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param <Integer> $recordId
	 * @param <String> $qualifiedModuleName
	 * @return <Settings_Webforms_Record_Model> RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_smsnotifier_servers WHERE id = ?', array($recordId));

		if ($db->num_rows($result)) {
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$rowData = $db->query_result_rowdata($result, 0);

			$recordModel = new self();
			$recordModel->setData($rowData)->setModule($moduleModel);

			$parameters = Zend_Json::decode(decode_html($recordModel->get('parameters')));
			foreach ($parameters as $fieldName => $fieldValue) {
				$recordModel->set($fieldName, $fieldValue);
			}

			return $recordModel;
		}
		return false;
	}

	/**
	 * Function to get clean record instance by using moduleName
	 * @param <String> $qualifiedModuleName
	 * @return <Settings_SMSNotifier_Record_Model>
	 */
	static public function getCleanInstance($qualifiedModuleName) {
		$recordModel = new self();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		return $recordModel->setModule($moduleModel);
	}
}