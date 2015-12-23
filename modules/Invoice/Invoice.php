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

class Invoice extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_invoice";
	var $table_index = 'invoiceid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_invoice', 'vtiger_invoiceaddress', 'vtiger_invoicecf', 'vtiger_inventoryproductrel');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_invoice' => 'invoiceid', 'vtiger_invoiceaddress' => 'invoiceaddressid', 'vtiger_invoicecf' => 'invoiceid', 'vtiger_inventoryproductrel' => 'id');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_invoicecf', 'invoiceid');
	var $column_fields = Array();
	var $update_product_array = Array();
	var $sortby_fields = Array('subject', 'invoice_no', 'invoicestatus', 'smownerid', 'accountname', 'lastname');
	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		//'Invoice No'=>Array('crmentity'=>'crmid'),
		'Invoice No' => Array('invoice' => 'invoice_no'),
		'Subject' => Array('invoice' => 'subject'),
		'Status' => Array('invoice' => 'invoicestatus'),
		'Total' => Array('invoice' => 'total'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Invoice No' => 'invoice_no',
		'Subject' => 'subject',
		'Status' => 'invoicestatus',
		'Total' => 'hdnGrandTotal',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'subject';
	var $search_fields = Array(
		//'Invoice No'=>Array('crmentity'=>'crmid'),
		'Invoice No' => Array('invoice' => 'invoice_no'),
		'Subject' => Array('purchaseorder' => 'subject'),
		'Account Name' => Array('contactdetails' => 'account_id'),
		'Created Date' => Array('crmentity' => 'createdtime'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
	);
	var $search_fields_name = Array(
		'Invoice No' => 'invoice_no',
		'Subject' => 'subject',
		'Account Name' => 'account_id',
		'Created Time' => 'createdtime',
		'Assigned To' => 'assigned_user_id'
	);
	// This is the list of vtiger_fields that are required.
	var $required_fields = array("accountname" => 1);
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('vtiger_invoicegrouprelation','invoiceid');

	var $mandatory_fields = Array('subject', 'createdtime', 'modifiedtime', 'assigned_user_id');
	var $_recurring_mode;
	// For Alphabetical search
	var $def_basicsearch_col = 'subject';
	var $entity_table = "vtiger_crmentity";
	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/** 	Constructor which will set the column_fields in this object
	 */
	function Invoice()
	{
		$this->log = LoggerManager::getLogger('Invoice');
		$this->log->debug("Entering Invoice() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Invoice');
		$this->log->debug("Exiting Invoice method ...");
	}

	/** Function to handle the module specific save operations

	 */
	function save_module($module)
	{
		global $updateInventoryProductRel_deduct_stock;
		$updateInventoryProductRel_deduct_stock = true;

		//in ajax save we should not call this function, because this will delete all the existing product values
		if (isset($_REQUEST)) {
			if ($_REQUEST['action'] != 'InvoiceAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
				//Based on the total Number of rows we will save the product relationship with this entity
				saveInventoryProductDetails($this, 'Invoice');
			} else if ($_REQUEST['action'] == 'InvoiceAjax' || $_REQUEST['action'] == 'MassEditSave' || $_REQUEST['action'] == 'FROM_WS') {
				$updateInventoryProductRel_deduct_stock = false;
			}
		}
		// Update the currency id and the conversion rate for the invoice
		$update_query = "update vtiger_invoice set currency_id=?, conversion_rate=? where invoiceid=?";

		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$this->db->pquery($update_query, $update_params);
	}

	/**
	 * Customizing the restore procedure.
	 */
	function restore($module, $id)
	{
		global $updateInventoryProductRel_deduct_stock;
		$status = getInvoiceStatus($id);
		if ($status != 'Cancel') {
			$updateInventoryProductRel_deduct_stock = true;
		}
		parent::restore($module, $id);
	}

	/**
	 * Customizing the Delete procedure.
	 */
	function trash($module, $recordId)
	{
		$status = getInvoiceStatus($recordId);
		if ($status != 'Cancel') {
			addProductsToStock($recordId);
		}
		parent::trash($module, $recordId);
	}

	/** 	function used to get the name of the current object
	 * 	@return string $this->name - name of the current object
	 */
	function get_summary_text()
	{
		$log = vglobal('log');
		$log->debug("Entering get_summary_text() method ...");
		$log->debug("Exiting get_summary_text method ...");
		return $this->name;
	}

	/** 	Function used to get the Status history of the Invoice
	 * 	@param $id - invoice id
	 * 	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_invoicestatushistory($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_invoicestatushistory(" . $id . ") method ...");

		$adb = PearDatabase::getInstance();
		global $mod_strings;
		global $app_strings;

		$query = 'select vtiger_invoicestatushistory.*, vtiger_invoice.invoice_no from vtiger_invoicestatushistory inner join vtiger_invoice on vtiger_invoice.invoiceid = vtiger_invoicestatushistory.invoiceid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_invoice.invoiceid where vtiger_crmentity.deleted = 0 and vtiger_invoice.invoiceid = ?';
		$result = $adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Invoice No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_INVOICE_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Amount are mandatory fields. So no need to do security check to these fields.
		$current_user = vglobal('current_user');

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$invoicestatus_access = (getFieldVisibilityPermission('Invoice', $current_user->id, 'invoicestatus') != '0') ? 1 : 0;
		$picklistarray = getAccessPickListValues('Invoice');

		$invoicestatus_array = ($invoicestatus_access != 1) ? $picklistarray['invoicestatus'] : array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($invoicestatus_access != 1) ? 'Not Accessible' : '-';

		while ($row = $adb->fetch_array($result)) {
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['invoiceid'];
			$entries[] = $row['invoice_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['invoicestatus'], $invoicestatus_array)) ? $row['invoicestatus'] : $error_msg;
			$entries[] = DateTimeField::convertToUserFormat($row['lastmodified']);

			$entries_list[] = $entries;
		}

		$return_data = Array('header' => $header, 'entries' => $entries_list);

		$log->debug("Exiting get_invoicestatushistory method ...");

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

		// Define the dependency matrix ahead
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityInvoice', array('vtiger_usersInvoice', 'vtiger_groupsInvoice', 'vtiger_lastModifiedByInvoice'));
		$matrix->setDependency('vtiger_inventoryproductrelInvoice', array('vtiger_productsInvoice', 'vtiger_serviceInvoice'));
		$matrix->setDependency('vtiger_invoice', array('vtiger_crmentityInvoice', "vtiger_currency_info$secmodule",
			'vtiger_invoicecf', 'vtiger_salesorderInvoice', 'vtiger_invoiceaddress',
			'vtiger_inventoryproductrelInvoice', 'vtiger_contactdetailsInvoice', 'vtiger_accountInvoice'));

		if (!$queryPlanner->requireTable('vtiger_invoice', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_invoice", "invoiceid", $queryPlanner);

		if ($queryPlanner->requireTable('vtiger_crmentityInvoice', $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityInvoice on vtiger_crmentityInvoice.crmid=vtiger_invoice.invoiceid and vtiger_crmentityInvoice.deleted=0";
		}
		if ($queryPlanner->requireTable('vtiger_invoicecf')) {
			$query .= " left join vtiger_invoicecf on vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid";
		}
		if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
			$query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_invoice.currency_id";
		}
		if ($queryPlanner->requireTable('vtiger_invoiceaddress')) {
			$query .= " left join vtiger_invoiceaddress on vtiger_invoice.invoiceid=vtiger_invoiceaddress.invoiceaddressid";
		}
		if ($queryPlanner->requireTable('vtiger_inventoryproductrelInvoice', $matrix)) {
			$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductrelInvoice on vtiger_invoice.invoiceid = vtiger_inventoryproductrelInvoice.id";
			// To Eliminate duplicates in reports
			if (($module == 'Products' || $module == 'Services') && $secmodule == "Invoice") {
				if ($module == 'Products') {
					$query .= " and vtiger_inventoryproductrelInvoice.productid = vtiger_products.productid ";
				} else if ($module == 'Services') {
					$query .= " and vtiger_inventoryproductrelInvoice.productid = vtiger_service.serviceid ";
				}
			}
		}
		if ($queryPlanner->requireTable('vtiger_productsInvoice')) {
			$query .= " left join vtiger_products as vtiger_productsInvoice on vtiger_productsInvoice.productid = vtiger_inventoryproductrelInvoice.productid";
		}
		if ($queryPlanner->requireTable('vtiger_serviceInvoice')) {
			$query .= " left join vtiger_service as vtiger_serviceInvoice on vtiger_serviceInvoice.serviceid = vtiger_inventoryproductrelInvoice.productid";
		}
		if ($queryPlanner->requireTable('vtiger_groupsInvoice')) {
			$query .= " left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentityInvoice.smownerid";
		}
		if ($queryPlanner->requireTable('vtiger_usersInvoice')) {
			$query .= " left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentityInvoice.smownerid";
		}
		if ($queryPlanner->requireTable('vtiger_contactdetailsInvoice')) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_invoice.contactid = vtiger_contactdetailsInvoice.contactid";
		}
		if ($queryPlanner->requireTable('vtiger_accountInvoice')) {
			$query .= " left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.accountid";
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedByInvoice')) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByInvoice on vtiger_lastModifiedByInvoice.id = vtiger_crmentityInvoice.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyInvoice")) {
			$query .= " left join vtiger_users as vtiger_createdbyInvoice on vtiger_createdbyInvoice.id = vtiger_crmentityInvoice.smcreatorid ";
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
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_invoice" => "invoiceid"),
			"Accounts" => array("vtiger_invoice" => array("invoiceid", "accountid")),
			"Contacts" => array("vtiger_invoice" => array("invoiceid", "contactid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Accounts' || $return_module == 'Contacts') {
			$this->trash('Invoice', $id);
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
	 * Returns Export Invoice Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Invoice", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_invoice ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_invoiceaddress ON vtiger_invoiceaddress.invoiceaddressid = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_invoice.contactid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_invoice.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_invoice.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Invoice', $current_user);
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
