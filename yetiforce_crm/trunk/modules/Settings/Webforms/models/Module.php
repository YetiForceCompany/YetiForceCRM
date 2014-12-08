<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_Module_Model extends Settings_Vtiger_Module_Model {

	var $baseTable = 'vtiger_webforms';
	var $baseIndex = 'id';
	var $nameFields = array('name');
	var $listFields = array('name'=>'WebForm Name', 'targetmodule' => 'Module', 'publicid'=>'Public Id', 'returnurl' => 'Return Url', 'enabled' => 'Status');
	var $name = 'Webforms';

	public static function getSupportedModulesList() {
		$webformModules = array('Contacts','Accounts','Leads','Potentials','HelpDesk');
		$sourceModule = array();
		foreach ($webformModules as $key => $moduleName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$presenceValues = array(0,2);
			if(in_array($moduleModel->presence, $presenceValues)){
				$sourceModule[$moduleName] = vtranslate($moduleName, $moduleName);
			}
		}
		return $sourceModule;
	}

	/**
	 * Function to get Create view url
	 * @return <String> Url
	 */
	public function getCreateRecordUrl() {
		return "index.php?module=".$this->getName()."&parent=".$this->getParentName()."&view=Edit";
	}

	/**
	 * Function to get List view url
	 * @return <String> Url
	 */
	public function getListViewUrl() {
		return "index.php?module=".$this->getName()."&parent=".$this->getParentName()."&view=List";
	}

	/**
	 * Function to get list of Blocks
	 * @return <Array> list of Block models <Settings_Webforms_Block_Model>
	 */
	public function getBlocks() {
		if(empty($this->blocks)) {
			$this->blocks =  Settings_Webforms_Block_Model::getAllForModule($this);
		}
		return $this->blocks;
	}

	/**
	 * Function to get list of fields
	 * @return <Array> list of Field models <Settings_Webforms_Field_Model>
	 */
	public function getFields() {
		if (!$this->fields) {
			$fieldsList = array();
			$blocks = $this->getBlocks();
			foreach ($blocks as $blockModel) {
				$fieldsList = array_merge($fieldsList, $blockModel->getFields());
			}
			$this->fields = $fieldsList;
		}
		return $this->fields;
	}

	/**
	 * Function to get field using field name
	 * @param <String> $fieldName
	 * @return <Settings_Webforms_Field_Model>
	 */
	public function getField($fieldName) {
		$fields = $this->getFields();
		return $fields[$fieldName];
	}

	/**
	 * Function to delete record
	 * @param <Settings_Webforms_Record_Model> $recordModel
	 * @return <boolean> true
	 */
	public function deleteRecord($recordModel) {
		$recordId = $recordModel->getId();
		$db = PearDatabase::getInstance();

		$db->pquery("DELETE from vtiger_webforms_field WHERE webformid = ?", array($recordId));
		$db->pquery("DELETE from vtiger_webforms WHERE id = ?", array($recordId));
		return true;
	}
}
