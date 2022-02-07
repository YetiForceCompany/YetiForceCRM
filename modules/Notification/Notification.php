<?php
/**
 * Notification CRMEntity Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Notification extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_notification';
	public $table_index = 'notificationid';
	protected $lockFields = ['notification_status' => ['PLL_READ']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = [];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_notification'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_notification' => 'notificationid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_TITLE' => 'title',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'FL_TITLE' => ['notification', 'title'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['title'];
	// For Alphabetical search
	public $def_basicsearch_col = 'title';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'title';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['title', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
}
