<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

include_once 'modules/Vtiger/CRMEntity.php';

class _ModuleName_ extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_<modulename>';
	var $table_index = '<modulename>id';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = ['vtiger_<modulename>cf', '<modulename>id'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = ['vtiger_crmentity', 'vtiger_<modulename>', 'vtiger_<modulename>cf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_<modulename>' => '<modulename>id',
		'vtiger_<modulename>cf' => '<modulename>id'
	];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'<entityfieldlabel>' => ['<modulename>', '<entitycolumn>'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	var $list_fields_name = [
		/* Format: Field Label => fieldname */
		'<entityfieldlabel>' => '<entityfieldname>',
		'Assigned To' => 'assigned_user_id',
	];
	// Make the field link to detail view
	var $list_link_field = '<entityfieldname>';
	// For Popup listview and UI type support
	var $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'<entityfieldlabel>' => ['<modulename>', '<entitycolumn>'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	var $search_fields_name = [
		/* Format: Field Label => fieldname */
		'<entityfieldlabel>' => '<entityfieldname>',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup window record selection
	var $popup_fields = ['<entityfieldname>'];
	// For Alphabetical search
	var $def_basicsearch_col = '<entityfieldname>';
	// Column value to use on detail view record text display
	var $def_detailview_recname = '<entityfieldname>';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = ['<entityfieldname>', 'assigned_user_id'];
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {

		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
