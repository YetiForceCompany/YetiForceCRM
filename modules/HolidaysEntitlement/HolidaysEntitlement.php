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

	var $table_name = 'vtiger_holidaysentitlement';
	var $table_index = 'holidaysentitlementid';
	var $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_holidaysentitlementcf', 'holidaysentitlementid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_holidaysentitlement', 'vtiger_holidaysentitlementcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_holidaysentitlement' => 'holidaysentitlementid',
		'vtiger_holidaysentitlementcf' => 'holidaysentitlementid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('holidaysentitlement', 'holidaysentitlement_no'),
		'LBL_EMPLOYEE' => Array('holidaysentitlement', 'ossemployeesid'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'holidaysentitlement_no',
		'LBL_EMPLOYEE' => 'ossemployeesid',
		'Assigned To' => 'assigned_user_id',
	);
	// Make the field link to detail view
	var $list_link_field = 'subject';
	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('holidaysentitlement', 'holidaysentitlement_no'),
		'LBL_EMPLOYEE' => Array('holidaysentitlement', 'ossemployeesid'),
		'Assigned To' => Array('crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'holidaysentitlement_no',
		'LBL_EMPLOYEE' => 'ossemployeesid',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	var $popup_fields = Array('ossemployeesid');
	// For Alphabetical search
	var $def_basicsearch_col = 'ossemployeesid';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'ossemployeesid';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('ossemployeesid', 'assigned_user_id', 'holidaysentitlement_year', 'days');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

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
			\includes\fields\RecordNumber::setNumber($moduleName, 'HE', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array('HolidaysEntitlement'));
			$moduleInstance = vtlib\Module::getInstance('HolidaysEntitlement');
			$targetModule = vtlib\Module::getInstance('OSSEmployees');
			$targetModule->setRelatedList($moduleInstance, 'HolidaysEntitlement', array('ADD'), 'get_dependents_list');
		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
