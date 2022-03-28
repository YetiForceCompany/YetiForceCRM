<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

include_once 'modules/Vtiger/CRMEntity.php';

class FBookkeeping extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_fbookkeeping';
	public $table_index = 'fbookkeepingid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_fbookkeepingcf', 'fbookkeepingid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_fbookkeeping', 'u_yf_fbookkeepingcf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_fbookkeeping' => 'fbookkeepingid',
		'u_yf_fbookkeepingcf' => 'fbookkeepingid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'subject' => ['fbookkeeping', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
}
