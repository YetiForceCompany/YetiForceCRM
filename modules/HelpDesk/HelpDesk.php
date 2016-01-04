<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */

class HelpDesk extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_troubletickets";
	var $table_index = 'ticketid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_ticketcomments' => 'ticketid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_ticketcf', 'ticketid');
	var $column_fields = Array();
	//Pavani: Assign value to entity_table
	var $entity_table = "vtiger_crmentity";
	var $sortby_fields = Array('title', 'status', 'priority', 'crmid', 'firstname', 'smownerid');
	var $list_fields = Array(
		//Module Sequence Numbering
		//'Ticket ID'=>Array('crmentity'=>'crmid'),
		'Ticket No' => Array('troubletickets' => 'ticket_no'),
		// END
		'Subject' => Array('troubletickets' => 'title'),
		'Related To' => Array('troubletickets' => 'parent_id'),
		'Contact Name' => Array('troubletickets' => 'contact_id'),
		'Status' => Array('troubletickets' => 'status'),
		'Priority' => Array('troubletickets' => 'priority'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Total time [h]' => Array('troubletickets', 'sum_time')
	);
	var $list_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Subject' => 'ticket_title',
		'Related To' => 'parent_id',
		'Contact Name' => 'contact_id',
		'Status' => 'ticketstatus',
		'Priority' => 'ticketpriorities',
		'Assigned To' => 'assigned_user_id',
		'Total time [h]' => 'sum_time'
	);
	var $list_link_field = 'ticket_title';
	var $range_fields = Array(
		'ticketid',
		'title',
		'firstname',
		'lastname',
		'parent_id',
		'productid',
		'productname',
		'priority',
		'severity',
		'status',
		'category',
		'description',
		'solution',
		'modifiedtime',
		'createdtime'
	);
	var $search_fields = Array(
		//'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
		'Ticket No' => Array('vtiger_troubletickets' => 'ticket_no'),
		'Title' => Array('vtiger_troubletickets' => 'title')
	);
	var $search_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Title' => 'ticket_title',
	);
	//Specify Required fields
	var $required_fields = array();
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'modifiedtime';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'ticket_title';

	//var $groupTable = Array('vtiger_ticketgrouprelation','ticketid');

	/** 	Constructor which will set the column_fields in this object
	 */
	function HelpDesk()
	{
		$this->log = LoggerManager::getLogger('helpdesk');
		$this->log->debug("Entering HelpDesk() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('HelpDesk');
		$this->log->debug("Exiting HelpDesk method ...");
	}

	function save_module($module)
	{
		//Inserting into Ticket Comment Table
		$this->insertIntoTicketCommentTable("vtiger_ticketcomments", $module);

		//Inserting into vtiger_attachments
		$this->insertIntoAttachment($this->id, $module);

		//service contract update
		$return_action = $_REQUEST['return_action'];
		$for_module = $_REQUEST['return_module'];
		$for_crmid = $_REQUEST['return_id'];
		if ($return_action && $for_module && $for_crmid) {
			if ($for_module == 'ServiceContracts') {
				$on_focus = CRMEntity::getInstance($for_module);
				$on_focus->save_related_module($for_module, $for_crmid, $module, $this->id);
			}
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmid)
	{
		parent::save_related_module($module, $crmid, $with_module, $with_crmid);
		if ($with_module == 'ServiceContracts') {
			$serviceContract = CRMEntity::getInstance("ServiceContracts");
			$serviceContract->updateHelpDeskRelatedTo($with_crmid, $crmid);
			$serviceContract->updateServiceContractState($with_crmid);
		}
	}

	/** Function to insert values in vtiger_ticketcomments  for the specified tablename and  module
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 */
	function insertIntoTicketCommentTable($table_name, $module)
	{
		$log = vglobal('log');
		$log->info("in insertIntoTicketCommentTable  " . $table_name . "    module is  " . $module);
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');

		$current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);
		if ($this->column_fields['from_portal'] != 1) {
			$ownertype = 'user';
			$ownerId = $current_user->id;
		} else {
			$ownertype = 'customer';
			$ownerId = $this->column_fields['parent_id'];
		}

		$comment = $this->column_fields['comments'];
		if ($comment != '') {
			$sql = "insert into vtiger_ticketcomments values(?,?,?,?,?,?)";
			$params = array('', $this->id, from_html($comment), $ownerId, $ownertype, $current_time);
			$adb->pquery($sql, $params);
		}
	}

	/**
	 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	 */
	function insertIntoAttachment($id, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;

		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] != '' && $files['size'] > 0) {
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex . '_hidden']);
				$file_saved = $this->uploadAndSaveFile($id, $module, $files);
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}

	/**     Function to get the Ticket History information as in array format
	 * 	@param int $ticketid - ticket id
	 * 	@return array - return an array with title and the ticket history informations in the following format
	  array(
	  header=>array('0'=>'title'),
	  entries=>array('0'=>'info1','1'=>'info2',etc.,)
	  )
	 */
	function get_ticket_history($ticketid)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering into get_ticket_history($ticketid) method ...");

		$query = "select title,update_log from vtiger_troubletickets where ticketid=?";
		$result = $adb->pquery($query, array($ticketid));
		$update_log = $adb->query_result($result, 0, "update_log");

		$splitval = split('--//--', trim($update_log, '--//--'));

		$header[] = $adb->query_result($result, 0, "title");

		$return_value = Array('header' => $header, 'entries' => $splitval);

		$log->debug("Exiting from get_ticket_history($ticketid) method ...");

		return $return_value;
	}

	/** 	Function to get the HelpDesk field labels in caps letters without space
	 * 	@return array $mergeflds - array(	key => val	)    where   key=0,1,2..n & val = ASSIGNEDTO,RELATEDTO, .,etc
	 * */
	function getColumnNames_Hd()
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering getColumnNames_Hd() method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select fieldlabel from vtiger_field where tabid=13 and block <> 30 and vtiger_field.uitype <> '61' and vtiger_field.presence in (0,2)";
			$params1 = array();
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select vtiger_field.fieldid,fieldlabel from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=13 and vtiger_field.block <> 30 and vtiger_field.uitype <> '61' and vtiger_field.displaytype in (1,2,3,4) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")  group by fieldid";
				array_push($params1, $profileList);
			}
		}
		$result = $this->db->pquery($sql1, $params1);
		$numRows = $this->db->num_rows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$custom_fields[$i] = $this->db->query_result($result, $i, "fieldlabel");
			$custom_fields[$i] = preg_replace("/\s+/", "", $custom_fields[$i]);
			$custom_fields[$i] = strtoupper($custom_fields[$i]);
		}
		$mergeflds = $custom_fields;
		$log->debug("Exiting getColumnNames_Hd method ...");
		return $mergeflds;
	}

	/**     Function to get the Customer Name who has made comment to the ticket from the customer portal
	 *      @param  int    $id   - Ticket id
	 *      @return string $customername - The contact name
	 * */
	function getCustomerName($id)
	{
		$log = vglobal('log');
		$log->debug("Entering getCustomerName(" . $id . ") method ...");
		$adb = PearDatabase::getInstance();
		$sql = "select * from vtiger_portalinfo inner join vtiger_troubletickets on vtiger_troubletickets.contact_id = vtiger_portalinfo.id where vtiger_troubletickets.ticketid=?";
		$result = $adb->pquery($sql, array($id));
		$customername = $adb->query_result($result, 0, 'user_name');
		$log->debug("Exiting getCustomerName method ...");
		return $customername;
	}

	// Function to create, export query for helpdesk module
	/** Function to export the ticket records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Tickets Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("HelpDesk", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		//Ticket changes--5198
		$fields_list = str_replace(",vtiger_ticketcomments.comments as 'Add Comment'", ' ', $fields_list);


		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
                       FROM " . $this->entity_table . "
				INNER JOIN vtiger_troubletickets
					ON vtiger_troubletickets.ticketid =vtiger_crmentity.crmid
				LEFT JOIN vtiger_account
					ON vtiger_account.accountid = vtiger_troubletickets.parent_id
				LEFT JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_troubletickets.contact_id
				LEFT JOIN vtiger_ticketcf
					ON vtiger_ticketcf.ticketid=vtiger_troubletickets.ticketid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid and vtiger_users.status='Active'
				LEFT JOIN vtiger_products
					ON vtiger_products.productid=vtiger_troubletickets.product_id";
		//end
		$query .= getNonAdminAccessControlQuery('HelpDesk', $current_user);
		$where_auto = " vtiger_crmentity.deleted = 0 ";

		if ($where != "")
			$query .= "  WHERE ($where) AND " . $where_auto;
		else
			$query .= "  WHERE " . $where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the update ticket history for the specified ticketid
	 * @param $id -- $ticketid:: Type Integer
	 */
	function constructUpdateLog($focus, $mode, $assigned_group_name, $assigntype)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');

		if ($mode != 'edit') {//this will be updated when we create new ticket
			$updatelog = "Ticket created. Assigned to ";

			if (!empty($assigned_group_name) && $assigntype == 'T') {
				$updatelog .= " group " . (is_array($assigned_group_name) ? $assigned_group_name[0] : $assigned_group_name);
			} elseif ($focus->column_fields['assigned_user_id'] != '') {
				$updatelog .= " user " . getUserFullName($focus->column_fields['assigned_user_id']);
			} else {
				$updatelog .= " user " . getUserFullName($current_user->id);
			}

			$fldvalue = date("l dS F Y h:i:s A") . ' by ' . $current_user->user_name;
			$updatelog .= " -- " . $fldvalue . "--//--";
		} else {
			$ticketid = $focus->id;

			//First retrieve the existing information
			$tktresult = $adb->pquery("select * from vtiger_troubletickets where ticketid=?", array($ticketid));
			$crmresult = $adb->pquery("select * from vtiger_crmentity where crmid=?", array($ticketid));

			$updatelog = decode_html($adb->query_result($tktresult, 0, "update_log"));

			$old_owner_id = $adb->query_result($crmresult, 0, "smownerid");
			$old_status = $adb->query_result($tktresult, 0, "status");
			$old_priority = $adb->query_result($tktresult, 0, "priority");
			$old_severity = $adb->query_result($tktresult, 0, "severity");
			$old_category = $adb->query_result($tktresult, 0, "category");

			//Assigned to change log
			if ($focus->column_fields['assigned_user_id'] != $old_owner_id) {
				$owner_name = getOwnerName($focus->column_fields['assigned_user_id']);
				if ($assigntype == 'T')
					$updatelog .= ' Transferred to group ' . $owner_name . '\.';
				else
					$updatelog .= ' Transferred to user ' . decode_html($owner_name) . '\.'; // Need to decode UTF characters which are migrated from versions < 5.0.4.
			}
			//Status change log
			if ($old_status != $focus->column_fields['ticketstatus'] && $focus->column_fields['ticketstatus'] != '') {
				$updatelog .= ' Status Changed to ' . $focus->column_fields['ticketstatus'] . '\.';
			}
			//Priority change log
			if ($old_priority != $focus->column_fields['ticketpriorities'] && $focus->column_fields['ticketpriorities'] != '') {
				$updatelog .= ' Priority Changed to ' . $focus->column_fields['ticketpriorities'] . '\.';
			}
			//Severity change log
			if ($old_severity != $focus->column_fields['ticketseverities'] && $focus->column_fields['ticketseverities'] != '') {
				$updatelog .= ' Severity Changed to ' . $focus->column_fields['ticketseverities'] . '\.';
			}
			//Category change log
			if ($old_category != $focus->column_fields['ticketcategories'] && $focus->column_fields['ticketcategories'] != '') {
				$updatelog .= ' Category Changed to ' . $focus->column_fields['ticketcategories'] . '\.';
			}

			$updatelog .= ' -- ' . date("l dS F Y h:i:s A") . ' by ' . $current_user->user_name . '--//--';
		}
		return $updatelog;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Attachments" => "vtiger_seattachmentsrel", "Documents" => "vtiger_senotesrel");

		$tbl_field_arr = Array("vtiger_seattachmentsrel" => "attachmentsid", "vtiger_senotesrel" => "notesid");

		$entity_tbl_field_arr = Array("vtiger_seattachmentsrel" => "crmid", "vtiger_senotesrel" => "crmid");

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", array($transferId, $entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->query_result($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", array($entityId, $transferId, $id_field_value));
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		$log->debug("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityHelpDesk", array("vtiger_groupsHelpDesk", "vtiger_usersHelpDesk", "vtiger_lastModifiedByHelpDesk"));
		$matrix->setDependency("vtiger_troubletickets", array("vtiger_crmentityHelpDesk", "vtiger_ticketcf", "vtiger_crmentityRelHelpDesk", "vtiger_productsRel"));
		$matrix->setDependency("vtiger_crmentityRelHelpDesk", array("vtiger_accountRelHelpDesk", "vtiger_contactdetailsRelHelpDesk"));


		if (!$queryplanner->requireTable('vtiger_troubletickets', $matrix)) {
			return '';
		}
		// TODO Support query planner
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_troubletickets", "ticketid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityHelpDesk", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityHelpDesk on vtiger_crmentityHelpDesk.crmid=vtiger_troubletickets.ticketid and vtiger_crmentityHelpDesk.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_ticketcf")) {
			$query .=" left join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid";
		}
		if ($queryplanner->requireTable("vtiger_crmentityRelHelpDesk", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id";
		}
		if ($queryplanner->requireTable("vtiger_accountRelHelpDesk")) {
			$query .=" left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid";
		}
		if ($queryplanner->requireTable("vtiger_contactdetailsRelHelpDesk")) {
			$query .=" left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_troubletickets.contact_id";
		}
		if ($queryplanner->requireTable("vtiger_productsRel")) {
			$query .=" left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id";
		}
		if ($queryplanner->requireTable("vtiger_groupsHelpDesk")) {
			$query .=" left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentityHelpDesk.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersHelpDesk")) {
			$query .=" left join vtiger_users as vtiger_usersHelpDesk on vtiger_usersHelpDesk.id = vtiger_crmentityHelpDesk.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByHelpDesk")) {
			$query .=" left join vtiger_users as vtiger_lastModifiedByHelpDesk on vtiger_lastModifiedByHelpDesk.id = vtiger_crmentityHelpDesk.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyHelpDesk")) {
			$query .= " left join vtiger_users as vtiger_createdbyHelpDesk on vtiger_createdbyHelpDesk.id = vtiger_crmentityHelpDesk.smcreatorid ";
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
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_troubletickets" => "ticketid"),
			"Products" => array("vtiger_troubletickets" => array("ticketid", "product_id")),
			"Services" => array("vtiger_crmentityrel" => array("crmid", "relcrmid"), "vtiger_troubletickets" => "ticketid"),
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
			$sql = 'UPDATE vtiger_troubletickets SET parent_id=? WHERE ticketid=?';
			$this->db->pquery($sql, array(null, $id));
			$se_sql = 'DELETE FROM vtiger_seticketsrel WHERE ticketid=?';
			$this->db->pquery($se_sql, array($id));
		} elseif ($return_module == 'Contacts') {
			$sql = 'UPDATE vtiger_troubletickets SET contact_id=? WHERE ticketid=?';
			$this->db->pquery($sql, array(null, $id));
			$se_sql = 'DELETE FROM vtiger_seticketsrel WHERE ticketid=?';
			$this->db->pquery($se_sql, array($id));
		} elseif ($return_module == 'Products') {
			$sql = 'UPDATE vtiger_troubletickets SET product_id=? WHERE ticketid=?';
			$this->db->pquery($sql, array(null, $id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

	public static function getTicketEmailContents($entityData, $toOwner = false)
	{
		global $HELPDESK_SUPPORT_NAME;
		$adb = PearDatabase::getInstance();
		$moduleName = $entityData->getModuleName();
		$wsId = $entityData->getId();

		if (strpos($wsId, 'x')) {
			$parts = explode('x', $wsId);
			$entityId = $parts[1];
		} else {
			$entityId = $wsId;
		}

		$isNew = $entityData->isNew();

		if (!$isNew) {
			$reply = getTranslatedString("replied", $moduleName);
			$temp = getTranslatedString("Re", $moduleName);
		} else {
			$reply = getTranslatedString("created", $moduleName);
			$temp = " ";
		}


		$wsParentId = $entityData->get('contact_id');
		$parentIdParts = explode('x', $wsParentId);

		// If this function is being triggered as part of Eventing API
		// Then the reference field ID will not matching the webservice format.
		// Regardless of the entry we need just the ID
		$parentId = array_pop($parentIdParts);

		$desc = getTranslatedString('Ticket ID', $moduleName) . ' : ' . $entityId . '<br>'
			. getTranslatedString('Ticket Title', $moduleName) . ' : ' . $temp . ' '
			. $entityData->get('ticket_title');
		$name = (!$toOwner) ? getParentName($parentId) : '';
		$desc .= "<br><br>" . getTranslatedString('Hi', $moduleName) . " " . $name . ",<br><br>"
			. getTranslatedString('LBL_PORTAL_BODY_MAILINFO', $moduleName) . " " . $reply . " " . getTranslatedString('LBL_DETAIL', $moduleName) . "<br>";
		$desc .= "<br>" . getTranslatedString('Ticket No', $moduleName) . " : " . $entityData->get('ticket_no');
		$desc .= "<br>" . getTranslatedString('Status', $moduleName) . " : " . $entityData->get('ticketstatus');
		$desc .= "<br>" . getTranslatedString('Category', $moduleName) . " : " . $entityData->get('ticketcategories');
		$desc .= "<br>" . getTranslatedString('Severity', $moduleName) . " : " . $entityData->get('ticketseverities');
		$desc .= "<br>" . getTranslatedString('Priority', $moduleName) . " : " . $entityData->get('ticketpriorities');
		$desc .= "<br><br>" . getTranslatedString('Description', $moduleName) . " : <br>" . $entityData->get('description');
		$desc .= "<br><br>" . getTranslatedString('Solution', $moduleName) . " : <br>" . $entityData->get('solution');
		$desc .= getTicketComments($entityId);

		$sql = "SELECT * FROM vtiger_ticketcf WHERE ticketid = ?";
		$result = $adb->pquery($sql, array($entityId));
		$cffields = $adb->getFieldsArray($result);
		foreach ($cffields as $cfOneField) {
			if ($cfOneField != 'ticketid' && $cfOneField != 'from_portal') {
				$cfData = $adb->query_result($result, 0, $cfOneField);
				$sql = "SELECT fieldlabel FROM vtiger_field WHERE columnname = ? and vtiger_field.presence in (0,2)";
				$cfLabel = $adb->query_result($adb->pquery($sql, array($cfOneField)), 0, 'fieldlabel');
				$desc .= '<br>' . $cfLabel . ' : ' . $cfData;
			}
		}
		$desc .= '<br><br>' . getTranslatedString("LBL_REGARDS", $moduleName) . ',<br>' . $HELPDESK_SUPPORT_NAME;
		return $desc;
	}

	public static function getPortalTicketEmailContents($entityData)
	{
		require_once 'config/config.php';
		global $PORTAL_URL, $HELPDESK_SUPPORT_NAME;

		$moduleName = $entityData->getModuleName();
		$wsId = $entityData->getId();

		if (strpos($wsId, 'x')) {
			$parts = explode('x', $wsId);
			$entityId = $parts[1];
		} else {
			$entityId = $wsId;
		}
		$wsParentId = $entityData->get('contact_id');
		$parentIdParts = explode('x', $wsParentId);

		// If this function is being triggered as part of Eventing API
		// Then the reference field ID will not matching the webservice format.
		// Regardless of the entry we need just the ID
		$parentId = array_pop($parentIdParts);

		$portalUrl = "<a href='" . $PORTAL_URL . "/index.php?module=HelpDesk&action=index&ticketid=" . $entityId . "&fun=detail'>"
			. getTranslatedString('LBL_TICKET_DETAILS', $moduleName) . "</a>";
		$contents = getTranslatedString('Dear', $moduleName) . ' ';
		$contents .= ($parentId) ? getParentName($parentId) : '';
		$contents .= ",<br>";
		$contents .= getTranslatedString('reply', $moduleName) . ' <b>' . $entityData->get('ticket_title')
			. '</b> ' . getTranslatedString('customer_portal', $moduleName);
		$contents .= getTranslatedString("link", $moduleName) . '<br>';
		$contents .= $portalUrl;
		$contents .= '<br><br>' . getTranslatedString("Thanks", $moduleName) . '<br>' . $HELPDESK_SUPPORT_NAME;
		return $contents;
	}

	function clearSingletonSaveFields()
	{
		$this->column_fields['comments'] = '';
	}
	/* {[The function is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
	/* {[Contributor(s):							}] */

	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_emails(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_ossmailview.*, vtiger_crmentity.modifiedtime, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name FROM vtiger_ossmailview 
			INNER JOIN vtiger_ossmailview_relation ON vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid 
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_ossmailview_relation.crmid = " . $id . " ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}
}
