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

class Settings_Profiles_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_profile';
	public $baseIndex = 'profileid';
	public $listFields = ['profilename' => 'Name', 'description' => 'Description'];

	const GLOBAL_ACTION_VIEW = 1;
	const GLOBAL_ACTION_EDIT = 2;
	const GLOBAL_ACTION_DEFAULT_VALUE = 1;
	const IS_PERMITTED_VALUE = 0;
	const NOT_PERMITTED_VALUE = 1;
	const FIELD_ACTIVE = 0;
	const FIELD_INACTIVE = 1;
	const FIELD_READWRITE = 0;
	const FIELD_READONLY = 1;

	public $name = 'Profiles';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Profiles&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=Profiles&parent=Settings&view=Edit';
	}

	/**
	 * Function to get non visible modules list.
	 *
	 * @return <Array> list of modules
	 */
	public static function getNonVisibleModulesList()
	{
		return ['ModTracker', 'Import', 'Home', 'OSSMailScanner'];
	}
}
