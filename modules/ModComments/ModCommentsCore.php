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
require_once 'include/CRMEntity.php';

class ModCommentsCore extends CRMEntity
{
	public $table_name = 'vtiger_modcomments';
	public $table_index = 'modcommentsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_modcommentscf', 'modcommentsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_modcomments', 'vtiger_modcommentscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_modcomments' => 'modcommentsid',
		'vtiger_modcommentscf' => 'modcommentsid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Comment' => 'commentcontent',
		'Assigned To' => 'assigned_user_id',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Comment' => ['modcomments', 'commentcontent'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'Comment' => 'commentcontent',
	];
	// For Popup window record selection
	public $popup_fields = ['commentcontent'];
	// For Alphabetical search
	public $def_basicsearch_col = 'commentcontent';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'commentcontent';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'commentcontent'];

	public function __construct()
	{
		$this->column_fields = vtlib\Deprecated::getColumnFields('ModComments');
	}
}
