<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class ProjectTask extends CRMEntity
{

	public $table_name = 'vtiger_projecttask';
	public $table_index = 'projecttaskid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_projecttaskcf', 'projecttaskid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_projecttask', 'vtiger_projecttaskcf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_projecttask' => 'projecttaskid',
		'vtiger_projecttaskcf' => 'projecttaskid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Project Task Name' => ['projecttask', 'projecttaskname'],
		'Start Date' => ['projecttask', 'startdate'],
		'End Date' => ['projecttask', 'enddate'],
		'Type' => ['projecttask', 'projecttasktype'],
		'Progress' => ['projecttask', 'projecttaskprogress'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_TOTAL_TIME_H' => ['projecttask', 'sum_time']
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'Project Task Name' => 'projecttaskname',
		'Start Date' => 'startdate',
		'End Date' => 'enddate',
		'Type' => 'projecttasktype',
		'Progress' => 'projecttaskprogress',
		'Assigned To' => 'assigned_user_id',
		'FL_TOTAL_TIME_H' => 'sum_time'
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['projecttaskname', 'startdate', 'enddate', 'projecttasktype', 'projecttaskprogress', 'assigned_user_id', 'sum_time'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'projecttaskname';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Project Task Name' => ['projecttask', 'projecttaskname'],
		'Start Date' => ['projecttask', 'startdate'],
		'Type' => ['projecttask', 'projecttasktype'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'Project Task Name' => 'projecttaskname',
		'Start Date' => 'startdate',
		'Type' => 'projecttasktype',
		'Assigned To' => 'assigned_user_id'
	];
	// For Popup window record selection
	public $popup_fields = ['projecttaskname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'projecttaskname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'projecttaskname';
	// Required Information for enabling Import feature
	public $required_fields = ['projecttaskname' => 1];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'projecttaskname', 'projectid', 'assigned_user_id'];

	/**
	 * Transform the value while exporting
	 */
	public function transformExportValue($key, $value)
	{
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string $moduleName Module name
	 * @param string $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(['ProjectTask']);
			}

			\App\Fields\RecordNumber::setNumber($moduleName, 'PT', 1);
		} else if ($eventType === 'module.postupdate') {

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(['ProjectTask']);
			}

			\App\Fields\RecordNumber::setNumber($moduleName, 'PT', 1);
		}
	}
}
