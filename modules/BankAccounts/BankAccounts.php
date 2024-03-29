<?php
/**
 * Bank accounts crmentity file.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';
/**
 * BankAccounts class.
 */
class BankAccounts extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_bankaccounts';
	public $table_index = 'bankaccountsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_bankaccountscf', 'bankaccountsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_bankaccounts', 'u_yf_bankaccountscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_bankaccounts' => 'bankaccountsid',
		'u_yf_bankaccountscf' => 'bankaccountsid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_NAME' => 'name',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		'FL_NAME' => ['bankaccounts', 'name'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'name';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['name', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		} elseif ('module.disabled' === $eventType) {
		} elseif ('module.preuninstall' === $eventType) {
		} elseif ('module.preupdate' === $eventType) {
		} elseif ('module.postupdate' === $eventType) {
		}
	}
}
