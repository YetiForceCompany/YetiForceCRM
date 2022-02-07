<?php
/**
 * Main Occurrences module class.
 *
 * @package   App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

/**
 * Class Occurrences.
 */
class Occurrences extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_occurrences';
	public $table_index = 'occurrencesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = [];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_occurrences'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_occurrences' => 'occurrencesid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_TOPIC' => 'topic',
		'Assigned To' => 'assigned_user_id',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		'FL_TOPIC' => ['occurrences', 'topic'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['topic'];
	// For Alphabetical search
	public $def_basicsearch_col = 'topic';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'topic';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['topic', 'assigned_user_id'];
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
		} elseif ('module.disabled' === $eventType) {
		} elseif ('module.preuninstall' === $eventType) {
		} elseif ('module.preupdate' === $eventType) {
		} elseif ('module.postupdate' === $eventType) {
		}
	}
}
