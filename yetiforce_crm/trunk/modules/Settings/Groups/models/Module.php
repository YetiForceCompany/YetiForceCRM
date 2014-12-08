<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Module Model Class
 */
class Settings_Groups_Module_Model extends Settings_Vtiger_Module_Model {

	var $baseTable = 'vtiger_groups';
	var $baseIndex = 'groupid';
	var $listFields = array('groupname' => 'Name', 'description' => 'Description');
	var $name = 'Groups';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module=Groups&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return 'index.php?module=Groups&parent=Settings&view=Edit';
	}
}
