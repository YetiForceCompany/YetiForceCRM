<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

class PurchaseOrder extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_purchaseorder";
	var $table_index = 'purchaseorderid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_purchaseorder', 'vtiger_purchaseorderaddress', 'vtiger_purchaseordercf', 'vtiger_inventoryproductrel');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_purchaseorder' => 'purchaseorderid', 'vtiger_purchaseorderaddress' => 'purchaseorderaddressid', 'vtiger_purchaseordercf' => 'purchaseorderid', 'vtiger_inventoryproductrel' => 'id');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_purchaseordercf', 'purchaseorderid');
	var $entity_table = "vtiger_crmentity";
	var $billadr_table = "vtiger_purchaseorderaddress";
	var $column_fields = Array();
	var $sortby_fields = Array('subject', 'tracking_no', 'smownerid', 'lastname');
	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		//  Module Sequence Numbering
		//'Order No'=>Array('crmentity'=>'crmid'),
		'Order No' => Array('purchaseorder' => 'purchaseorder_no'),
		// END
		'Subject' => Array('purchaseorder' => 'subject'),
		'Vendor Name' => Array('purchaseorder' => 'vendorid'),
		'Tracking Number' => Array('purchaseorder' => 'tracking_no'),
		'Total' => Array('purchaseorder' => 'total'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Order No' => 'purchaseorder_no',
		'Subject' => 'subject',
		'Vendor Name' => 'vendor_id',
		'Tracking Number' => 'tracking_no',
		'Total' => 'hdnGrandTotal',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'subject';
	var $search_fields = Array(
		'Order No' => Array('purchaseorder' => 'purchaseorder_no'),
		'Subject' => Array('purchaseorder' => 'subject'),
	);
	var $search_fields_name = Array(
		'Order No' => 'purchaseorder_no',
		'Subject' => 'subject',
	);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject', 'vendor_id', 'createdtime', 'modifiedtime', 'assigned_user_id');
	// This is the list of vtiger_fields that are required.
	var $required_fields = array("accountname" => 1);
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'subject';
	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	//var $groupTable = Array('vtiger_pogrouprelation','purchaseorderid');
	/** Constructor Function for Order class
	 *  This function creates an instance of LoggerManager class using getLogger method
	 *  creates an instance for PearDatabase class and get values for column_fields array of Order class.
	 */
	function PurchaseOrder()
	{
		$this->log = LoggerManager::getLogger('PurchaseOrder');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('PurchaseOrder');
	}

	function save_module($module)
	{
		global $adb, $updateInventoryProductRel_deduct_stock;
		$updateInventoryProductRel_deduct_stock = false;
		//in ajax save we should not call this function, because this will delete all the existing product values
		if ($_REQUEST['action'] != 'PurchaseOrderAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {

			$requestProductIdsList = $requestQuantitiesList = array();
			$totalNoOfProducts = $_REQUEST['totalProductCount'];
			for ($i = 1; $i <= $totalNoOfProducts; $i++) {
				$productId = $_REQUEST['hdnProductId' . $i];
				$requestProductIdsList[$productId] = $productId;
				if (array_key_exists($productId, $requestQuantitiesList)) {
					$requestQuantitiesList[$productId] = $requestQuantitiesList[$productId] + $_REQUEST['qty' . $i];
					continue;
				}
				$requestQuantitiesList[$productId] = $_REQUEST['qty' . $i];
			}

			if ($this->mode == '' && $this->column_fields['postatus'] === 'Received Shipment') {				   //Updating Product stock quantity during create mode
				foreach ($requestProductIdsList as $productId) {
					addToProductStock($productId, $requestQuantitiesList[$productId]);
				}
			} else if ($this->column_fields['postatus'] === 'Received Shipment' && $this->mode != '') {  //Updating Product stock quantity during edit mode
				$recordId = $this->id;
				$result = $adb->pquery("SELECT productid, quantity FROM vtiger_inventoryproductrel WHERE id = ?", array($recordId));
				$numOfRows = $adb->num_rows($result);
				for ($i = 0; $i < $numOfRows; $i++) {
					$productId = $adb->query_result($result, $i, 'productid');
					$productIdsList[$productId] = $productId;
					$quantitiesList[$productId] = $adb->query_result($result, $i, 'quantity');
				}

				$newProductIds = array_diff($requestProductIdsList, $productIdsList);
				if ($newProductIds) {
					foreach ($newProductIds as $productId) {
						addToProductStock($productId, $requestQuantitiesList[$productId]);
					}
				}

				$deletedProductIds = array_diff($productIdsList, $requestProductIdsList);
				if ($deletedProductIds) {
					foreach ($deletedProductIds as $productId) {
						$productStock = getPrdQtyInStck($productId);
						$quantity = $productStock - $quantitiesList[$productId];
						updateProductQty($productId, $quantity);
					}
				}

				$updatedProductIds = array_intersect($productIdsList, $requestProductIdsList);
				if ($updatedProductIds) {
					foreach ($updatedProductIds as $productId) {
						$quantityDiff = $quantitiesList[$productId] - $requestQuantitiesList[$productId];
						if ($quantityDiff < 0) {
							$quantityDiff = -($quantityDiff);
							addToProductStock($productId, $quantityDiff);
						} elseif ($quantityDiff > 0) {
							$productStock = getPrdQtyInStck($productId);
							$quantity = $productStock - $quantityDiff;
							updateProductQty($productId, $quantity);
						}
					}
				}
			}

			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'PurchaseOrder', $this->update_prod_stock);

			if ($this->mode != '') {
				$updateInventoryProductRel_deduct_stock = true;
			}
		}

		// Update the currency id and the conversion rate for the purchase order
		$update_query = "update vtiger_purchaseorder set currency_id=?, conversion_rate=? where purchaseorderid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$adb->pquery($update_query, $update_params);
	}

	/** 	Function used to get the Status history of the Purchase Order
	 * 	@param $id - purchaseorder id
	 * 	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_postatushistory($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_postatushistory(" . $id . ") method ...");

		$adb = PearDatabase::getInstance();
		global $mod_strings;
		global $app_strings;

		$query = 'select vtiger_postatushistory.*, vtiger_purchaseorder.purchaseorder_no from vtiger_postatushistory inner join vtiger_purchaseorder on vtiger_purchaseorder.purchaseorderid = vtiger_postatushistory.purchaseorderid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_purchaseorder.purchaseorderid where vtiger_crmentity.deleted = 0 and vtiger_purchaseorder.purchaseorderid = ?';
		$result = $adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Order No'];
		$header[] = $app_strings['Vendor Name'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_PO_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Vendor, Total are mandatory fields. So no need to do security check to these fields.
		$current_user = vglobal('current_user');

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$postatus_access = (getFieldVisibilityPermission('PurchaseOrder', $current_user->id, 'postatus') != '0') ? 1 : 0;
		$picklistarray = getAccessPickListValues('PurchaseOrder');

		$postatus_array = ($postatus_access != 1) ? $picklistarray['postatus'] : array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($postatus_access != 1) ? 'Not Accessible' : '-';

		while ($row = $adb->fetch_array($result)) {
			$entries = Array();

			//Module Sequence Numbering
			//$entries[] = $row['purchaseorderid'];
			$entries[] = $row['purchaseorder_no'];
			// END
			$entries[] = $row['vendorname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['postatus'], $postatus_array)) ? $row['postatus'] : $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header' => $header, 'entries' => $entries_list);

		$log->debug("Exiting get_postatushistory method ...");

		return $return_data;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{

		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityPurchaseOrder', array('vtiger_usersPurchaseOrder', 'vtiger_groupsPurchaseOrder', 'vtiger_lastModifiedByPurchaseOrder'));
		$matrix->setDependency('vtiger_inventoryproductrelPurchaseOrder', array('vtiger_productsPurchaseOrder', 'vtiger_servicePurchaseOrder'));
		$matrix->setDependency('vtiger_purchaseorder', array('vtiger_crmentityPurchaseOrder', "vtiger_currency_info$secmodule",
			'vtiger_purchaseordercf', 'vtiger_vendorRelPurchaseOrder', 'vtiger_purchaseorderaddress',
			'vtiger_inventoryproductrelPurchaseOrder', 'vtiger_contactdetailsPurchaseOrder'));

		if (!$queryPlanner->requireTable('vtiger_purchaseorder', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_purchaseorder", "purchaseorderid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityPurchaseOrder", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityPurchaseOrder on vtiger_crmentityPurchaseOrder.crmid=vtiger_purchaseorder.purchaseorderid and vtiger_crmentityPurchaseOrder.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_purchaseordercf")) {
			$query .= " left join vtiger_purchaseordercf on vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid";
		}
		if ($queryPlanner->requireTable("vtiger_purchaseorderaddress")) {
			$query .= " left join vtiger_purchaseorderaddress on vtiger_purchaseorder.purchaseorderid=vtiger_purchaseorderaddress.purchaseorderaddressid";
		}
		if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
			$query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_purchaseorder.currency_id";
		}
		if ($queryPlanner->requireTable("vtiger_inventoryproductrelPurchaseOrder", $matrix)) {
			$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductrelPurchaseOrder on vtiger_purchaseorder.purchaseorderid = vtiger_inventoryproductrelPurchaseOrder.id";
			// To Eliminate duplicates in reports
			if (($module == 'Products' || $module == 'Services') && $secmodule == "PurchaseOrder") {
				if ($module == 'Products') {
					$query .= " and vtiger_inventoryproductrelPurchaseOrder.productid = vtiger_products.productid ";
				} else if ($module == 'Services') {
					$query .= " and vtiger_inventoryproductrelPurchaseOrder.productid = vtiger_service.serviceid ";
				}
			}
		}
		if ($queryPlanner->requireTable("vtiger_productsPurchaseOrder")) {
			$query .= " left join vtiger_products as vtiger_productsPurchaseOrder on vtiger_productsPurchaseOrder.productid = vtiger_inventoryproductrelPurchaseOrder.productid";
		}
		if ($queryPlanner->requireTable("vtiger_servicePurchaseOrder")) {
			$query .= " left join vtiger_service as vtiger_servicePurchaseOrder on vtiger_servicePurchaseOrder.serviceid = vtiger_inventoryproductrelPurchaseOrder.productid";
		}
		if ($queryPlanner->requireTable("vtiger_usersPurchaseOrder")) {
			$query .= " left join vtiger_users as vtiger_usersPurchaseOrder on vtiger_usersPurchaseOrder.id = vtiger_crmentityPurchaseOrder.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsPurchaseOrder")) {
			$query .= " left join vtiger_groups as vtiger_groupsPurchaseOrder on vtiger_groupsPurchaseOrder.groupid = vtiger_crmentityPurchaseOrder.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_vendorRelPurchaseOrder")) {
			$query .= " left join vtiger_vendor as vtiger_vendorRelPurchaseOrder on vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendorid";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsPurchaseOrder")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder on vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contactid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByPurchaseOrder")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByPurchaseOrder on vtiger_lastModifiedByPurchaseOrder.id = vtiger_crmentityPurchaseOrder.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyPurchaseOrder")) {
			$query .= " left join vtiger_users as vtiger_createdbyPurchaseOrder on vtiger_createdbyPurchaseOrder.id = vtiger_crmentityPurchaseOrder.smcreatorid ";
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	function setRelationTables($secmodule)
	{
		$rel_tables = array(
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_purchaseorder" => "purchaseorderid")
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Vendors') {
			$sql_req = 'UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid= ?';
			$this->db->pquery($sql_req, array($id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')
	{
		//Ignore relation table insertions while saving of the record
		if ($table_name == 'vtiger_inventoryproductrel') {
			return;
		}
		parent::insertIntoEntityTable($table_name, $module, $fileid);
	}
	/* Function to create records in current module.
	 * *This function called while importing records to this module */

	function createRecords($obj)
	{
		$createRecords = createRecords($obj);
		return $createRecords;
	}
	/* Function returns the record information which means whether the record is imported or not
	 * *This function called while importing records to this module */

	function importRecord($obj, $inventoryFieldData, $lineItemDetails)
	{
		$entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
		return $entityInfo;
	}
	/* Function to return the status count of imported records in current module.
	 * *This function called while importing records to this module */

	function getImportStatusCount($obj)
	{
		$statusCount = getImportStatusCount($obj);
		return $statusCount;
	}

	function undoLastImport($obj, $user)
	{
		$undoLastImport = undoLastImport($obj, $user);
	}

	/** Function to export the lead records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export PurchaseOrder Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("PurchaseOrder", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_purchaseorder ON vtiger_purchaseorder.purchaseorderid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_purchaseordercf ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_purchaseorderaddress ON vtiger_purchaseorderaddress.purchaseorderaddressid = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_purchaseorder.purchaseorderid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_purchaseorder.contactid
				LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_purchaseorder.vendorid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_purchaseorder.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('PurchaseOrder', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != "") {
			$query .= " where ($where) AND " . $where_auto;
		} else {
			$query .= " where " . $where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}
}
