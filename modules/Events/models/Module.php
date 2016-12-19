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
	 * @return string - url
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
		$moduleName = $this->getName();
		if (\App\Cache::has('EntityField', $moduleName)) {
			$nameFieldObject = \App\Cache::get('EntityField', $moduleName);
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$row = (new \App\Db\Query())->select(['fieldname', 'tablename', 'entityidfield'])
				->from('vtiger_entityname')
				->where(['tabid' => \App\Module::getModuleId('Calendar')])
				->one();
			$this->nameFields = [];
			if ($row) {
				$fieldNames = $row['fieldname'];
				$this->nameFields = explode(',', $fieldNames);
				$entiyObj = new stdClass();
				$entiyObj->basetable = $row['tablename'];
				$entiyObj->basetableid = $row['entityidfield'];
				$entiyObj->fieldname = $fieldNames;
				\App\Cache::save('EntityField', $moduleName, $entiyObj);
			}
		}
		return $this->nameFields;
	}
}
