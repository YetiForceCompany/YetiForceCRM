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

class Reservations extends Vtiger_CRMEntity
{

	public $table_name = 'vtiger_reservations';
	public $table_index = 'reservationsid';
	public $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_reservationscf', 'reservationsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_reservations', 'vtiger_reservationscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_reservations' => 'reservationsid',
		'vtiger_reservationscf' => 'reservationsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'No.' => Array('reservations', 'reservations_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'No.' => 'reservations_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['reservations_no', 'createdtime', 'assigned_user_id'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'assigned_user_id';
	// For Popup listview and UI type support
	public $search_fields = Array(
		'No.' => Array('reservations', 'reservations_no'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Created Time' => Array('crmentity', 'createdtime'),
	);
	public $search_fields_name = Array(
		'No.' => 'reservations_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	);
	// For Popup window record selection
	public $popup_fields = Array('name');
	// For Alphabetical search
	public $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'name';
	// Required Information for enabling Import feature
	public $required_fields = Array('assigned_user_id' => 1);
	// Callback function list during Importing
	public $special_functions = Array('set_import_assigned_user');
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('createdtime', 'modifiedtime', 'assigned_user_id');

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
		$registerLink = false;
		$adb = PearDatabase::getInstance();

		if ($event_type == 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('Reservations');
			\App\Fields\RecordNumber::setNumber($moduleName, 'RES', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array('Reservations'));
			$moduleInstance = vtlib\Module::getInstance($modulename);
			$targetModule = vtlib\Module::getInstance('Accounts');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', array('ADD'), 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('HelpDesk');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', array('ADD'), 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Leads');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', array('ADD'), 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Project');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', array('ADD'), 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Vendors');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', array('ADD'), 'getDependentsList');
		} else if ($event_type == 'module.disabled') {
			
		} else if ($event_type == 'module.enabled') {
			
		} else if ($event_type == 'module.preuninstall') {
			
		} else if ($event_type == 'module.preupdate') {
			
		} else if ($event_type == 'module.postupdate') {
			
		}
	}

	public function retrieve_entity_info($record, $module)
	{
		parent::retrieve_entity_info($record, $module);
		$start = DateTimeField::convertToUserTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format('Y-m-d');
		$end = DateTimeField::convertToUserTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format('Y-m-d');
	}

	public function saveentity($module_name, $fileid = '')
	{
		$date_start = $this->column_fields['date_start'];
		$due_date = $this->column_fields['due_date'];
		$start = DateTimeField::convertToDBTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format(DateTimeField::getPHPDateFormat());
		$end = DateTimeField::convertToDBTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format(DateTimeField::getPHPDateFormat());
		parent::saveentity($module_name, $fileid = '');
		$this->column_fields['date_start'] = $date_start;
		$this->column_fields['due_date'] = $due_date;
	}
}
