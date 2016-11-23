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

class HolidaysEntitlement extends Vtiger_CRMEntity
{

	public $table_name = 'vtiger_holidaysentitlement';
	public $table_index = 'holidaysentitlementid';
	public $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_holidaysentitlementcf', 'holidaysentitlementid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_holidaysentitlement', 'vtiger_holidaysentitlementcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_holidaysentitlement' => 'holidaysentitlementid',
		'vtiger_holidaysentitlementcf' => 'holidaysentitlementid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('holidaysentitlement', 'holidaysentitlement_no'),
		'LBL_EMPLOYEE' => Array('holidaysentitlement', 'ossemployeesid'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'holidaysentitlement_no',
		'LBL_EMPLOYEE' => 'ossemployeesid',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['holidaysentitlement_no', 'ossemployeesid', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('holidaysentitlement', 'holidaysentitlement_no'),
		'LBL_EMPLOYEE' => Array('holidaysentitlement', 'ossemployeesid'),
		'Assigned To' => Array('crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'holidaysentitlement_no',
		'LBL_EMPLOYEE' => 'ossemployeesid',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	public $popup_fields = Array('ossemployeesid');
	// For Alphabetical search
	public $def_basicsearch_col = 'ossemployeesid';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'ossemployeesid';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('ossemployeesid', 'assigned_user_id', 'holidaysentitlement_year', 'days');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('HolidaysEntitlement');
			\App\Fields\RecordNumber::setNumber($moduleName, 'HE', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array('HolidaysEntitlement'));
			$moduleInstance = vtlib\Module::getInstance('HolidaysEntitlement');
			$targetModule = vtlib\Module::getInstance('OSSEmployees');
			$targetModule->setRelatedList($moduleInstance, 'HolidaysEntitlement', array('ADD'), 'getDependentsList');
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
}
