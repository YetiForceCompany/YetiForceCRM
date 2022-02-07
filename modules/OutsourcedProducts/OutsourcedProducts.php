<?php
/**
 * OutsourcedProducts CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class OutsourcedProducts extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_outsourcedproducts';
	public $table_index = 'outsourcedproductsid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	public $related_tables = ['vtiger_outsourcedproductscf' => ['outsourcedproductsid', 'vtiger_outsourcedproducts', 'outsourcedproductsid']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_outsourcedproductscf', 'outsourcedproductsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_outsourcedproducts', 'vtiger_outsourcedproductscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_outsourcedproducts' => 'outsourcedproductsid',
		'vtiger_outsourcedproductscf' => 'outsourcedproductsid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'Product Name' => 'productname',
		'Category' => 'pscategory',
		'Sub Category' => 'pssubcategory',
		'Assigned To' => 'assigned_user_id',
		'Date Sold' => 'datesold',
		'Status' => 'oproductstatus',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		'Product Name' => ['outsourcedproducts' => 'productname'],
		'Category' => ['outsourcedproducts' => 'pscategory'],
		'Sub Category' => ['outsourcedproducts' => 'pssubcategory'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Date Sold' => ['outsourcedproducts' => 'datesold'],
		'Status' => ['outsourcedproducts' => 'oproductstatus'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['productname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'productname';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'productname'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
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
			$dbCommand = \App\Db::getInstance()->createCommand();
			// Mark the module as Standard module
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			//adds sharing accsess
			$AssetsModule = vtlib\Module::getInstance($moduleName);
			vtlib\Access::setDefaultSharing($AssetsModule);

			//Showing Assets module in the related modules in the More Information Tab
		}
	}
}
