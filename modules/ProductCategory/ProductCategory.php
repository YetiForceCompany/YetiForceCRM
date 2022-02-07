<?php
/**
 * Main ProductCategory module file.
 *
 * @package   App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

/**
 * Class ProductCategory class.
 */
class ProductCategory extends Vtiger_CRMEntity
{
	public $table_name = 'u_yf_productcategory';
	public $table_index = 'productcategoryid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['u_yf_productcategorycf', 'productcategoryid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'u_yf_productcategory', 'u_yf_productcategorycf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_productcategory' => 'productcategoryid',
		'u_yf_productcategorycf' => 'productcategoryid',
	];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'FL_CATEGORY_NAME' => 'category',
		'FL_PARENT_CATEGORY' => 'parent_id',
		'Assigned To' => 'assigned_user_id',
	];

	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		'FL_CATEGORY_NAME' => ['productcategory', 'category'],
		'FL_PARENT_CATEGORY' => ['productcategory', 'parent_id'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['category'];
	// For Alphabetical search
	public $def_basicsearch_col = 'category';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'category';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['category', 'assigned_user_id'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type
	 * @param mixed $moduleName
	 * @param mixed $eventType
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
