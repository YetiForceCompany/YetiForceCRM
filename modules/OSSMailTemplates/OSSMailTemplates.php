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

class OSSMailTemplates extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_ossmailtemplates';
	var $table_index = 'ossmailtemplatesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ossmailtemplatescf', 'ossmailtemplatesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ossmailtemplates', 'vtiger_ossmailtemplatescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossmailtemplates' => 'ossmailtemplatesid',
		'vtiger_ossmailtemplatescf' => 'ossmailtemplatesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Name' => Array('ossmailtemplates', 'name'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Name' => 'name',
		'Assigned To' => 'assigned_user_id',
	);
	// Make the field link to detail view
	var $list_link_field = 'name';
	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Name' => Array('ossmailtemplates', 'name'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Name' => 'name',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	var $popup_fields = Array('name');
	// For Alphabetical search
	var $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'name';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('name', 'assigned_user_id');
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

			$adb->query("UPDATE vtiger_tab SET customized = 0 WHERE name = '$moduleName'", true);

			$Module = vtlib\Module::getInstance($moduleName);
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_InstallModule', $moduleName . ' ' . $Module->version, $user_id), false);
		} else if ($eventType == 'module.disabled') {

			$adb->pquery("UPDATE vtiger_settings_field SET active = '1' WHERE name = '$moduleName' && linkto = 'index.php?module=$moduleName&view=Index&parent=Settings'");
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_DisabledModule', $moduleName, $user_id), false);
		} else if ($eventType == 'module.enabled') {

			$adb->query("UPDATE vtiger_settings_field SET active = '0' WHERE name = '$moduleName' && linkto = 'index.php?module=$moduleName&view=Index&parent=Settings'");
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_EnabledModule', $moduleName, $user_id), false);
		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

			$Module = vtlib\Module::getInstance($moduleName);
			if (version_compare($Module->version, '1.02', '>')) {
				$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
				$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_UpdateModule', $moduleName . ' ' . $Module->version, $user_id), false);
			}
		}
	}
}
