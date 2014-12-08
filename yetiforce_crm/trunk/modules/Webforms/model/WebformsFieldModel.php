<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ******************************************************************************* */
require_once 'modules/Webforms/model/WebformsModel.php';
require_once 'modules/Webforms/Webforms.php';

class Webforms_Field_Model {

	protected $data;

	function __construct($data=array()) {
		$this->data = $data;
	}

	function setId($id) {
		$this->data["id"] = $id;
	}

	function setWebformId($webformid) {
		$this->data["webformid"] = $webformid;
	}

	function setFieldName($fieldname) {
		$this->data["fieldname"] = $fieldname;
	}

	function setNeutralizedField($fieldname, $fieldlabel=false) {
		$fieldlabel = str_replace(" ", "_", $fieldlabel);
		if (Webforms_Model::isCustomField($fieldname)) {
			$this->data["neutralizedfield"] = 'label:' . $fieldlabel;
		} else {
			$this->data["neutralizedfield"] = $fieldname;
		}
	}

	function setEnabled($enabled) {
		$this->data["enabled"] = $enabled;
	}

	function setDefaultValue($defaultvalue) {
		if (is_array($defaultvalue)) {
			$defaultvalue = implode(" |##| ", $defaultvalue);
		}
		$this->data["defaultvalue"] = $defaultvalue;
	}

	function setRequired($required) {
		$this->data["required"] = $required;
	}

	function getId() {
		return $this->data["id"];
	}

	function getWebformId() {
		return $this->data["webformid"];
	}

	function getFieldName() {
		return $this->data["fieldname"];
	}

	function getNeutralizedField() {
		$neutralizedfield = str_replace(" ", "_", $this->data['neutralizedfield']);
		return $neutralizedfield;
	}

	function getEnabled() {
		return $this->data["enabled"];
	}

	function getDefaultValue() {
		$data=$this->data["defaultvalue"];
		return $data;
	}

	function getRequired() {
		return $this->data["required"];
	}

	static function retrieveNeutralizedField($webformid, $fieldname) {
		global $adb;
		$sql = "SELECT neutralizedfield FROM vtiger_webforms_field WHERE webformid=? and fieldname=?";
		$result = $adb->pquery($sql, array($webformid, $fieldname));
		$model = false;
		if ($adb->num_rows($result)) {
			$neutralizedfield = $adb->query_result($result, 0, "neutralizedfield");
		}
		return $neutralizedfield;
	}

}

?>