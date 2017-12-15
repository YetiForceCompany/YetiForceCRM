<?php
/**
 * MultiCompany CRMEntity Class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class MultiCompany extends Vtiger_CRMEntity
{

	public $table_name = 'u_yf_multicompany';
	public $table_index = 'multicompanyid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_multicompanycf', 'multicompanyid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_multicompany', 'u_yf_multicompanycf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_multicompany' => 'multicompanyid',
		'u_yf_multicompanycf' => 'multicompanyid'
	];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'FL_COMPANY_NAME' => ['multicompany', 'company_name'],
		'FL_STATUS' => ['multicompany', 'mulcomp_status'],
		'FL_EMAIL_1' => ['multicompany', 'email1'],
		'FL_PHONE' => ['multicompany', 'phone'],
		'FL_VATID' => ['multicompany', 'vat'],
		'AddressLevel5' => ['multicompany', 'addresslevel5a'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'FL_COMPANY_NAME' => 'company_name',
		'FL_STATUS' => 'mulcomp_status',
		'FL_EMAIL_1' => 'email1',
		'FL_PHONE' => 'phone',
		'FL_VATID' => 'vat',
		'AddressLevel5' => 'addresslevel5a',
		'Assigned To' => 'assigned_user_id'
	];
	// Make the field link to detail view
	public $list_link_field = 'company_name';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'FL_COMPANY_NAME' => ['multicompany', 'company_name'],
		'FL_STATUS' => ['multicompany', 'mulcomp_status'],
		'FL_EMAIL_1' => ['multicompany', 'email1'],
		'FL_PHONE' => ['multicompany', 'phone'],
		'FL_VATID' => ['multicompany', 'vat'],
		'AddressLevel5' => ['multicompany', 'addresslevel5a'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'FL_COMPANY_NAME' => 'company_name',
		'FL_STATUS' => 'mulcomp_status',
		'FL_EMAIL_1' => 'email1',
		'FL_PHONE' => 'phone',
		'FL_VATID' => 'vat',
		'AddressLevel5' => 'addresslevel5a',
		'Assigned To' => 'assigned_user_id'
	];
	// For Popup window record selection
	public $popup_fields = ['company_name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'company_name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'company_name';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['company_name', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			
		} else if ($eventType === 'module.disabled') {
			
		} else if ($eventType === 'module.preuninstall') {
			
		} else if ($eventType === 'module.preupdate') {
			
		} else if ($eventType === 'module.postupdate') {
			
		}
	}
}
