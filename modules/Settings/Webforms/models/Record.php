<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_Record_Model extends Settings_Vtiger_Record_Model {

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
		return $this->get('name');
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
	 * Function to get Detail view url
	 * @return <String> Url
	 */
	public function getDetailViewUrl() {
		$moduleModel = $this->getModule();
		return "index.php?module=".$moduleModel->getName()."&parent=".$moduleModel->getParentName()."&view=Detail&record=".$this->getId();
	}

	/**
	 * Function to get Edit view url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		$moduleModel = $this->getModule();
		return "index.php?module=".$moduleModel->getName()."&parent=".$moduleModel->getParentName()."&view=Edit&record=".$this->getId();
	}

	/**
	 * Function to get Delete url
	 * @return <String> Url
	 */
	public function getDeleteUrl() {
		$moduleModel = $this->getModule();
		return "index.php?module=".$moduleModel->getName()."&parent=".$moduleModel->getParentName()."&action=Delete&record=".$this->getId();
	}

	/**
	 * Function to get Show form url
	 * @return <String> Url
	 */
	public function getShowFormUrl() {
		$moduleModel = $this->getModule();
		return "index.php?module=".$moduleModel->getName()."&parent=".$moduleModel->getParentName()."&view=ShowForm&record=".$this->getId();
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
						'linklabel' => 'LBL_SHOW_FORM',
						'linkurl' => "javascript:Settings_Webforms_List_Js.showForm(event,'".$this->getShowFormUrl()."');",
						'linkicon' => 'icon-picture'
				),
				array(
						'linktype' => 'LISTVIEWRECORD',
						'linklabel' => 'LBL_EDIT',
						'linkurl' => $this->getEditViewUrl(),
						'linkicon' => 'icon-pencil'
				),
				array(
						'linktype' => 'LISTVIEWRECORD',
						'linklabel' => 'LBL_DELETE',
						'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'".$this->getDeleteUrl()."');",
						'linkicon' => 'icon-trash'
				)
		);
		foreach($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get Detail view links for this record instance
	 * @return <Array> list of link models <Vtiger_Link_Model>
	 */
	public function getDetailViewLinks() {
		$linkTypes = array('DETAILVIEWBASIC');
		$moduleModel = $this->getModule();
		$recordId = $this->getId();

		$detailViewLinks = array(
				array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_EDIT',
						'linkurl' => $this->getEditViewUrl(),
						'linkicon' => ''
				),
				array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => vtranslate('LBL_SHOW_FORM', $moduleModel->getParentName(). ':' .$moduleModel->getName()),
						'linkurl' => 'javascript:Settings_Webforms_Detail_Js.showForm("'.$this->getShowFormUrl().'")',
						'linkicon' => 'icon-picture'
				),
				array(
						'linktype' => 'DETAILVIEW',
						'linklabel' => 'LBL_DELETE',
						'linkurl' => 'javascript:Settings_Webforms_Detail_Js.deleteRecord("'.$this->getDeleteUrl().'")',
						'linkicon' => ''
				)
		);

		foreach ($detailViewLinks as $detailViewLink) {
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
		}
		return $linkModelList;
	}

	/**
	 * Function to get list of Selected Fields from target module for this record instance
	 * @return <Array> list of field models <Settings_Webforms_Field_Model>
	 */
	public function getSelectedFieldsList($mode='') {
		if (!$this->selectedFields) {
			$targetModule = $this->get('targetmodule');
			if ($targetModule) {
				$db = PearDatabase::getInstance();
				$targetModuleModel = Vtiger_Module_Model::getInstance($targetModule);
				$allFields = $targetModuleModel->getFields();

				$result = $db->pquery("SELECT * FROM vtiger_webforms_field WHERE webformid = ? ORDER BY sequence", array($this->getId()));
				$numOfRows = $db->num_rows($result);

				for($i=0; $i<$numOfRows; $i++) {
					$fieldData = $db->query_result_rowdata($result, $i);
					$fieldName = $fieldData['fieldname'];
					if (array_key_exists($fieldName, $allFields)) {
						$fieldModel = $allFields[$fieldName];

						//Check for hidden fields made as mandatory from layout editor,to unset hidden option
						$mandatoryStatus = $fieldModel->isMandatory(true);
						$hiddenStatus = $fieldData['hidden'];
						$fieldValue = trim($fieldData['defaultvalue']);
						$fieldType = $fieldModel->getFieldDataType();
						if(($mandatoryStatus == 1) and ($hiddenStatus == 1) and ($fieldValue == "") and ($fieldType != "boolean")){
							$fieldData['hidden'] = 0;
						}
						if(($fieldType == 'reference') && $mode != 'showForm'){
							$explodeResult = explode("x",$fieldValue);
							$fieldValue = $explodeResult[1];
							if(!isRecordExists($fieldValue)){
								$fieldValue = 0;
							}
						}
						
						if ($fieldModel->isViewable()) {
							foreach ($fieldData as $key => $value) {
								$fieldModel->set($key, $value);
							}
							$fieldModel->set('fieldvalue', $fieldValue);
							$selectedFields[$fieldName] = $fieldModel;
						}
					}
				}
			}
			$this->selectedFields = $selectedFields;
		}
		return $this->selectedFields;
	}

	/**
	 * Function to get List of fields
	 * @param <String> $targetModule
	 * @return <Array> list of Field models <Settings_Webforms_Field_Model>
	 */
	public function getAllFieldsList($targetModule = false) {
		if (!$targetModule) {
			$targetModule = $this->get('targetmodule');
		}
		$targetModuleModel = Vtiger_Module_Model::getInstance($targetModule);
		$restrictedFields = array('70','52','4','53');
		$blocks = $targetModuleModel->getBlocks();
		foreach ($blocks as $blockLabel => $blockModel) {
			$fieldModelsList = $blockModel->getFields();
			$webformFieldList = array();
			foreach ($fieldModelsList as $fieldName => $fieldModel) {
				if (in_array($fieldModel->get('uitype'), $restrictedFields) || !$fieldModel->isViewable()) {
					continue;
				}
				if($fieldModel->isEditable()){
					$webformFieldInstnace = Settings_Webforms_ModuleField_Model::getInstanceFromFieldObject($fieldModel);
					if ($fieldModel->getDefaultFieldValue()) {
						$webformFieldInstnace->set('fieldvalue', $fieldModel->getDefaultFieldValue());
					}
					$webformFieldList[$webformFieldInstnace->getName()] = $webformFieldInstnace;
				}
			}
			$targetModuleAllFieldsList[$blockLabel] = $webformFieldList;
		}
		return $targetModuleAllFieldsList;
	}

	/**
	 * Function generate public id for this record instance for first time only
	 * @return <String> id
	 */
	public function generatePublicId() {
		return md5(microtime(true) + $this->getName());
	}

	/**
	 * Function to delete this record
	 */
	public function delete() {
		$this->getModule()->deleteRecord($this);
	}
    
    /**
     * Function to set db insert value value for checkbox
     * @param <string> $fieldName
     */
    public function setCheckBoxValue($fieldName) {
        if($this->get($fieldName) == "on"){
			$this->set($fieldName,1);
		} else {
			$this->set($fieldName,0);
		}
    }

    /**
	 * Function to save the record
	 */
	public function save() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $this->get('mode');
    
		$db = PearDatabase::getInstance();		
		$this->setCheckBoxValue('enabled');
        $this->setCheckBoxValue('captcha');
        $this->setCheckBoxValue('roundrobin');
        if(is_array($this->get('roundrobin_userid'))){
            $roundrobinUsersList = json_encode($this->get('roundrobin_userid'),JSON_FORCE_OBJECT);
        }
		//Saving data about webform
		if ($mode) {
			$updateQuery = "UPDATE vtiger_webforms SET description = ?, returnurl = ?, ownerid = ?, enabled = ?, captcha = ? , roundrobin = ?, roundrobin_userid = ?, roundrobin_logic = ? ,targetmodule = ? WHERE id = ?";
            $params = array($this->get('description'), $this->get('returnurl'), $this->get('ownerid'), $this->get('enabled'), $this->get('captcha'),  $this->get('roundrobin'), $roundrobinUsersList, 0, $this->get('targetmodule'),$this->getId()); 
            $db->pquery($updateQuery, $params);
		} else {
			$insertQuery = "INSERT INTO vtiger_webforms(name, targetmodule, publicid, enabled, description, ownerid, returnurl, captcha, roundrobin, roundrobin_userid, roundrobin_logic) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->getName(), $this->get('targetmodule'), $this->generatePublicId(), $this->get('enabled'),  $this->get('description'), $this->get('ownerid'), $this->get('returnurl'), $this->get('captcha'), $this->get('roundrobin'),$roundrobinUsersList,0);

			$db->pquery($insertQuery, $params);
			$this->set('id', $db->getLastInsertID());
		}

		//Deleting existing data
		$db->pquery("DELETE FROM vtiger_webforms_field WHERE webformid = ?", array($this->getId()));

		//Saving data of source module fields info for this webform
		$selectedFieldsData = $this->get('selectedFieldsData');
		$sourceModuleModel = Vtiger_Module_Model::getInstance($this->get('targetmodule'));

		$fieldInsertQuery = "INSERT INTO vtiger_webforms_field(webformid, fieldname, neutralizedfield, defaultvalue, required, sequence, hidden) VALUES(?, ?, ?, ?, ?, ?, ?)";
		foreach ($selectedFieldsData as $fieldName => $fieldDetails) {
			$params = array($this->getId());
			$neutralizedField = $fieldName;
			$fieldDefaultValue = $fieldDetails['defaultvalue'];

			$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $sourceModuleModel);
			$dataType = $fieldModel->getFieldDataType();

			//Updating custom field label
			if (self::isCustomField($fieldName)) {
				$neutralizedField = 'label:'.str_replace(' ', '_', decode_html($fieldModel->get('label')));
			}

			//Handling multi picklist
			if(is_array($fieldDefaultValue)){
				$fieldDefaultValue = implode(" |##| ", $fieldDefaultValue);
			}

			//Handling Data format
			if ($dataType === 'date') {
				$fieldDefaultValue = Vtiger_Date_UIType::getDBInsertedValue($fieldDefaultValue);
			}
	
			if ($dataType === 'time') {
				$fieldDefaultValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldDefaultValue);
			}

			//Handling CheckBox value
			if ($dataType === 'boolean') {
				if ($fieldDefaultValue) {
					$fieldDefaultValue = 'on';
				} else {
					$fieldDefaultValue = '';
				}
			}
			if ($dataType === 'reference') {
				$referenceModule = $fieldDetails['referenceModule'];
				$referenceObject = VtigerWebserviceObject::fromName($db,$referenceModule);
				$referenceEntityId = $referenceObject->getEntityId();
				$fieldDefaultValue = $referenceEntityId."x".$fieldDefaultValue;
			}
			
			if ($dataType === 'currency') {
				$decimalSeperator = $currentUser->get('currency_decimal_separator');
				$groupSeperator = $currentUser->get('currency_grouping_separator');
				$fieldDefaultValue = str_replace($decimalSeperator, '.', $fieldDefaultValue);
				$fieldDefaultValue = str_replace($groupSeperator, '', $fieldDefaultValue);
			}

			array_push($params, $fieldName, $neutralizedField, $fieldDefaultValue, $fieldDetails['required'], $fieldDetails['sequence'], $fieldDetails['hidden']);
			$db->pquery($fieldInsertQuery, $params);
	}
	}

	/**
	 * Function check whether duplicate record exist or not with this name
	 * @return <boolean> true/false
	 */
	public function checkDuplicate() {
		$db = PearDatabase::getInstance();

		$query = "SELECT 1 FROM vtiger_webforms WHERE name = ?";
		$params = array($this->getName());

		$record = $this->getId();
		if ($record) {
			$query .= " AND id != ?";
			array_push($params, $record);
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param <Integer> $recordId
	 * @param <String> $qualifiedModuleName
	 * @return <Settings_Webforms_Record_Model> RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_webforms WHERE id = ?", array($recordId));
		if ($db->num_rows($result)) {
			$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$rowData = $db->query_result_rowdata($result, 0);
			$recordModel = new $recordModelClass();
			$recordModel->setData($rowData)->setModule($moduleModel);
			return $recordModel;
		}
		return false;
	}

	/**
	 * Function to get clean record instance by using moduleName
	 * @param <String> $moduleName
	 * @return <Settings_Vtiger_Module_Model>
	 */
	static public function getCleanInstance($moduleName) {
		$recordModel = new self();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);
		return $recordModel->setModule($moduleModel);
	}

	/**
	 * Function to check whether field is custom or not
	 * @param <String> $fieldName
	 * @return <boolean> true/false
	 */
	static function isCustomField($fieldName) {
		if (substr($fieldName, 0, 3) === "cf_") {
			return true;
		}
		return false;
	}

	public function getDisplayValue($key) {
		$fields = $this->getModule()->getFields();
		$fieldModel = $fields[$key];
		return $fieldModel->getDisplayValue($this->get($key));
	}
    
    /**
     * Function to check whether the captcha is enabled or not
     * @return <boolean> true/false
     */
    public function isCaptchaEnabled() { 
        if ($this->get('captcha') == '1') {
            return true;
        } else {
            return false;
        }
    }
}