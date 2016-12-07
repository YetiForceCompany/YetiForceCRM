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

	public $table_name = 'vtiger_ossdocumentcontrol';
	public $table_index = 'ossdocumentcontrolid';

	/**
	 * Mandatory table for supporting custom fields.
	 */

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_ossdocumentcontrol');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossdocumentcontrol' => 'ossdocumentcontrolid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Name' => Array('ossdocumentcontrol', 'name'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Name' => 'name',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['title', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'name';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Name' => Array('ossdocumentcontrol', 'name'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Name' => 'name',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	public $popup_fields = Array('name');
	// For Alphabetical search
	public $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'name';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('name', 'assigned_user_id');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

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
		Settings_Vtiger_Module_Model::addSettingsField('LBL_OTHER_SETTINGS', [
			'name' => 'Document Control',
			'iconpath' => 'adminIcon-workflow',
			'description' => 'LBL_DOCUMENT_CONTROL_DESCRIPTION',
			'linkto' => 'index.php?module=OSSDocumentControl&parent=Settings&view=Index'
		]);
	}
}
