<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// Settings Module Model Class

class Settings_Groups_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_groups';
	public $baseIndex = 'groupid';
	public $listFields = ['groupname' => 'Name', 'description' => 'Description'];
	public $name = 'Groups';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=Edit';
	}
}
