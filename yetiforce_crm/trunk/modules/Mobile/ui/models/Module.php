<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/ModuleRecord.php';

class Mobile_UI_ModuleModel {
	private $data;
	
	function initData($moduleData) {
		$this->data = $moduleData;
	}
	
	function id() {
		return $this->data['id'];
	}
	
	function name() {
		return $this->data['name'];
	}
	
	function label() {
		return $this->data['label'];
	}
	
	static function buildModelsFromResponse($modules) {
		$instances = array();
		foreach($modules as $moduleData) {
			$instance = new self();
			$instance->initData($moduleData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}