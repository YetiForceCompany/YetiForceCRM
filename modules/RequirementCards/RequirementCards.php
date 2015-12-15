<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class RequirementCards extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_requirementcards';
	var $table_index= 'requirementcardsid';
	var $column_fields = Array();
	
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_requirementcardscf', 'requirementcardsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_requirementcards', 'vtiger_requirementcardscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_requirementcards' => 'requirementcardsid',
		'vtiger_requirementcardscf'=>'requirementcardsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('requirementcards', 'requirementcards_no'),
		'LBL_SUBJECT' => Array('requirementcards', 'subject'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'requirementcards_no',
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'subject';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_NO' => Array('requirementcards', 'requirementcards_no'),
		'LBL_SUBJECT' => Array('requirementcards', 'subject'),
		'Assigned To' => Array('crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'LBL_NO' => 'requirementcards_no',
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('subject');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject','assigned_user_id');

	var $default_order_by = 'subject';
	var $default_sort_order='ASC';
	
	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		$adb = PearDatabase::getInstance();
 		if($eventType == 'module.postinstall') {
			include_once('vtlib/Vtiger/Module.php'); 
 			$moduleInstance = CRMEntity::getInstance('RequirementCards');
			$moduleInstance->setModuleSeqNumber("configure",'RequirementCards','RC','1');
 			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array('RequirementCards'));
			
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if(class_exists('ModComments')) ModComments::addWidgetTo(array('RequirementCards'));
			}
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModTracker');
			if($modcommentsModuleInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once('vtlib/Vtiger/Module.php');
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = Vtiger_Functions::getModuleId('RequirementCards');
				$moduleModTrackerInstance = new ModTracker();
				if(!$moduleModTrackerInstance->isModulePresent($tabid)){
					$res=$adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)",array($tabid,1));
					$moduleModTrackerInstance->updateCache($tabid,1);
				} else{
					$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 1 WHERE tabid = ?", array($tabid));
					$moduleModTrackerInstance->updateCache($tabid,1);
				}
				if(!$moduleModTrackerInstance->isModTrackerLinkPresent($tabid)){
					$moduleInstance=Vtiger_Module::getInstance($tabid);
					$moduleInstance->addLink('DETAILVIEWBASIC', 'View History', "javascript:ModTrackerCommon.showhistory('\$RECORD\$')",'','',
											array('path'=>'modules/ModTracker/ModTracker.php','class'=>'ModTracker','method'=>'isViewPermitted'));
				}
			}

		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}
}
