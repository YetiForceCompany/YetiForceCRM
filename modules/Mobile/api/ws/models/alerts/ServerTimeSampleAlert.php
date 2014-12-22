<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../Alert.php';

/** Server time sample alert */
class Mobile_WS_AlertModel_ServerTimeSampleAlert extends Mobile_WS_AlertModel {
	function __construct() {
		// Mandatory call to parent constructor
		parent::__construct();
		
		$this->name = 'Server Time Alert';
		$this->description='Alert to get server time information';
		$this->refreshRate= 1; // 1 second
		$this->recordsLinked = FALSE; 
		// There is no module records linked with message.
		// If set to true $this->moduleName needs to be set.
	}
	
	function message() {
		return date('Y-m-d H:i:s');
	}
	
	/** Override base class methods */
	function query() {
		return false;
	}

	function queryParameters() {
		return false;
	}
}