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
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

class Leads extends CRMEntity
{

	public $table_name = "vtiger_leaddetails";
	public $table_index = 'leadid';
	public $tab_name = Array('vtiger_crmentity', 'vtiger_leaddetails', 'vtiger_leadsubdetails', 'vtiger_leadaddress', 'vtiger_leadscf', 'vtiger_entity_stats');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_leaddetails' => 'leadid', 'vtiger_leadsubdetails' => 'leadsubscriptionid', 'vtiger_leadaddress' => 'leadaddressid', 'vtiger_leadscf' => 'leadid', 'vtiger_entity_stats' => 'crmid');
	public $entity_table = "vtiger_crmentity";

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_leadscf', 'leadid');
	//construct this from database;
	public $column_fields = Array();
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = Array('smcreatorid', 'smownerid', 'contactid', 'crmid');
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'Company' => Array('leaddetails' => 'company'),
		'Phone' => Array('leadaddress' => 'phone'),
		'Website' => Array('leadsubdetails' => 'website'),
		'Email' => Array('leaddetails' => 'email'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	public $list_fields_name = Array(
		'Company' => 'company',
		'Phone' => 'phone',
		'Website' => 'website',
		'Email' => 'email',
		'Assigned To' => 'assigned_user_id'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['company', 'phone', 'website', 'email', 'assigned_user_id'];
	public $list_link_field = 'company';
	public $search_fields = Array(
		'Company' => Array('leaddetails' => 'company')
	);
	public $search_fields_name = Array(
		'Company' => 'company'
	);
	public $required_fields = array();
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime');
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = array('leadsource', 'leadstatus', 'rating', 'industry', 'secondaryemail', 'email', 'annualrevenue', 'designation', 'salutation');
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// For Alphabetical search
	public $def_basicsearch_col = 'company';

	/** Function to export the lead records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Leads Query.
	 */
	public function create_export_query($where)
	{

		$current_user = vglobal('current_user');
		\App\Log::trace("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Leads", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
	      			FROM " . $this->entity_table . "
				INNER JOIN vtiger_leaddetails
					ON vtiger_crmentity.crmid=vtiger_leaddetails.leadid
				LEFT JOIN vtiger_leadsubdetails
					ON vtiger_leaddetails.leadid = vtiger_leadsubdetails.leadsubscriptionid
				LEFT JOIN vtiger_leadaddress
					ON vtiger_leaddetails.leadid=vtiger_leadaddress.leadaddressid
				LEFT JOIN vtiger_leadscf
					ON vtiger_leadscf.leadid=vtiger_leaddetails.leadid
	                        LEFT JOIN vtiger_groups
                        	        ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users
					ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'
				";

		$query .= $this->getNonAdminAccessControlQuery('Leads', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0 && vtiger_leaddetails.converted =0";

		if ($where != '')
			$query .= sprintf(' where (%s) && %s', $where, $where_auto);
		else
			$query .= sprintf(' where %s', $where_auto);

		\App\Log::trace("Exiting create_export_query method ...");
		return $query;
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

		$rel_table_arr = Array('Documents' => 'vtiger_senotesrel', 'Attachments' => 'vtiger_seattachmentsrel',
			'Products' => 'vtiger_seproductsrel', 'Campaigns' => 'vtiger_campaign_records');

		$tbl_field_arr = Array('vtiger_senotesrel' => 'notesid', 'vtiger_seattachmentsrel' => 'attachmentsid',
			'vtiger_seproductsrel' => 'productid', 'vtiger_campaign_records' => 'campaignid');

		$entity_tbl_field_arr = Array('vtiger_senotesrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid',
			'vtiger_seproductsrel' => 'crmid', 'vtiger_campaign_records' => 'crmid');

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

	public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_leaddetails', array('vtiger_crmentityLeads', 'vtiger_leadaddress', 'vtiger_leadsubdetails', 'vtiger_leadscf', 'vtiger_email_trackLeads'));
		$matrix->setDependency('vtiger_crmentityLeads', array('vtiger_groupsLeads', 'vtiger_usersLeads', 'vtiger_lastModifiedByLeads'));

		if (!$queryPlanner->requireTable("vtiger_leaddetails", $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_leaddetails", "leadid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityLeads", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityLeads on vtiger_crmentityLeads.crmid = vtiger_leaddetails.leadid and vtiger_crmentityLeads.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_leadaddress")) {
			$query .= " left join vtiger_leadaddress on vtiger_leaddetails.leadid = vtiger_leadaddress.leadaddressid";
		}
		if ($queryPlanner->requireTable("vtiger_leadsubdetails")) {
			$query .= " left join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("vtiger_leadscf")) {
			$query .= " left join vtiger_leadscf on vtiger_leadscf.leadid = vtiger_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsLeads")) {
			$query .= " left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersLeads")) {
			$query .= " left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByLeads")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByLeads on vtiger_lastModifiedByLeads.id = vtiger_crmentityLeads.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyLeads")) {
			$query .= " left join vtiger_users as vtiger_createdbyLeads on vtiger_createdbyLeads.id = vtiger_crmentityLeads.smcreatorid ";
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
			'Products' => array('vtiger_seproductsrel' => array('crmid', 'productid'), 'vtiger_leaddetails' => 'leadid'),
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_leaddetails' => 'leadid'],
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_leaddetails' => 'leadid'),
			'Services' => array('vtiger_crmentityrel' => array('crmid', 'relcrmid'), 'vtiger_leaddetails' => 'leadid'),
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

		if ($return_module === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $return_id])->execute();
		} elseif ($return_module === 'Products') {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['crmid' => $id, 'productid' => $return_id])->execute();
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids))
			$withCrmids = [$withCrmids];
		foreach ($withCrmids as $withCrmid) {
			if ($withModule === 'Products') {
				App\Db::getInstance()->createCommand()->insert('vtiger_seproductsrel', [
					'crmid' => $crmid,
					'productid' => $withCrmid,
					'setype' => $module,
					'rel_created_user' => App\User::getCurrentUserId(),
					'rel_created_time' => date('Y-m-d H:i:s')
				])->execute();
			} elseif ($withModule === 'Campaigns') {
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $withCrmid,
					'crmid' => $crmid,
					'campaignrelstatusid' => 0
				])->execute();
			} else {
				parent::save_related_module($module, $crmid, $withModule, $withCrmid, $relatedName);
			}
		}
	}

	public function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false, $additionalColumns = '')
	{
		if (is_array($tableColumns)) {
			$tableColumnsString = implode(',', $tableColumns);
		}
		if (is_array($additionalColumns)) {
			$additionalColumns = implode(',', $additionalColumns);
		}
		if (!empty($additionalColumns)) {
			$additionalColumns = ',' . $additionalColumns;
		}
		$selectClause = sprintf('SELECT %s.%s AS recordid, %s %s', $this->table_name, $this->table_index, $tableColumnsString, $additionalColumns);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$fromClause = " FROM $this->table_name";

		$fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		if ($this->tab_name) {
			foreach ($this->tab_name as $tableName) {
				if ($tableName != 'vtiger_crmentity' && $tableName != $this->table_name) {
					if ($this->tab_name_index[$tableName]) {
						$fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}
				}
			}
		}
		$fromClause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$whereClause = " WHERE vtiger_crmentity.deleted = 0 && vtiger_leaddetails.converted=0 ";
		$whereClause .= $this->getListViewSecurityParameter($module);

		if ($ignoreEmpty) {
			foreach ($tableColumns as $tableColumn) {
				$whereClause .= " && ($tableColumn IS NOT NULL && $tableColumn != '') ";
			}
		}

		if (isset($selectedColumns) && trim($selectedColumns) != '') {
			$sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $tableColumnsString $additionalColumns $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
		}

		$i = 1;
		foreach ($tableColumns as $tableColumn) {
			$tableInfo = explode('.', $tableColumn);
			$duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
			if (count($tableColumns) != $i++)
				$duplicateCheckClause .= " && ";
		}

		$query = $selectClause . $fromClause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
			$whereClause .
			" ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";
		return $query;
	}
}
