<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ProjectMilestone extends CRMEntity
{
	public $table_name = 'vtiger_projectmilestone';
	public $table_index = 'projectmilestoneid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_projectmilestonecf', 'projectmilestoneid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_projectmilestone', 'vtiger_projectmilestonecf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_projectmilestone' => 'projectmilestoneid',
		'vtiger_projectmilestonecf' => 'projectmilestoneid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Project Milestone Name' => 'projectmilestonename',
		'Milestone Date' => 'projectmilestonedate',
		'FL_STATUS' => 'projectmilestone_status',
		'Type' => 'projectmilestonetype',
		'Related to' => 'projectid',
		'FL_PARENT_PROJECT_MILESTONE' => 'parentid',
		//'Assigned To' => 'assigned_user_id'
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Project Milestone Name' => ['projectmilestone', 'projectmilestonename'],
		'Milestone Date' => ['projectmilestone', 'projectmilestonedate'],
		'FL_STATUS' => ['projectmilestone', 'projectmilestone_status'],
		'Type' => ['projectmilestone', 'projectmilestonetype'],
		'Related to' => ['projectmilestone', 'projectid'],
		'FL_PARENT_PROJECT_MILESTONE' => ['projectmilestone', 'parentid'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['projectmilestonename'];
	// For Alphabetical search
	public $def_basicsearch_col = 'projectmilestonename';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'projectmilestonename';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'projectmilestonename', 'projectid', 'assigned_user_id'];

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		}
	}
}
