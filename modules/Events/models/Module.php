<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Calendar Module Model Class
 */
class Events_Module_Model extends Calendar_Module_Model
{

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=Calendar&view=' . $this->getListViewName();
	}

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields()
	{
		$nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
		$moduleName = $this->getName();
		if ($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM vtiger_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array(\includes\Modules::getModuleId('Calendar')));
			$this->nameFields = array();
			if ($result) {
				$rowCount = $adb->num_rows($result);
				if ($rowCount > 0) {
					$fieldNames = $adb->query_result($result, 0, 'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}

			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid = $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname = $fieldNames;
			Vtiger_Cache::set('EntityField', $this->getName(), $entiyObj);
		}
		return $this->nameFields;
	}
}
