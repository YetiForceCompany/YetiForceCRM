<?php
/**
 * IGDN CRMEntity Class
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class IGDN extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_igdn';
	public $table_index = 'igdnid';
	protected $lockFields = ['igdn_status' => ['PLL_ACCEPTED']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('u_yf_igdncf', 'igdnid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'u_yf_igdn', 'u_yf_igdncf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_igdn' => 'igdnid',
		'u_yf_igdncf' => 'igdnid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'subject' => Array('igdn', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'assigned_user_id'];
// Make the field link to detail view
	public $list_link_field = 'subject';
// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'subject' => Array('igdn', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
// For Popup window record selection
	public $popup_fields = Array('subject');
// For Alphabetical search
	public $def_basicsearch_col = 'subject';
// Column value to use on detail view record text display
	public $def_detailview_recname = 'subject';
// Used when enabling/disabling the mandatory fields for the module.
// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('subject', 'assigned_user_id');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		if ($eventType == 'module.postinstall') {
			
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
}
