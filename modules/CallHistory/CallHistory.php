<?php
/**
 * CallHistory model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class CallHistory extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_callhistory';
	public $table_index = 'callhistoryid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_callhistorycf', 'callhistoryid'];
	public $related_tables = ['vtiger_callhistorycf' => ['callhistoryid', 'vtiger_callhistory', 'callhistoryid']];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_callhistory', 'vtiger_callhistorycf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_callhistory' => 'callhistoryid',
		'vtiger_callhistorycf' => 'callhistoryid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_TO_NUMBER' => ['callhistory', 'to_number'],
		'LBL_FROM_NUMBER' => ['callhistory', 'from_number'],
		'LBL_DURATION' => ['callhistory', 'duration'],
		'LBL_TYPE' => ['callhistory', 'callhistorytype'],
		'LBL_START_TIME' => ['callhistory', 'start_time'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_TO_NUMBER' => 'to_number',
		'LBL_FROM_NUMBER' => 'from_number',
		'LBL_DURATION' => 'duration',
		'LBL_TYPE' => 'callhistorytype',
		'LBL_START_TIME' => 'start_time',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['to_number', 'from_number', 'duration', 'callhistorytype', 'start_time'];
	// Make the field link to detail view
	public $list_link_field = 'to_number';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_TO_NUMBER' => ['callhistory', 'to_number'],
		'LBL_FROM_NUMBER' => ['callhistory', 'from_number'],
		'LBL_DURATION' => ['callhistory', 'duration'],
		'LBL_TYPE' => ['callhistory', 'callhistorytype'],
		'LBL_START_TIME' => ['callhistory', 'start_time'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'LBL_TO_NUMBER' => 'to_number',
		'LBL_FROM_NUMBER' => 'from_number',
		'LBL_DURATION' => 'duration',
		'LBL_TYPE' => 'callhistorytype',
		'LBL_START_TIME' => 'start_time',
	];
	// For Popup window record selection
	public $popup_fields = ['to_number'];
	// For Alphabetical search
	public $def_basicsearch_col = 'to_number';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'to_number';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['to_number', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
}
