<?php
/**
 * OutsourcedProducts CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'Product Name' => ['outsourcedproducts' => 'productname'],
		'Category' => ['outsourcedproducts' => 'pscategory'],
		'Sub Category' => ['outsourcedproducts' => 'pssubcategory'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Date Sold' => ['outsourcedproducts' => 'datesold'],
		'Status' => ['outsourcedproducts' => 'oproductstatus'],
	];
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
	public $relationFields = ['productname', 'pscategory', 'assigned_user_id', 'datesold', 'oproductstatus'];
	// Make the field link to detail view
	public $list_link_field = 'productname';
	// For Popup listview and UI type support
	public $search_fields = [
		'Product Name' => ['outsourcedproducts' => 'productname'],
		'Category' => ['outsourcedproducts' => 'pscategory'],
		'Sub Category' => ['outsourcedproducts' => 'pssubcategory'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Date Sold' => ['outsourcedproducts' => 'datesold'],
		'Status' => ['outsourcedproducts' => 'oproductstatus'],
	];
	public $search_fields_name = [
		'Product Name' => 'productname',
		'Category' => 'pscategory',
		'Sub Category' => 'pssubcategory',
		'Assigned To' => 'assigned_user_id',
		'Date Sold' => 'datesold',
		'Status' => 'oproductstatus',
	];
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
	public $unit_price;

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string Module name
	 * @param string Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			$dbCommand = \App\Db::getInstance()->createCommand();
			// Mark the module as Standard module
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			//adds sharing accsess
			$AssetsModule = vtlib\Module::getInstance($moduleName);
			vtlib\Access::setDefaultSharing($AssetsModule);

			//Showing Assets module in the related modules in the More Information Tab
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string $module            This module name
	 * @param array  $transferEntityIds List of Entity Id's from which related records need to be transfered
	 * @param int    $entityId          Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		\App\Log::trace('Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)');

		$relTableArr = ['Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel'];

		$tblFieldArr = ['vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid'];

		$entityTblFieldArr = ['vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid'];

		foreach ($transferEntityIds as $transferId) {
			foreach ($relTableArr as $relTable) {
				$idField = $tblFieldArr[$relTable];
				$entityIdField = $entityTblFieldArr[$relTable];
				// IN clause to avoid duplicate entries
				$subQuery = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $entityId]);
				$query = (new App\Db\Query())->select([$idField])->from($relTable)->where([$entityIdField => $transferId])->andWhere(['not in', $idField, $subQuery]);
				$dataReader = $query->createCommand()->query();
				while ($idFieldValue = $dataReader->readColumn(0)) {
					$dbCommand->update($relTable, [$entityIdField => $entityId], [$entityIdField => $transferId, $idField => $idFieldValue])->execute();
				}
				$dataReader->close();
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}
}
