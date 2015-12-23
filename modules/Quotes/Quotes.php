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

class Quotes extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_quotes";
	var $table_index = 'quoteid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_quotes', 'vtiger_quotesaddress', 'vtiger_quotescf', 'vtiger_inventoryproductrel');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_quotes' => 'quoteid', 'vtiger_quotesaddress' => 'quoteaddressid', 'vtiger_quotescf' => 'quoteid', 'vtiger_inventoryproductrel' => 'id');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_quotescf', 'quoteid');
	var $entity_table = "vtiger_crmentity";
	var $object_name = "Quote";
	var $new_schema = true;
	var $column_fields = Array();
	var $sortby_fields = Array('subject', 'crmid', 'smownerid', 'accountname', 'lastname');
	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		//'Quote No'=>Array('crmentity'=>'crmid'),
		// Module Sequence Numbering
		'Quote No' => Array('quotes' => 'quote_no'),
		// END
		'Subject' => Array('quotes' => 'subject'),
		'Quote Stage' => Array('quotes' => 'quotestage'),
		'Potential Name' => Array('quotes' => 'potentialid'),
		'Account Name' => Array('account' => 'accountid'),
		'Total' => Array('quotes' => 'total'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Quote No' => 'quote_no',
		'Subject' => 'subject',
		'Quote Stage' => 'quotestage',
		'Potential Name' => 'potential_id',
		'Account Name' => 'account_id',
		'Total' => 'hdnGrandTotal',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'subject';
	var $search_fields = Array(
		'Quote No' => Array('quotes' => 'quote_no'),
		'Subject' => Array('quotes' => 'subject'),
		'Account Name' => Array('quotes' => 'accountid'),
		'Quote Stage' => Array('quotes' => 'quotestage'),
	);
	var $search_fields_name = Array(
		'Quote No' => 'quote_no',
		'Subject' => 'subject',
		'Account Name' => 'account_id',
		'Quote Stage' => 'quotestage',
	);
	// This is the list of vtiger_fields that are required.
	var $required_fields = array("accountname" => 1);
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('vtiger_quotegrouprelation','quoteid');

	var $mandatory_fields = Array('subject', 'createdtime', 'modifiedtime', 'assigned_user_id');
	// For Alphabetical search
	var $def_basicsearch_col = 'subject';
	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/** 	Constructor which will set the column_fields in this object
	 */
	function Quotes()
	{
		$this->log = LoggerManager::getLogger('quote');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Quotes');
	}

	function save_module()
	{
		$adb = PearDatabase::getInstance();
		//in ajax save we should not call this function, because this will delete all the existing product values
		if ($_REQUEST['action'] != 'QuotesAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'Quotes');
		}

		// Update the currency id and the conversion rate for the quotes
		$update_query = "update vtiger_quotes set currency_id=?, conversion_rate=? where quoteid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$adb->pquery($update_query, $update_params);

		$tot_no_prod = $_REQUEST['totalProductCount'];
	}

	/** 	function used to get the list of sales orders which are related to the Quotes
	 * 	@param int $id - quote id
	 * 	@return array - return an array which will be returned from the function GetRelatedList
	 */
	function get_salesorder($id)
	{
		global $log, $singlepane_view;
		$log->debug("Entering get_salesorder(" . $id . ") method ...");
		require_once('modules/SalesOrder/SalesOrder.php');
		$focus = new SalesOrder();

		$button = '';

		if ($singlepane_view == 'true')
			$returnset = '&return_module=Quotes&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=Quotes&return_action=CallRelatedList&return_id=' . $id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "select vtiger_crmentity.*, vtiger_salesorder.*, vtiger_quotes.subject as quotename
			, vtiger_account.accountname,case when (vtiger_users.user_name not like '') then
			$userNameSql else vtiger_groups.groupname end as user_name
		from vtiger_salesorder
		inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_salesorder.salesorderid
		left outer join vtiger_quotes on vtiger_quotes.quoteid=vtiger_salesorder.quoteid
		left outer join vtiger_account on vtiger_account.accountid=vtiger_salesorder.accountid
		left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
        LEFT JOIN vtiger_salesordercf ON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid
        LEFT JOIN vtiger_invoice_recurring_info ON vtiger_invoice_recurring_info.start_period = vtiger_salesorder.salesorderid
		LEFT JOIN vtiger_salesorderaddress ON vtiger_salesorderaddress.salesorderaddressid = vtiger_salesorder.salesorderid
		left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
		where vtiger_crmentity.deleted=0 and vtiger_salesorder.quoteid = " . $id;
		$log->debug("Exiting get_salesorder method ...");
		return GetRelatedList('Quotes', 'SalesOrder', $focus, $query, $button, $returnset);
	}

	/** 	Function used to get the Quote Stage history of the Quotes
	 * 	@param $id - quote id
	 * 	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_quotestagehistory($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_quotestagehistory(" . $id . ") method ...");

		$adb = PearDatabase::getInstance();
		global $mod_strings;
		global $app_strings;

		$query = 'select vtiger_quotestagehistory.*, vtiger_quotes.quote_no from vtiger_quotestagehistory inner join vtiger_quotes on vtiger_quotes.quoteid = vtiger_quotestagehistory.quoteid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_quotes.quoteid where vtiger_crmentity.deleted = 0 and vtiger_quotes.quoteid = ?';
		$result = $adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Quote No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['Quote Stage'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		$current_user = vglobal('current_user');

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$quotestage_access = (getFieldVisibilityPermission('Quotes', $current_user->id, 'quotestage') != '0') ? 1 : 0;
		$picklistarray = getAccessPickListValues('Quotes');

		$quotestage_array = ($quotestage_access != 1) ? $picklistarray['quotestage'] : array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($quotestage_access != 1) ? 'Not Accessible' : '-';

		while ($row = $adb->fetch_array($result)) {
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['quoteid'];
			$entries[] = $row['quote_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['quotestage'], $quotestage_array)) ? $row['quotestage'] : $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header' => $header, 'entries' => $entries_list);

		$log->debug("Exiting get_quotestagehistory method ...");

		return $return_data;
	}

	// Function to get column name - Overriding function of base class
	function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype = '')
	{
		if ($columname == 'potentialid' || $columname == 'contactid') {
			if ($fldvalue == '')
				return null;
		}
		return parent::get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
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
		$matrix->setDependency('vtiger_crmentityQuotes', array('vtiger_usersQuotes', 'vtiger_groupsQuotes', 'vtiger_lastModifiedByQuotes'));
		$matrix->setDependency('vtiger_inventoryproductrelQuotes', array('vtiger_productsQuotes', 'vtiger_serviceQuotes'));
		$matrix->setDependency('vtiger_quotes', array('vtiger_crmentityQuotes', "vtiger_currency_info$secmodule",
			'vtiger_quotescf', 'vtiger_potentialRelQuotes', 'vtiger_quotesaddress',
			'vtiger_inventoryproductrelQuotes', 'vtiger_accountQuotes',
			'vtiger_invoice_recurring_info', 'vtiger_quotesQuotes', 'vtiger_usersRel1'));

		if (!$queryPlanner->requireTable('vtiger_quotes', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_quotes", "quoteid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityQuotes", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityQuotes on vtiger_crmentityQuotes.crmid=vtiger_quotes.quoteid and vtiger_crmentityQuotes.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_quotescf")) {
			$query .= " left join vtiger_quotescf on vtiger_quotes.quoteid = vtiger_quotescf.quoteid";
		}
		if ($queryPlanner->requireTable("vtiger_quotesaddress")) {
			$query .= " left join vtiger_quotesaddress on vtiger_quotes.quoteid=vtiger_quotesaddress.quoteaddressid";
		}
		if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
			$query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_quotes.currency_id";
		}
		if ($queryPlanner->requireTable("vtiger_inventoryproductrelQuotes", $matrix)) {
			$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductrelQuotes on vtiger_quotes.quoteid = vtiger_inventoryproductrelQuotes.id";
			// To Eliminate duplicates in reports
			if (($module == 'Products' || $module == 'Services') && $secmodule == "Quotes") {
				if ($module == 'Products') {
					$query .= " and vtiger_inventoryproductrelQuotes.productid = vtiger_products.productid ";
				} else if ($module == 'Services') {
					$query .= " and vtiger_inventoryproductrelQuotes.productid = vtiger_service.serviceid ";
				}
			}
		}
		if ($queryPlanner->requireTable("vtiger_productsQuotes")) {
			$query .= " left join vtiger_products as vtiger_productsQuotes on vtiger_productsQuotes.productid = vtiger_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("vtiger_serviceQuotes")) {
			$query .= " left join vtiger_service as vtiger_serviceQuotes on vtiger_serviceQuotes.serviceid = vtiger_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsQuotes")) {
			$query .= " left join vtiger_groups as vtiger_groupsQuotes on vtiger_groupsQuotes.groupid = vtiger_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersQuotes")) {
			$query .= " left join vtiger_users as vtiger_usersQuotes on vtiger_usersQuotes.id = vtiger_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersRel1")) {
			$query .= " left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager";
		}
		if ($queryPlanner->requireTable("vtiger_potentialRelQuotes")) {
			$query .= " left join vtiger_potential as vtiger_potentialRelQuotes on vtiger_potentialRelQuotes.potentialid = vtiger_quotes.potentialid";
		}
		if ($queryPlanner->requireTable("vtiger_accountQuotes")) {
			$query .= " left join vtiger_account as vtiger_accountQuotes on vtiger_accountQuotes.accountid = vtiger_quotes.accountid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByQuotes")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByQuotes on vtiger_lastModifiedByQuotes.id = vtiger_crmentityQuotes.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyQuotes")) {
			$query .= " left join vtiger_users as vtiger_createdbyQuotes on vtiger_createdbyQuotes.id = vtiger_crmentityQuotes.smcreatorid ";
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
			"SalesOrder" => array("vtiger_salesorder" => array("quoteid", "salesorderid"), "vtiger_quotes" => "quoteid"),
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_quotes" => "quoteid"),
			"Accounts" => array("vtiger_quotes" => array("quoteid", "accountid")),
			"Contacts" => array("vtiger_quotes" => array("quoteid", "contactid")),
			"Potentials" => array("vtiger_quotes" => array("quoteid", "potentialid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Accounts') {
			$this->trash('Quotes', $id);
		} elseif ($return_module == 'Potentials') {
			$relation_query = 'UPDATE vtiger_quotes SET potentialid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} elseif ($return_module == 'Contacts') {
			$relation_query = 'UPDATE vtiger_quotes SET contactid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
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
	 * Returns Export Quotes Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Quotes", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_quotes ON vtiger_quotes.quoteid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_quotescf ON vtiger_quotescf.quoteid = vtiger_quotes.quoteid
				LEFT JOIN vtiger_quotesaddress ON vtiger_quotesaddress.quoteaddressid = vtiger_quotes.quoteid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_quotes.potentialid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_quotes.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_quotes.currency_id
				LEFT JOIN vtiger_users AS vtiger_inventoryManager ON vtiger_inventoryManager.id = vtiger_quotes.inventorymanager
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Quotes', $current_user);
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
