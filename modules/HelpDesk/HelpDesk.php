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
	var $tab_name = Array('vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf', 'vtiger_entity_stats');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_entity_stats' => 'crmid');

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
		'FL_TOTAL_TIME_H' => Array('troubletickets', 'sum_time')
	);
	var $list_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Subject' => 'ticket_title',
		'Related To' => 'parent_id',
		'Contact Name' => 'contact_id',
		'Status' => 'ticketstatus',
		'Priority' => 'ticketpriorities',
		'Assigned To' => 'assigned_user_id',
		'FL_TOTAL_TIME_H' => 'sum_time'
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
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'ticket_title';

	public function save_module($module)
	{
		//Inserting into vtiger_attachments
		$this->insertIntoAttachment($this->id, $module);

		//service contract update
		$return_action = AppRequest::get('return_action');
		$for_module = AppRequest::get('return_module');
		$for_crmid = AppRequest::get('return_id');
		if ($return_action && $for_module && $for_crmid) {
			if ($for_module == 'ServiceContracts') {
				$on_focus = CRMEntity::getInstance($for_module);
				$on_focus->save_related_module($for_module, $for_crmid, $module, $this->id);
			}
		}
	}

	public function save_related_module($module, $crmid, $with_module, $with_crmid, $relatedName = false)
	{
		if ($with_module == 'ServiceContracts') {
			parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			$serviceContract = CRMEntity::getInstance("ServiceContracts");
			$serviceContract->updateHelpDeskRelatedTo($with_crmid, $crmid);
			$serviceContract->updateServiceContractState($with_crmid);
		} else {
			parent::save_related_module($module, $crmid, $with_module, $with_crmid, $relatedName);
		}
	}

	/**
	 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	 */
	public function insertIntoAttachment($id, $module)
	{
		$log = LoggerManager::getInstance();
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;

		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] != '' && $files['size'] > 0) {
				$files['original_name'] = AppRequest::get($fileindex . '_hidden');
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
	public function get_ticket_history($ticketid)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering into get_ticket_history($ticketid) method ...");

		$query = 'select title,update_log from vtiger_troubletickets where ticketid=?';
		$result = $adb->pquery($query, array($ticketid));
		$row = $adb->getRow($result);
		$updateLog = $row['update_log'];
		$header[] = $row['title'];
		$splitval = explode('--//--', trim($updateLog, '--//--'));

		$return_value = ['header' => $header, 'entries' => $splitval];

		$log->debug("Exiting from get_ticket_history($ticketid) method ...");
		return $return_value;
	}

	/** 	public function to get the HelpDesk field labels in caps letters without space
	 * 	@return array $mergeflds - array(	key => val	)    where   key=0,1,2..n & val = ASSIGNEDTO,RELATEDTO, .,etc
	 * */
	public function getColumnNames_Hd()
	{
		$log = LoggerManager::getInstance();
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
	public function getCustomerName($id)
	{
		$log = LoggerManager::getInstance();
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
	public function create_export_query($where)
	{
		$log = LoggerManager::getInstance();
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("HelpDesk", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
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

		if ($where != '')
			$query .= sprintf(' where (%s) && %s', $where, $where_auto);
		else
			$query .= sprintf(' where %s', $where_auto);

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the update ticket history for the specified ticketid
	 * @param $id -- $ticketid:: Type Integer
	 */
	public function constructUpdateLog($focus, $mode, $assigned_group_name, $assigntype)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserModel();

		if ($mode != 'edit') {//this will be updated when we create new ticket
			$updatelog = "Ticket created. Assigned to ";

			if (!empty($assigned_group_name) && $assigntype == 'T') {
				$updatelog .= " group " . (is_array($assigned_group_name) ? $assigned_group_name[0] : $assigned_group_name);
			} elseif ($focus->column_fields['assigned_user_id'] != '') {
				$updatelog .= " user " . \includes\fields\Owner::getUserLabel($focus->column_fields['assigned_user_id']);
			} else {
				$updatelog .= " user " . \includes\fields\Owner::getUserLabel($currentUser->getId());
			}

			$fldvalue = date("l dS F Y h:i:s A") . ' by ' . $currentUser->getName();
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
				$ownerName = \includes\fields\Owner::getLabel($focus->column_fields['assigned_user_id']);
				if ($assigntype == 'T')
					$updatelog .= ' Transferred to group ' . $ownerName . '\.';
				else
					$updatelog .= ' Transferred to user ' . decode_html($ownerName) . '\.'; // Need to decode UTF characters which are migrated from versions < 5.0.4.
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

			$updatelog .= ' -- ' . date("l dS F Y h:i:s A") . ' by ' . $currentUser->getName() . '--//--';
		}
		return $updatelog;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
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

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityHelpDesk", array("vtiger_groupsHelpDesk", "vtiger_usersHelpDesk", "vtiger_lastModifiedByHelpDesk"));
		$matrix->setDependency("vtiger_troubletickets", array("vtiger_crmentityHelpDesk", "vtiger_ticketcf", "vtiger_crmentityRelHelpDesk", "vtiger_productsRel"));
		$matrix->setDependency("vtiger_crmentityRelHelpDesk", array("vtiger_accountRelHelpDesk", "vtiger_contactdetailsRelHelpDesk"));


		if (!$queryplanner->requireTable('vtiger_troubletickets', $matrix)) {
			return '';
		}
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

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_troubletickets' => 'ticketid'),
			'Services' => array('vtiger_crmentityrel' => array('crmid', 'relcrmid'), 'vtiger_troubletickets' => 'ticketid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		$log = LoggerManager::getInstance();
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
		} elseif ($return_module == 'ServiceContracts' && $relatedName != 'get_many_to_many') {
			parent::unlinkRelationship($id, $return_module, $return_id);
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public static function getTicketEmailContents($entityData, $toOwner = false)
	{
		$HELPDESK_SUPPORT_NAME = AppConfig::main('HELPDESK_SUPPORT_NAME');
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
			$reply = \includes\Language::translate("replied", $moduleName);
			$temp = \includes\Language::translate("Re", $moduleName);
		} else {
			$reply = \includes\Language::translate("created", $moduleName);
			$temp = " ";
		}


		$wsParentId = $entityData->get('contact_id');
		$parentIdParts = explode('x', $wsParentId);

		// If this function is being triggered as part of Eventing API
		// Then the reference field ID will not matching the webservice format.
		// Regardless of the entry we need just the ID
		$parentId = array_pop($parentIdParts);

		$desc = \includes\Language::translate('Ticket ID', $moduleName) . ' : ' . $entityId . '<br>'
			. \includes\Language::translate('Ticket Title', $moduleName) . ' : ' . $temp . ' '
			. $entityData->get('ticket_title');
		$name = (!$toOwner) ? \vtlib\Functions::getCRMRecordLabel($parentId) : '';
		$desc .= "<br><br>" . \includes\Language::translate('Hi', $moduleName) . " " . $name . ",<br><br>"
			. \includes\Language::translate('LBL_PORTAL_BODY_MAILINFO', $moduleName) . " " . $reply . " " . \includes\Language::translate('LBL_DETAIL', $moduleName) . "<br>";
		$desc .= "<br>" . \includes\Language::translate('Ticket No', $moduleName) . " : " . $entityData->get('ticket_no');
		$desc .= "<br>" . \includes\Language::translate('Status', $moduleName) . " : " . $entityData->get('ticketstatus');
		$desc .= "<br>" . \includes\Language::translate('Category', $moduleName) . " : " . $entityData->get('ticketcategories');
		$desc .= "<br>" . \includes\Language::translate('Severity', $moduleName) . " : " . $entityData->get('ticketseverities');
		$desc .= "<br>" . \includes\Language::translate('Priority', $moduleName) . " : " . $entityData->get('ticketpriorities');
		$desc .= "<br><br>" . \includes\Language::translate('Description', $moduleName) . " : <br>" . $entityData->get('description');
		$desc .= "<br><br>" . \includes\Language::translate('Solution', $moduleName) . " : <br>" . $entityData->get('solution');
		$desc .= \vtlib\Functions::getTicketComments($entityId);

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
		$desc .= '<br><br>' . \includes\Language::translate("LBL_REGARDS", $moduleName) . ',<br>' . $HELPDESK_SUPPORT_NAME;
		return $desc;
	}

	public static function getPortalTicketEmailContents($entityData)
	{
		require_once 'config/config.php';
		$PORTAL_URL = AppConfig::main('PORTAL_URL');
		$HELPDESK_SUPPORT_NAME = AppConfig::main('HELPDESK_SUPPORT_NAME');
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
			. \includes\Language::translate('LBL_TICKET_DETAILS', $moduleName) . "</a>";
		$contents = \includes\Language::translate('Dear', $moduleName) . ' ';
		$contents .= ($parentId) ? \vtlib\Functions::getCRMRecordLabel($parentId) : '';
		$contents .= ",<br>";
		$contents .= \includes\Language::translate('reply', $moduleName) . ' <b>' . $entityData->get('ticket_title')
			. '</b> ' . \includes\Language::translate('customer_portal', $moduleName);
		$contents .= \includes\Language::translate("link", $moduleName) . '<br>';
		$contents .= $portalUrl;
		$contents .= '<br><br>' . \includes\Language::translate("Thanks", $moduleName) . '<br>' . $HELPDESK_SUPPORT_NAME;
		return $contents;
	}

	public function clearSingletonSaveFields()
	{
		$this->column_fields['comments'] = '';
	}
}
