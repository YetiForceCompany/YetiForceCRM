<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_API_Request {
	private $valuemap;
	private $rawvaluemap;
	private $defaultmap = array();
	
	function __construct($values = array(), $rawvalues = array()) {
		$this->valuemap = $values;
		$this->rawvaluemap = $rawvalues;
	}

	function get($key, $defvalue = '', $purify=true) {
		if(isset($this->valuemap[$key])) {
			return $purify ? vtlib_purify($this->valuemap[$key]) : $this->valuemap[$key];
		}
		if($defvalue === '' && isset($this->defaultmap[$key])) {
			$defvalue = $this->defaultmap[$key];
		}
		return $defvalue;
	}
	
	function has($key) {
		return isset($this->valuemap[$key]);
	}
	
	function getRaw($key, $defvalue = '') {
		if (isset($this->rawvaluemap[$key])) {
			return $this->rawvaluemap[$key];
		}
		return $this->get($key, $defvalue);
	}
	
	function set($key, $newvalue) {
		$this->valuemap[$key]= $newvalue;
	}
	
	function setDefault($key, $defvalue) {
		$this->defaultmap[$key] = $defvalue;
	}
	
	function getOperation() {
		return $this->get('_operation');
	}
	
	function getSession() {
		return $this->get('_session');
	}
}