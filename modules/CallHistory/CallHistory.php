<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
include_once 'modules/Vtiger/CRMEntity.php';

class CallHistory extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_callhistory';
	var $table_index = 'callhistoryid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_callhistorycf', 'callhistoryid');
	var $related_tables = Array('vtiger_callhistorycf' => Array('callhistoryid', 'vtiger_callhistory', 'callhistoryid'));

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_callhistory', 'vtiger_callhistorycf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_callhistory' => 'callhistoryid',
		'vtiger_callhistorycf' => 'callhistoryid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_TO_NUMBER' => Array('callhistory', 'to_number'),
		'LBL_FROM_NUMBER' => Array('callhistory', 'from_number'),
		'LBL_DURATION' => Array('callhistory', 'duration'),
		'LBL_TYPE' => Array('callhistory', 'callhistorytype'),
		'LBL_START_TIME' => Array('callhistory', 'start_time'),
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_TO_NUMBER' => 'to_number',
		'LBL_FROM_NUMBER' => 'from_number',
		'LBL_DURATION' => 'duration',
		'LBL_TYPE' => 'callhistorytype',
		'LBL_START_TIME' => 'start_time',
	);
	// Make the field link to detail view
	var $list_link_field = 'to_number';
	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_TO_NUMBER' => Array('callhistory', 'to_number'),
		'LBL_FROM_NUMBER' => Array('callhistory', 'from_number'),
		'LBL_DURATION' => Array('callhistory', 'duration'),
		'LBL_TYPE' => Array('callhistory', 'callhistorytype'),
		'LBL_START_TIME' => Array('callhistory', 'start_time'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_TO_NUMBER' => 'to_number',
		'LBL_FROM_NUMBER' => 'from_number',
		'LBL_DURATION' => 'duration',
		'LBL_TYPE' => 'callhistorytype',
		'LBL_START_TIME' => 'start_time',
	);
	// For Popup window record selection
	var $popup_fields = Array('to_number');
	// For Alphabetical search
	var $def_basicsearch_col = 'to_number';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'to_number';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('to_number', 'assigned_user_id');
	var $default_order_by = '';
	var $default_sort_order = 'DESC';

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
