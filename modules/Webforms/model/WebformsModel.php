<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************* */
require_once 'modules/Webforms/model/WebformsFieldModel.php';

class Webforms_Model {

	public $data;
	protected $fields = array();

	function __construct($values = array()) {
		$this->setData($values);
	}

	protected function addField(Webforms_Field_Model $field) {
		$this->fields[] = $field;
	}

	function setData($data) {
		$this->data = $data;
		if (isset($data["fields"])) {
			$this->setFields(vtlib_purify($data["fields"]), vtlib_purify($data["required"]), vtlib_purify($data["value"]));
		}
		if (isset($data['id'])) {
			if (($data['enabled'] == 'on') || ($data['enabled'] == 1)) {
				$this->setEnabled(1);
			} else {
				$this->setEnabled(0);
			}
		} else {
			$this->setEnabled(1);
		}
	}

	function hasId() {
		return!empty($this->data['id']);
	}

	function setId($id) {
		$this->data["id"] = $id;
	}

	function setName($name) {
		$this->data["name"] = $name;
	}

	function setTargetModule($module) {
		$this->data["targetmodule"] = $module;
	}

	protected function setPublicId($publicid) {
		$this->data["publicid"] = $publicid;
	}

	function setEnabled($enabled) {
		$this->data["enabled"] = $enabled;
	}

	function setDescription($description) {
		$this->data["description"] = $description;
	}

	function setReturnUrl($returnurl) {
		$this->data["returnurl"] = $returnurl;
	}

	function setOwnerId($ownerid) {
		$this->data["ownerid"];
	}

	function setFields(array $fieldNames, $required, $value) {
		require_once 'include/fields/DateTimeField.php';
		foreach ($fieldNames as $ind => $fieldname) {
			$fieldInfo = Webforms::getFieldInfo($this->getTargetModule(), $fieldname);
			$fieldModel = new Webforms_Field_Model();
			$fieldModel->setFieldName($fieldname);
			$fieldModel->setNeutralizedField($fieldname, $fieldInfo['label']);
			$field = Webforms::getFieldInfo('Leads', $fieldname);
			if (($field['type']['name'] == 'date')) {
				$defaultvalue = DateTimeField::convertToDBFormat($value[$fieldname]);
			}else if (($field['type']['name'] == 'boolean')){
				if(in_array($fieldname,$required)){
					if(empty($value[$fieldname])){
						$defaultvalue='off';
					}else{
						$defaultvalue='on';
					}
				}else{
					$defaultvalue=$value[$fieldname];
				}
			}
			else {
				$defaultvalue = vtlib_purify($value[$fieldname]);
			}
			$fieldModel->setDefaultValue($defaultvalue);
			if ((!empty($required) && in_array($fieldname, $required))) {
				$fieldModel->setRequired(1);
			} else {
				$fieldModel->setRequired(0);
			}
			$this->addField($fieldModel);
		}
	}

	function getId() {
		return vtlib_purify($this->data["id"]);
	}

	function getName() {
		return html_entity_decode(vtlib_purify($this->data["name"]));
	}

	function getTargetModule() {
		return vtlib_purify($this->data["targetmodule"]);
	}

	function getPublicId() {
		return vtlib_purify($this->data["publicid"]);
	}

	function getEnabled() {
		return vtlib_purify($this->data["enabled"]);
	}
    
	function getDescription() {
		return vtlib_purify($this->data["description"]);
	}

	function getReturnUrl() {
		return vtlib_purify($this->data["returnurl"]);
	}

	function getOwnerId() {
		return vtlib_purify($this->data["ownerid"]);
	}
    
    function getRoundrobin() {
        return vtlib_purify($this->data["roundrobin"]);
    }
    
    function getRoundrobinOwnerId() {
        global $adb;
        $roundrobin_userid = vtlib_purify($this->data["roundrobin_userid"]);
        $roundrobin_logic = vtlib_purify($this->data["roundrobin_logic"]);
        $useridList = json_decode($roundrobin_userid,true);
        if($roundrobin_logic >= count($useridList))
            $roundrobin_logic=0;
        $roundrobinOwnerId = $useridList[$roundrobin_logic];
        $nextRoundrobinLogic = ($roundrobin_logic+1)%count($useridList);
        $adb->pquery("UPDATE vtiger_webforms SET roundrobin_logic = ? WHERE id = ?", array($nextRoundrobinLogic,$this->getId()));
        return vtlib_purify($roundrobinOwnerId);
    }

	function getFields() {
		return $this->fields;
	}

	function generatePublicId($name) {
		global $adb, $log;
		$uid = md5(microtime(true) + $name);
		return $uid;
	}

	function retrieveFields() {
		global $adb;
		$fieldsResult = $adb->pquery("SELECT * FROM vtiger_webforms_field WHERE webformid=?", array($this->getId()));
		while ($fieldRow = $adb->fetch_array($fieldsResult)) {
			$this->addField(new Webforms_Field_Model($fieldRow));
		}
		return $this;
	}

	function save() {
		global $adb, $log;

		$isNew = !$this->hasId();

		// Create?
		if ($isNew) {
			if (self::existWebformWithName($this->getName())) {
				throw new Exception('LBL_DUPLICATE_NAME');
			}
			$this->setPublicId($this->generatePublicId($this->getName()));
			$insertSQL = "INSERT INTO vtiger_webforms(name, targetmodule, publicid, enabled, description,ownerid,returnurl) VALUES(?,?,?,?,?,?,?)";
			$result = $adb->pquery($insertSQL, array($this->getName(), $this->getTargetModule(), $this->getPublicid(), $this->getEnabled(), $this->getDescription(), $this->getOwnerId(), $this->getReturnUrl()));
			$this->setId($adb->getLastInsertID());
		} else {
			// Udpate
			$updateSQL = "UPDATE vtiger_webforms SET description=? ,returnurl=?,ownerid=?,enabled=? WHERE id=?";
			$result = $adb->pquery($updateSQL, array($this->getDescription(), $this->getReturnUrl(), $this->getOwnerId(), $this->getEnabled(), $this->getId()));
		}

		// Delete fields and re-add enabled once
		$adb->pquery("DELETE FROM vtiger_webforms_field WHERE webformid=?", array($this->getId()));
		$fieldInsertSQL = "INSERT INTO vtiger_webforms_field(webformid, fieldname, neutralizedfield, defaultvalue,required) VALUES(?,?,?,?,?)";
		foreach ($this->fields as $field) {
			$params = array();
			$params[] = $this->getId();
			$params[] = $field->getFieldName();
			$params[] = $field->getNeutralizedField();
			$params[] = $field->getDefaultValue();
			$params[] = $field->getRequired();
			$adb->pquery($fieldInsertSQL, $params);
		}
		return true;
	}

	function delete() {
		global $adb, $log;

		$adb->pquery("DELETE from vtiger_webforms_field where webformid=?", array($this->getId()));
		$adb->pquery("DELETE from vtiger_webforms where id=?", array($this->getId()));
		return true;
	}

	static function retrieveWithPublicId($publicid) {
		global $adb, $log;

		$model = false;
		// Retrieve model and populate information
		$result = $adb->pquery("SELECT * FROM vtiger_webforms WHERE publicid=? AND enabled=?", array($publicid, 1));
		if ($adb->num_rows($result)) {
			$model = new Webforms_Model($adb->fetch_array($result));
			$model->retrieveFields();
		}
		return $model;
	}

	static function retrieveWithId($data) {
		global $adb, $log;

		$id = $data;
		$model = false;
		// Retrieve model and populate information
		$result = $adb->pquery("SELECT * FROM vtiger_webforms WHERE id=?", array($id));
		if ($adb->num_rows($result)) {
			$model = new Webforms_Model($adb->fetch_array($result));
			$model->retrieveFields();
		}
		return $model;
	}

	static function listAll() {
		global $adb, $log;
		$webforms = array();

		$sql = "SELECT * FROM vtiger_webforms";
		$result = $adb->pquery($sql, array());

		for ($index = 0, $len = $adb->num_rows($result); $index < $len; $index++) {
			$webform = new Webforms_Model($adb->fetch_array($result));
			$webforms[] = $webform;
		}


		return $webforms;
	}

	static function isWebformField($webformid, $fieldname) {
		global $adb, $log;

		$checkSQL = "SELECT 1 from vtiger_webforms_field where webformid=? AND fieldname=?";
		$result = $adb->pquery($checkSQL, array($webformid, $fieldname));
		return (($adb->num_rows($result)) ? true : false);
	}

	static function isCustomField($fieldname) {
		if (substr($fieldname, 0, 3) === "cf_") {
			return true;
		}
		return false;
	}

	static function isRequired($webformid, $fieldname) {
		global $adb;
		$sql = "SELECT required FROM vtiger_webforms_field where webformid=? AND fieldname=?";
		$result = $adb->pquery($sql, array($webformid, $fieldname));
		$required = false;
		if ($adb->num_rows($result)) {
			$required = $adb->query_result($result, 0, "required");
		}
		return $required;
	}

	static function retrieveDefaultValue($webformid, $fieldname) {
		require_once 'include/fields/DateTimeField.php';
		global $adb,$current_user,$current_;
		$dateformat=$current_user->date_format;
		$sql = "SELECT defaultvalue FROM vtiger_webforms_field WHERE webformid=? and fieldname=?";
		$result = $adb->pquery($sql, array($webformid, $fieldname));
		$defaultvalue = false;
		if ($adb->num_rows($result)) {
			$defaultvalue = $adb->query_result($result, 0, "defaultvalue");
			$field = Webforms::getFieldInfo('Leads', $fieldname);
			if (($field['type']['name'] == 'date') && !empty($defaultvalue)) {
				$defaultvalue = DateTimeField::convertToUserFormat($defaultvalue);
			}
			$defaultvalue = explode(' |##| ', $defaultvalue);
		}
		return $defaultvalue;
	}

	static function existWebformWithName($name) {
		global $adb;
		$checkSQL = "SELECT 1 FROM vtiger_webforms WHERE name=?";
		$check = $adb->pquery($checkSQL, array($name));
		if ($adb->num_rows($check) > 0) {
			return true;
		}
		return false;
	}

	static function isActive($field, $mod) {
		global $adb;
		$tabid = getTabid($mod);
		$query = 'SELECT 1 FROM vtiger_field WHERE fieldname = ?  AND tabid = ? AND presence IN (0,2)';
		$res = $adb->pquery($query, array($field, $tabid));
		$rows = $adb->num_rows($res);
		if ($rows > 0) {
			return true;
		}else
			return false;
	}
}

?>
