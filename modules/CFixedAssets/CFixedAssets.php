<?php
/**
 * CFixedAssets CRMEntity Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class CFixedAssets extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_cfixedassets';
	public $table_index = 'cfixedassetsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_cfixedassetscf', 'cfixedassetsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_cfixedassets', 'u_yf_cfixedassetscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_cfixedassets' => 'cfixedassetsid',
		'u_yf_cfixedassetscf' => 'cfixedassetsid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => ['cfixedassets', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
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
}
