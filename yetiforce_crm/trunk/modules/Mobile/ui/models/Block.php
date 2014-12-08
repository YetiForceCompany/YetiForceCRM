<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/Field.php';

class Mobile_UI_BlockModel {
	private $_label;
	private $_fields = array();
	
	function initData($blockData) {
		$this->_label = $blockData['label'];
		if (isset($blockData['fields'])) {
			$this->_fields = Mobile_UI_FieldModel::buildModelsFromResponse($blockData['fields']);
		}
	}
	
	function label() {
		return $this->_label;
	}
	
	function fields() {
		return $this->_fields;
	}
	
	static function buildModelsFromResponse($blocks) {
		$instances = array();
		foreach($blocks as $blockData) {
			$instance = new self();
			$instance->initData($blockData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}