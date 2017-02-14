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

class OSSOutsourcedServices extends Vtiger_CRMEntity
{

	public $table_name = 'vtiger_ossoutsourcedservices';
	public $table_index = 'ossoutsourcedservicesid';
	public $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_ossoutsourcedservicescf', 'ossoutsourcedservicesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_ossoutsourcedservices', 'vtiger_ossoutsourcedservicescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossoutsourcedservices' => 'ossoutsourcedservicesid',
		'vtiger_ossoutsourcedservicescf' => 'ossoutsourcedservicesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Product Name' => Array('ossoutsourcedservices' => 'productname'),
		'Category' => Array('ossoutsourcedservices' => 'pscategory'),
		'Sub Category' => Array('ossoutsourcedservices' => 'pssubcategory'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Date Sold' => Array('ossoutsourcedservices' => 'datesold'),
		'LBL_osservicesstatus' => Array('ossoutsourcedservices' => 'osservicesstatus'),
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Product Name' => 'productname',
		'Category' => 'pscategory',
		'Assigned To' => 'assigned_user_id',
		'Date Sold' => 'datesold',
		'LBL_osservicesstatus' => 'osservicesstatus',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['productname', 'pscategory', 'assigned_user_id', 'datesold', 'osservicesstatus'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'productname';
	// For Popup listview and UI type support
	public $search_fields = Array(
		'Product Name' => Array('ossoutsourcedservices' => 'productname'),
		'Category' => Array('ossoutsourcedservices' => 'pscategory'),
		'Sub Category' => Array('ossoutsourcedservices' => 'pssubcategory'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Date Sold' => Array('ossoutsourcedservices' => 'datesold'),
		'LBL_osservicesstatus' => Array('ossoutsourcedservices' => 'osservicesstatus'),
	);
	public $search_fields_name = Array(
		'Product Name' => 'productname',
		'Category' => 'pscategory',
		'Sub Category' => 'pssubcategory',
		'Assigned To' => 'assigned_user_id',
		'Date Sold' => 'datesold',
		'LBL_osservicesstatus' => 'osservicesstatus',
	);
	// For Popup window record selection
	public $popup_fields = Array('productname');
	// For Alphabetical search
	public $def_basicsearch_col = 'productname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'productname';
	// Required Information for enabling Import feature
	public $required_fields = Array('productname' => 1);
	// Callback function list during Importing
	public $special_functions = Array('set_import_assigned_user');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname');

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
		if ($event_type == 'module.postinstall') {
			\App\Fields\RecordNumber::setNumber($modulename, 'UO', '1');
		} else if ($event_type == 'module.disabled') {
			
		} else if ($event_type == 'module.enabled') {
			
		} else if ($event_type == 'module.preuninstall') {
			
		} else if ($event_type == 'module.preupdate') {
			
		} else if ($event_type == 'module.postupdate') {
			
		}
	}
}
