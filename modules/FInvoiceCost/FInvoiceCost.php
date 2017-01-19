<?php
/**
 * FInvoiceCost CRMEntity Class
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class FInvoiceCost extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_finvoicecost';
	public $table_index = 'finvoicecostid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('u_yf_finvoicecostcf', 'finvoicecostid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'u_yf_finvoicecost', 'u_yf_finvoicecostcf', 'u_yf_finvoicecost_address');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_finvoicecost' => 'finvoicecostid',
		'u_yf_finvoicecostcf' => 'finvoicecostid',
		'u_yf_finvoicecost_address' => 'finvoicecostaddressid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('finvoicecost', 'subject'),
		'FL_SALE_DATE' => Array('finvoicecost', 'saledate'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
		'Assigned To' => 'assigned_user_id',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'saledate', 'assigned_user_id'];
// Make the field link to detail view
	public $list_link_field = 'subject';
// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('finvoicecost', 'subject'),
		'FL_SALE_DATE' => Array('finvoicecost', 'saledate'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
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
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
}
