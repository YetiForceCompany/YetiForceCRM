<?php
/**
 * CInternalTickets CRMEntity Class
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class CInternalTickets extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_cinternaltickets';
	public $table_index = 'cinternalticketsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_cinternalticketscf', 'cinternalticketsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_cinternaltickets', 'u_yf_cinternalticketscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_cinternaltickets' => 'cinternalticketsid',
		'u_yf_cinternalticketscf' => 'cinternalticketsid'
	];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => ['cinternaltickets', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => ['cinternaltickets', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup window record selection
	public $popup_fields = ['subject'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'assigned_user_id'];
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
			
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
}
