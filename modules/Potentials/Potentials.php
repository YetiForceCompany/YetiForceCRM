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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Potentials/Potentials.php,v 1.65 2005/04/28 08:08:27 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

class Potentials extends CRMEntity
{

	var $log;
	var $db;
	var $module_name = "Potentials";
	var $table_name = "vtiger_potential";
	var $table_index = 'potentialid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_potential', 'vtiger_potentialscf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_potential' => 'potentialid', 'vtiger_potentialscf' => 'potentialid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_potentialscf', 'potentialid');
	var $column_fields = Array();
	var $sortby_fields = Array('potentialname', 'closingdate', 'smownerid', 'accountname');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Potential' => Array('potential' => 'potentialname'),
		'Organization Name' => Array('potential' => 'related_to'),
		'Sales Stage' => Array('potential' => 'sales_stage'),
		'Expected Close Date' => Array('potential' => 'closingdate'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		'Potential' => 'potentialname',
		'Organization Name' => 'related_to',
		'Sales Stage' => 'sales_stage',
		'Expected Close Date' => 'closingdate',
		'Assigned To' => 'assigned_user_id');
	var $list_link_field = 'potentialname';
	var $search_fields = Array(
		'Potential' => Array('potential' => 'potentialname'),
		'Related To' => Array('potential' => 'related_to'),
		'Expected Close Date' => Array('potential' => 'closedate')
	);
	var $search_fields_name = Array(
		'Potential' => 'potentialname',
		'Related To' => 'related_to',
		'Expected Close Date' => 'closingdate'
	);
	var $required_fields = array();
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'potentialname');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'potentialname';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'potentialname';

	//var $groupTable = Array('vtiger_potentialgrouprelation','potentialid');
	function Potentials()
	{
		$this->log = LoggerManager::getLogger('potential');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Potentials');
	}

	function save_module($module)
	{
		
	}

	/** Function to create list query
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Query.
	 */
	function create_list_query($order_by, $where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
		$tab_id = getTabid("Potentials");
		$log->debug("Entering create_list_query(" . $order_by . "," . $where . ") method ...");
		// Determine if the vtiger_account name is present in the where clause.
		$account_required = preg_match("/accounts\.name/", $where);

		if ($account_required) {
			$query = "SELECT vtiger_potential.potentialid,  vtiger_potential.potentialname, vtiger_potential.dateclosed FROM vtiger_potential, vtiger_account ";
			$where_auto = "account.accountid = vtiger_potential.related_to AND vtiger_crmentity.deleted=0 ";
		} else {
			$query = 'SELECT vtiger_potential.potentialid, vtiger_potential.potentialname, vtiger_crmentity.smcreatorid, vtiger_potential.closingdate FROM vtiger_potential inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid LEFT JOIN vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid ';
			$where_auto = ' AND vtiger_crmentity.deleted=0';
		}

		$query .= $this->getNonAdminAccessControlQuery('Potentials', $current_user);
		if ($where != "")
			$query .= " where $where " . $where_auto;
		else
			$query .= " where " . $where_auto;
		if ($order_by != "")
			$query .= " ORDER BY $order_by";

		$log->debug("Exiting create_list_query method ...");
		return $query;
	}

	/** Function to export the Opportunities records in CSV Format
	 * @param reference variable - order by is passed when the query is executed
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Potentials Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Potentials", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
				FROM vtiger_potential
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_account on vtiger_potential.related_to=vtiger_account.accountid
				LEFT JOIN vtiger_potentialscf on vtiger_potentialscf.potentialid=vtiger_potential.potentialid
                LEFT JOIN vtiger_groups
        	        ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_campaign
					ON vtiger_campaign.campaignid = vtiger_potential.campaignid";

		$query .= $this->getNonAdminAccessControlQuery('Potentials', $current_user);
		$where_auto = "  vtiger_crmentity.deleted = 0 ";

		if ($where != "")
			$query .= "  WHERE ($where) AND " . $where_auto;
		else
			$query .= "  WHERE " . $where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
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

		$accountid = $this->column_fields['related_to'];
		$search_string = "&fromPotential=true&acc_id=$accountid";

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id$search_string','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = 'select case when (vtiger_users.user_name not like "") then ' . $userNameSql . ' else vtiger_groups.groupname end as user_name,
					vtiger_contactdetails.parentid,vtiger_potential.potentialid, vtiger_potential.potentialname, vtiger_contactdetails.contactid,
					vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.title, vtiger_contactdetails.department,
					vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
					vtiger_crmentity.modifiedtime , vtiger_account.accountname from vtiger_potential
					left join vtiger_contpotentialrel on vtiger_contpotentialrel.potentialid = vtiger_potential.potentialid
					inner join vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_contpotentialrel.contactid
					INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
					INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
					INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
					INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
					inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid
					left join vtiger_account on vtiger_account.accountid = vtiger_contactdetails.parentid
					left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
					left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id
					where vtiger_potential.potentialid = ' . $id . ' and vtiger_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Contact related Products
	 * @param  integer   $id  - contactid
	 * returns related Products record in array format
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_products(" . $id . ") method ...");
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

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		$query = "SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode,
				vtiger_products.commissionrate, vtiger_products.qty_per_unit, vtiger_products.unit_price,
				vtiger_crmentity.crmid, vtiger_crmentity.smownerid
				FROM vtiger_products
				INNER JOIN vtiger_seproductsrel ON vtiger_products.productid = vtiger_seproductsrel.productid and vtiger_seproductsrel.setype = 'Potentials'
				INNER JOIN vtiger_productcf
				ON vtiger_products.productid = vtiger_productcf.productid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_seproductsrel.crmid
				LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0 AND vtiger_potential.potentialid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** 	Function used to get the Sales Stage history of the Potential
	 * 	@param $id - potentialid
	 * 	return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are array which contains all the column values of an row
	 */
	function get_stage_history($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_stage_history(" . $id . ") method ...");

		$adb = PearDatabase::getInstance();
		global $mod_strings;
		global $app_strings;

		$query = 'select vtiger_potstagehistory.*, vtiger_potential.potentialname from vtiger_potstagehistory inner join vtiger_potential on vtiger_potential.potentialid = vtiger_potstagehistory.potentialid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_potential.potentialid where vtiger_crmentity.deleted = 0 and vtiger_potential.potentialid = ?';
		$result = $adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SALES_STAGE'];
		$header[] = $app_strings['LBL_PROBABILITY'];
		$header[] = $app_strings['LBL_CLOSE_DATE'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Sales Stage, Expected Close Dates are mandatory fields. So no need to do security check to these fields.
		$current_user = vglobal('current_user');

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$probability_access = (getFieldVisibilityPermission('Potentials', $current_user->id, 'probability') != '0') ? 1 : 0;
		$picklistarray = getAccessPickListValues('Potentials');

		$potential_stage_array = $picklistarray['sales_stage'];
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = 'Not Accessible';

		while ($row = $adb->fetch_array($result)) {
			$entries = Array();

			$entries[] = (in_array($row['stage'], $potential_stage_array)) ? $row['stage'] : $error_msg;
			$entries[] = ($probability_access != 1) ? $row['probability'] : 0;
			$entries[] = DateTimeField::convertToUserFormat($row['closedate']);
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDate();

			$entries_list[] = $entries;
		}

		$return_data = Array('header' => $header, 'entries' => $entries_list);

		$log->debug("Exiting get_stage_history method ...");

		return $return_data;
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

		$rel_table_arr = Array("Contacts" => "vtiger_contpotentialrel", "Products" => "vtiger_seproductsrel",
			"Attachments" => "vtiger_seattachmentsrel",
			"Documents" => "vtiger_senotesrel");

		$tbl_field_arr = Array("vtiger_contpotentialrel" => "contactid", "vtiger_seproductsrel" => "productid",
			"vtiger_seattachmentsrel" => "attachmentsid",
			"vtiger_senotesrel" => "notesid");

		$entity_tbl_field_arr = Array("vtiger_contpotentialrel" => "potentialid", "vtiger_seproductsrel" => "crmid",
			"vtiger_seattachmentsrel" => "crmid","vtiger_senotesrel" => "crmid");

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
		$matrix->setDependency('vtiger_crmentityPotentials', array('vtiger_groupsPotentials', 'vtiger_usersPotentials', 'vtiger_lastModifiedByPotentials'));
		$matrix->setDependency('vtiger_potential', array('vtiger_crmentityPotentials', 'vtiger_accountPotentials',
			'vtiger_contactdetailsPotentials', 'vtiger_campaignPotentials', 'vtiger_potentialscf'));


		if (!$queryplanner->requireTable("vtiger_potential", $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_potential", "potentialid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityPotentials", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityPotentials on vtiger_crmentityPotentials.crmid=vtiger_potential.potentialid and vtiger_crmentityPotentials.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_accountPotentials")) {
			$query .= " left join vtiger_account as vtiger_accountPotentials on vtiger_potential.related_to = vtiger_accountPotentials.accountid";
		}
		if ($queryplanner->requireTable("vtiger_potentialscf")) {
			$query .= " left join vtiger_potentialscf on vtiger_potentialscf.potentialid = vtiger_potential.potentialid";
		}
		if ($queryplanner->requireTable("vtiger_groupsPotentials")) {
			$query .= " left join vtiger_groups vtiger_groupsPotentials on vtiger_groupsPotentials.groupid = vtiger_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersPotentials")) {
			$query .= " left join vtiger_users as vtiger_usersPotentials on vtiger_usersPotentials.id = vtiger_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_campaignPotentials")) {
			$query .= " left join vtiger_campaign as vtiger_campaignPotentials on vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByPotentials")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByPotentials on vtiger_lastModifiedByPotentials.id = vtiger_crmentityPotentials.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyPotentials")) {
			$query .= " left join vtiger_users as vtiger_createdbyPotentials on vtiger_createdbyPotentials.id = vtiger_crmentityPotentials.smcreatorid ";
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
			"Products" => array("vtiger_seproductsrel" => array("crmid", "productid"), "vtiger_potential" => "potentialid"),
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_potential" => "potentialid"),
			"Accounts" => array("vtiger_potential" => array("potentialid", "related_to")),
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
			$this->trash($this->module_name, $id);
		} elseif ($return_module == 'Campaigns') {
			$sql = 'UPDATE vtiger_potential SET campaignid = ? WHERE potentialid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif ($return_module == 'Products') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif ($return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_contpotentialrel WHERE potentialid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids)
	{
		$adb = PearDatabase::getInstance();

		if (!is_array($with_crmids))
			$with_crmids = Array($with_crmids);
		foreach ($with_crmids as $with_crmid) {
			if ($with_module == 'Contacts') { //When we select contact from potential related list
				$sql = "insert into vtiger_contpotentialrel values (?,?)";
				$adb->pquery($sql, array($with_crmid, $crmid));
			} elseif ($with_module == 'Products') {//when we select product from potential related list
				$adb->insert('vtiger_seproductsrel', [
					'crmid' => $crmid,
					'productid' => $with_crmid,
					'setype' => 'Potentials'
				]);
			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
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
