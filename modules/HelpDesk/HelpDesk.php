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

	public $table_name = "vtiger_troubletickets";
	public $table_index = 'ticketid';
	public $tab_name = Array('vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf', 'vtiger_entity_stats');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_ticketcf', 'ticketid');
	public $column_fields = Array();
	//Pavani: Assign value to entity_table
	public $entity_table = "vtiger_crmentity";
	public $list_fields = Array(
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
	public $list_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Subject' => 'ticket_title',
		'Related To' => 'parent_id',
		'Contact Name' => 'contact_id',
		'Status' => 'ticketstatus',
		'Priority' => 'ticketpriorities',
		'Assigned To' => 'assigned_user_id',
		'FL_TOTAL_TIME_H' => 'sum_time'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['ticket_no', 'ticket_title', 'parent_id', 'ticketstatus', 'ticketpriorities', 'assigned_user_id', 'sum_time'];
	public $list_link_field = 'ticket_title';
	public $range_fields = Array(
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
	public $search_fields = Array(
		//'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
		'Ticket No' => Array('vtiger_troubletickets' => 'ticket_no'),
		'Title' => Array('vtiger_troubletickets' => 'title')
	);
	public $search_fields_name = Array(
		'Ticket No' => 'ticket_no',
		'Title' => 'ticket_title',
	);
	//Specify Required fields
	public $required_fields = array();
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log');
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'ticket_title';

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

	// Function to create, export query for helpdesk module
	/** Function to export the ticket records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Tickets Query.
	 */
	public function create_export_query($where)
	{

		$current_user = vglobal('current_user');
		\App\Log::trace("Entering create_export_query(" . $where . ") method ...");

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

		\App\Log::trace("Exiting create_export_query method ...");
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
				$updatelog .= " user " . \App\Fields\Owner::getUserLabel($focus->column_fields['assigned_user_id']);
			} else {
				$updatelog .= " user " . \App\Fields\Owner::getUserLabel($currentUser->getId());
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
				$ownerName = \App\Fields\Owner::getLabel($focus->column_fields['assigned_user_id']);
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

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

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
		\App\Log::trace("Exiting transferRelatedRecords...");
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
			$query .= " left join vtiger_crmentity as vtiger_crmentityHelpDesk on vtiger_crmentityHelpDesk.crmid=vtiger_troubletickets.ticketid and vtiger_crmentityHelpDesk.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_ticketcf")) {
			$query .= " left join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid";
		}
		if ($queryplanner->requireTable("vtiger_crmentityRelHelpDesk", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id";
		}
		if ($queryplanner->requireTable("vtiger_accountRelHelpDesk")) {
			$query .= " left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid";
		}
		if ($queryplanner->requireTable("vtiger_contactdetailsRelHelpDesk")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_troubletickets.contact_id";
		}
		if ($queryplanner->requireTable("vtiger_productsRel")) {
			$query .= " left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id";
		}
		if ($queryplanner->requireTable("vtiger_groupsHelpDesk")) {
			$query .= " left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentityHelpDesk.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersHelpDesk")) {
			$query .= " left join vtiger_users as vtiger_usersHelpDesk on vtiger_usersHelpDesk.id = vtiger_crmentityHelpDesk.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByHelpDesk")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByHelpDesk on vtiger_lastModifiedByHelpDesk.id = vtiger_crmentityHelpDesk.modifiedby ";
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
		} elseif ($return_module == 'ServiceContracts' && $relatedName != 'getManyToMany') {
			parent::unlinkRelationship($id, $return_module, $return_id);
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function clearSingletonSaveFields()
	{
		$this->column_fields['comments'] = '';
	}
}
