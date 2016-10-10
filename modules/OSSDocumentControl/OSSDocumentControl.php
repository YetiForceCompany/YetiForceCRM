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

class OSSDocumentControl extends Vtiger_CRMEntity
{

	var $table_name = 'vtiger_ossdocumentcontrol';
	var $table_index = 'ossdocumentcontrolid';

	/**
	 * Mandatory table for supporting custom fields.
	 */

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_ossdocumentcontrol');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossdocumentcontrol' => 'ossdocumentcontrolid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Name' => Array('ossdocumentcontrol', 'name'),
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
		'Name' => Array('ossdocumentcontrol', 'name'),
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
		$db = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			require_once 'modules/OSSDocumentControl/helpers/Tool.php';

			Oss_Tool::addUitype15Field('Documents', 'LBL_NOTE_INFORMATION', array('None', 'Checked'), 'ossdc_status');

			$this->addLink($moduleName);
			$db->query("UPDATE vtiger_tab SET customized=0 WHERE name='$moduleName'");
		} else if ($eventType == 'module.enabled') {
			
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}

	private function addLink($moduleName)
	{
		$db = PearDatabase::getInstance();

		$blockid = $db->query_result(
			$db->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label='LBL_OTHER_SETTINGS'", array()), 0, 'blockid');
		$sequence = (int) $db->query_result(
				$db->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", array($blockid)), 0, 'sequence') + 1;
		$fieldid = $db->getUniqueId('vtiger_settings_field');
		$db->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
				VALUES (?,?,?,?,?,?,?)", array($fieldid, $blockid, $sequence, 'Document Control', '', 'LBL_DOCUMENT_CONTROL_DESCRIPTION', 'index.php?module=OSSDocumentControl&parent=Settings&view=Index'));
	}
}
