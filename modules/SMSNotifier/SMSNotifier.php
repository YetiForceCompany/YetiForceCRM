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

class SMSNotifier extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_smsnotifier';
	public $table_index = 'smsnotifierid';

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_smsnotifiercf', 'smsnotifierid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_smsnotifier', 'vtiger_smsnotifiercf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_smsnotifier' => 'smsnotifierid',
		'vtiger_smsnotifiercf' => 'smsnotifierid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Message' => 'message',
		'Assigned To' => 'assigned_user_id',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Message' => ['smsnotifier', 'message'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['message'];
	// For Alphabetical search
	public $def_basicsearch_col = 'message';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'message';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'message', 'assigned_user_id', 'smsnotifier_status'];

	public function __construct()
	{
		$this->column_fields = vtlib\Deprecated::getColumnFields(static::class);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $modulename Module name
	 * @param string $eventType  Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($modulename, $eventType)
	{
	}
}
