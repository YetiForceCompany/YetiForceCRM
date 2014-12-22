<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_UI_FieldModel {
	private $data;
	
	function initData($fieldData) {
		$this->data = $fieldData;
	}
	
	function name() {
		return $this->data['name'];
	}
	
	function value() {
		$rawValue = $this->data['value'];
		if (is_array($rawValue)) return $rawValue['value'];
		return $rawValue;
	}
	
	function valueLabel() {
		$rawValue = $this->data['value'];
		if (is_array($rawValue)) return $rawValue['label'];
		return $rawValue;
	}
	
	function label() {
		return $this->data['label'];
	}
	
	function isReferenceType() {
		static $options = array('101', '116', '117', '26', '357',
			'50', '51', '52', '53', '57', '58', '59', '66',
			'73', '75', '76', '77', '78', '80', '81'
		);
		if (isset($this->data['uitype'])) {
			$uitype = $this->data['uitype'];
			if (in_array($uitype, $options)) {
				return true;
			}
		} else if(isset($this->data['type'])) {
			switch($this->data['type']['name']) {
				case 'reference':
				case 'owner':
					return true;
			}
		}
		return $this->isMultiReferenceType();
	}
	
	function isMultiReferenceType() {
		static $options = array('10', '68');
		
		$uitype = $this->data['uitype'];
		if (in_array($uitype, $options)) {
			return true;
		}
		return false;
	}
	
	static function buildModelsFromResponse($fields) {
		$instances = array();

		foreach($fields as $fieldData) {
			$instance = new self();
			$instance->initData($fieldData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}