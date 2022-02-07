<?php
/**
 * OSSOutsourcedServices CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class OSSOutsourcedServices extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_ossoutsourcedservices';
	public $table_index = 'ossoutsourcedservicesid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ossoutsourcedservicescf', 'ossoutsourcedservicesid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ossoutsourcedservices', 'vtiger_ossoutsourcedservicescf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossoutsourcedservices' => 'ossoutsourcedservicesid',
		'vtiger_ossoutsourcedservicescf' => 'ossoutsourcedservicesid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Product Name' => 'productname',
		'Category' => 'pscategory',
		'Assigned To' => 'assigned_user_id',
		'Date Sold' => 'datesold',
		'LBL_osservicesstatus' => 'osservicesstatus',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		'Product Name' => ['ossoutsourcedservices' => 'productname'],
		'Category' => ['ossoutsourcedservices' => 'pscategory'],
		'Sub Category' => ['ossoutsourcedservices' => 'pssubcategory'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Date Sold' => ['ossoutsourcedservices' => 'datesold'],
		'LBL_osservicesstatus' => ['ossoutsourcedservices' => 'osservicesstatus'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['productname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'productname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'productname';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'productname'];
}
