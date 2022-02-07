<?php
/**
 * DataSetRegister crmentity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class DataSetRegister extends Vtiger_CRMEntity
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table_name = 'u_yf_datasetregister';
	/**
	 * Table index.
	 *
	 * @var string
	 */
	public $table_index = 'datasetregisterid';

	/**
	 * Mandatory table for supporting custom fields.
	 *
	 * @var array
	 */
	public $customFieldTable = ['u_yf_datasetregistercf', 'datasetregisterid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 *
	 * @var array
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_datasetregister', 'u_yf_datasetregistercf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 *
	 * @var array
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_datasetregister' => 'datasetregisterid',
		'u_yf_datasetregistercf' => 'datasetregisterid',
	];

	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
		'FL_STATUS' => 'datasetregister_status',
		'FL_ADDED_TO_REGISTER' => 'added_to_register',
		'FL_REMOVED_FROM_REGISTER' => 'removed_from_register',
		'FL_DATA_SUBMITTED' => 'data_submitted'
	];

	/**
	 * For Popup listview and UI type support.
	 *
	 * @var array
	 */
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['datasetregister', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
		'FL_STATUS' => ['datasetregister', 'datasetregister_status'],
		'FL_ADDED_TO_REGISTER' => ['datasetregister', 'added_to_register'],
		'FL_REMOVED_FROM_REGISTER' => ['datasetregister', 'removed_from_register'],
		'FL_DATA_SUBMITTED' => ['datasetregister', 'data_submitted']
	];
	/**
	 * Search fields name.
	 *
	 * @var array
	 */
	public $search_fields_name = [];
	/**
	 * For Popup window record selection.
	 *
	 * @var array
	 */
	public $popup_fields = ['subject', 'datasetregister_status', 'added_to_register', 'removed_from_register', 'data_submitted'];
	/**
	 * For Alphabetical search.
	 *
	 * @var string
	 */
	public $def_basicsearch_col = 'subject';
	/**
	 * Column value to use on detail view record text display.
	 *
	 * @var string
	 */
	public $def_detailview_recname = 'subject';
	/**
	 * Used when enabling/disabling the mandatory fields for the module. Refers to vtiger_field.fieldname values.
	 *
	 * @var array
	 */
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	/**
	 * Default order by.
	 *
	 * @var string
	 */
	public $default_order_by = '';
	/**
	 * Default sort order.
	 *
	 * @var string
	 */
	public $default_sort_order = 'ASC';
}
