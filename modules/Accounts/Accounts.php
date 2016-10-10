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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

class Accounts extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = 'vtiger_account';
	var $table_index = 'accountid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_account', 'vtiger_accountaddress', 'vtiger_accountscf', 'vtiger_entity_stats');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_account' => 'accountid', 'vtiger_accountaddress' => 'accountaddressid', 'vtiger_accountscf' => 'accountid', 'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_accountscf', 'accountid');
	var $entity_table = 'vtiger_crmentity';
	var $column_fields = [];
	var $sortby_fields = Array('accountname', 'bill_city', 'website', 'phone', 'smownerid');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Account Name' => Array('vtiger_account' => 'accountname'),
		'Website' => Array('vtiger_account' => 'website'),
		'Phone' => Array('vtiger_account' => 'phone'),
		'Assigned To' => Array('vtiger_crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Account Name' => 'accountname',
		'Website' => 'website',
		'Phone' => 'phone',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'accountname';
	var $search_fields = Array(
		'Account Name' => Array('vtiger_account' => 'accountname'),
		'Assigned To' => Array('vtiger_crmentity' => 'smownerid'),
	);
	var $search_fields_name = Array(
		'Account Name' => 'accountname',
		'Assigned To' => 'assigned_user_id',
	);
	// This is the list of vtiger_fields that are required
	var $required_fields = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'accountname');
	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('accountname', 'account_type', 'industry', 'annualrevenue', 'phone', 'email1', 'rating', 'website', 'fax');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'accountname';

	/** Function to handle module specific operations when saving a entity
	 */
	public function save_module($module)
	{
		
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/** Returns a list of the associated Campaigns
	 * @param $id -- campaign id :: Type Integer
	 * @returns list of campaigns in array format
	 */
	public function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_campaigns(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
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
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
		}

		$entityIds = $this->getRelatedContactsIds();
		$entityIds[] = $id;
		$entityIds = implode(',', $entityIds);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_campaign.campaignid, vtiger_campaign.campaignname, vtiger_campaign.campaigntype, vtiger_campaign.campaignstatus,
				vtiger_campaign.expectedrevenue, vtiger_campaign.closingdate, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
				vtiger_crmentity.modifiedtime
				from vtiger_campaign
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_campaign.campaignid
				INNER JOIN vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
				LEFT JOIN vtiger_campaign_records ON vtiger_campaign_records.campaignid=vtiger_campaign.campaignid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted=0 && vtiger_campaign_records.crmid IN (" . $entityIds . ")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}

	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';
		$current_user = vglobal('current_user');
		if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id', 'readwrite') == '0') {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.*,
			vtiger_crmentity.crmid,
                        vtiger_crmentity.smownerid,
			vtiger_account.accountname,
			case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
			FROM vtiger_contactdetails
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
			LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.parentid
			INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
			INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
			INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
			INNER JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
			LEFT JOIN vtiger_groups	ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_contactdetails.parentid = " . $id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Account related Tickets
	 * @param  integer   $id      - accountid
	 * returns related Ticket record in array format
	 */
	public function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_tickets(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';
		$current_user = vglobal('current_user');
		if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name, vtiger_users.id,
				vtiger_troubletickets.title, vtiger_troubletickets.ticketid AS crmid, vtiger_troubletickets.status, vtiger_troubletickets.priority,
				vtiger_troubletickets.parent_id, vtiger_troubletickets.ticket_no, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime
				FROM vtiger_troubletickets
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
				LEFT JOIN vtiger_ticketcf ON vtiger_troubletickets.ticketid = vtiger_ticketcf.ticketid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE  vtiger_crmentity.deleted = 0 and vtiger_troubletickets.parent_id = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/**
	 * Function to get Account related Products
	 * @param  integer   $id      - accountid
	 * returns related Products record in array format
	 */
	public function get_products($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_products(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
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
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$query = "SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode, vtiger_products.commissionrate,
				vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_seproductsrel.rel_created_user, vtiger_seproductsrel.rel_created_time
				FROM vtiger_products
				INNER JOIN vtiger_seproductsrel ON vtiger_products.productid = vtiger_seproductsrel.productid
				and vtiger_seproductsrel.setype IN ('Accounts', 'Contacts')
				INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0 && vtiger_seproductsrel.crmid IN (%s)";
		$query = sprintf($query, $entityIds);
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Function to export the account records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Accounts Query.
	 */
	public function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Accounts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list,case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
	       			FROM " . $this->entity_table . "
				INNER JOIN vtiger_account
					ON vtiger_account.accountid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_accountaddress
					ON vtiger_accountaddress.accountaddressid = vtiger_account.accountid
				LEFT JOIN vtiger_accountscf
					ON vtiger_accountscf.accountid = vtiger_account.accountid
	                        LEFT JOIN vtiger_groups
                        	        ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users
					ON vtiger_users.id = vtiger_crmentity.smownerid and vtiger_users.status = 'Active'
				LEFT JOIN vtiger_account vtiger_account2
					ON vtiger_account2.accountid = vtiger_account.parentid
				"; //vtiger_account2 is added to get the Member of account

		$query .= $this->getNonAdminAccessControlQuery('Accounts', $current_user);
		$where_auto = " vtiger_crmentity.deleted = 0 ";

		if ($where != '')
			$query .= sprintf(' where (%s) && %s', $where, $where_auto);
		else
			$query .= sprintf(' where %s', $where_auto);

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the Columnnames of the Account Record
	 * Used By vtigerCRM Word Plugin
	 * Returns the Merge Fields for Word Plugin
	 */
	public function getColumnNames_Acnt()
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering getColumnNames_Acnt() method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "SELECT fieldlabel FROM vtiger_field WHERE tabid = 6 and vtiger_field.presence in (0,2)";
			$params1 = [];
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select vtiger_field.fieldid,fieldlabel from vtiger_field INNER JOIN vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=6 and vtiger_field.displaytype in (1,2,4) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = [];
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
		$log->debug("Exiting getColumnNames_Acnt method ...");
		return $mergeflds;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			'Contacts' => array('vtiger_contactdetails' => array('parentid', 'contactid'), 'vtiger_account' => 'accountid'),
			'HelpDesk' => array('vtiger_troubletickets' => array('parent_id', 'ticketid'), 'vtiger_account' => 'accountid'),
			'Products' => array('vtiger_seproductsrel' => array('crmid', 'productid'), 'vtiger_account' => 'accountid'),
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_account' => 'accountid'),
			'Campaigns' => array('vtiger_campaign_records' => array('crmid', 'campaignid'), 'vtiger_account' => 'accountid'),
			'Assets' => array('vtiger_assets' => array('parent_id', 'assetsid'), 'vtiger_account' => 'accountid'),
			'Project' => array('vtiger_project' => array('linktoaccountscontacts', 'projectid'), 'vtiger_account' => 'accountid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
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
		$matrix->setDependency('vtiger_crmentityAccounts', array('vtiger_groupsAccounts', 'vtiger_usersAccounts', 'vtiger_lastModifiedByAccounts'));
		$matrix->setDependency('vtiger_account', array('vtiger_crmentityAccounts', ' vtiger_accountaddress', 'vtiger_accountscf', 'vtiger_accountAccounts', 'vtiger_email_trackAccounts'));

		if (!$queryPlanner->requireTable('vtiger_account', $matrix)) {
			return '';
		}
		/* //To remove
		  // Activities related to contact should linked to accounts if contact is related to that account
		  if($module == "Calendar"){
		  // query to get all the contacts related to Accounts
		  $relContactsQuery = "SELECT contactid FROM vtiger_contactdetails as vtiger_tmpContactCalendar
		  INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_tmpContactCalendar.contactid
		  WHERE vtiger_tmpContactCalendar.parentid IS NOT NULL && vtiger_tmpContactCalendar.parentid !=''
		  && vtiger_crmentity.deleted=0";

		  $query = " left join vtiger_cntactivityrel as vtiger_tmpcntactivityrel ON
		  vtiger_activity.activityid = vtiger_tmpcntactivityrel.activityid AND
		  vtiger_tmpcntactivityrel.contactid IN ($relContactsQuery)
		  left join vtiger_contactdetails as vtiger_tmpcontactdetails on vtiger_tmpcntactivityrel.contactid = vtiger_tmpcontactdetails.contactid ";
		  }else {
		  $query = "";
		  }
		 */
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_account", "accountid", $queryPlanner);

		if ($queryPlanner->requireTable('vtiger_crmentityAccounts', $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityAccounts on vtiger_crmentityAccounts.crmid=vtiger_account.accountid and vtiger_crmentityAccounts.deleted=0";
		}
		if ($queryPlanner->requireTable('vtiger_accountaddress')) {
			$query .= " left join vtiger_accountaddress on vtiger_account.accountid=vtiger_accountaddress.accountaddressid";
		}
		if ($queryPlanner->requireTable('vtiger_accountscf')) {
			$query .= " left join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid";
		}
		if ($queryPlanner->requireTable('vtiger_accountAccounts', $matrix)) {
			$query .= "	left join vtiger_account as vtiger_accountAccounts on vtiger_accountAccounts.accountid = vtiger_account.parentid";
		}
		if ($queryPlanner->requireTable('vtiger_email_track')) {
			$query .= " LEFT JOIN vtiger_email_track AS vtiger_email_trackAccounts ON vtiger_email_trackAccounts .crmid = vtiger_account.accountid";
		}
		if ($queryPlanner->requireTable('vtiger_groupsAccounts')) {
			$query .= "	left join vtiger_groups as vtiger_groupsAccounts on vtiger_groupsAccounts.groupid = vtiger_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('vtiger_usersAccounts')) {
			$query .= " left join vtiger_users as vtiger_usersAccounts on vtiger_usersAccounts.id = vtiger_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedByAccounts')) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByAccounts on vtiger_lastModifiedByAccounts.id = vtiger_crmentityAccounts.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyAccounts")) {
			$query .= " left join vtiger_users as vtiger_createdbyAccounts on vtiger_createdbyAccounts.id = vtiger_crmentityAccounts.smcreatorid ";
		}

		return $query;
	}

	/**
	 * Function to get Account hierarchy of the given Account
	 * @param  integer   $id      - accountid
	 * returns Account hierarchy in array format
	 */
	public function getAccountHierarchy($id, $listColumns = false)
	{
		$log = LoggerManager::getInstance();
		$current_user = vglobal('current_user');
		$log->debug('Entering getAccountHierarchy(' . $id . ') method ...');

		$listview_header = [];
		$listview_entries = [];

		$listColumns = $listColumns ? $listColumns : AppConfig::module('Accounts', 'COLUMNS_IN_HIERARCHY');
		if (empty($listColumns)) {
			$listColumns = $this->list_fields_name;
		}

		$hierarchyFields = [];
		foreach ($listColumns as $fieldLabel => $fieldName) {
			if (getFieldVisibilityPermission('Accounts', $current_user->id, $fieldName) == '0') {
				$listview_header[] = $fieldLabel;
			}
			$field = vtlib\Functions::getModuleFieldInfo('Accounts', $fieldName);
			$hierarchyFields[] = $field;
		}
		$this->hierarchyFields = $hierarchyFields;
		$accountsList = [];

		// Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
		$encountered_accounts = array($id);
		$accountsList = $this->__getParentAccounts($id, $accountsList, $encountered_accounts);

		$baseId = current(array_keys($accountsList));
		$accountsList = [$baseId => $accountsList[$baseId]];

		// Get the accounts hierarchy (list of child accounts) based on the current account
		$accountsList[$baseId] = $this->__getChildAccounts($baseId, $accountsList[$baseId], $accountsList[$baseId]['depth']);

		// Create array of all the accounts in the hierarchy
		$accountHierarchy = $this->getHierarchyData($id, $accountsList[$baseId], $baseId, $listview_entries);

		$accountHierarchy = array('header' => $listview_header, 'entries' => $listview_entries);
		$log->debug('Exiting getAccountHierarchy method ...');
		return $accountHierarchy;
	}

	/**
	 * Function to create array of all the accounts in the hierarchy
	 * @param  integer   $id - Id of the record highest in hierarchy
	 * @param  array   $accountInfoBase 
	 * @param  integer   $accountId - accountid
	 * @param  array   $listviewEntries 
	 * returns All the parent accounts of the given accountid in array format
	 */
	public function getHierarchyData($id, $accountInfoBase, $accountId, &$listviewEntries)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering getHierarchyData(' . $id . ',' . $accountId . ') method ...');
		$currentUser = vglobal('current_user');
		require('user_privileges/user_privileges_' . $currentUser->id . '.php');

		$hasRecordViewAccess = (vtlib\Functions::userIsAdministrator($currentUser)) || (isPermitted('Accounts', 'DetailView', $accountId) == 'yes');
		foreach ($this->hierarchyFields as &$field) {
			$fieldName = $field['fieldname'];
			$rawData = '';
			// Permission to view account is restricted, avoid showing field values (except account name)
			if (getFieldVisibilityPermission('Accounts', $currentUser->id, $fieldName) == '0') {
				$data = $accountInfoBase[$fieldName];
				if ($fieldName == 'accountname') {
					if ($accountId != $id) {
						if ($hasRecordViewAccess) {
							$data = '<a href="index.php?module=Accounts&view=Detail&record=' . $accountId . '">' . $data . '</a>';
						} else {
							$data = '<span>' . $data . '&nbsp;<span class="glyphicon glyphicon-warning-sign"></span></span>';
						}
					} else {
						$data = '<strong>' . $data . '</strong>';
					}
					// - to show the hierarchy of the Accounts
					$account_depth = str_repeat(' .. ', $accountInfoBase['depth']);
					$data = $account_depth . $data;
				} else if ($fieldName == 'assigned_user_id' || $fieldName == 'shownerid') {
					
				} else {
					$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($field['fieldid']);
					$rawData = $data;
					$data = $fieldModel->getDisplayValue($data);
				}
				$accountInfoData[] = ['data' => $data, 'fieldname' => $fieldName, 'rawData' => $rawData];
			}
		}
		$listviewEntries[$accountId] = $accountInfoData;
		foreach ($accountInfoBase as $accId => $accountInfo) {
			if (is_array($accountInfo) && intval($accId)) {
				$listviewEntries = $this->getHierarchyData($id, $accountInfo, $accId, $listviewEntries);
			}
		}
		$log->debug('Exiting getHierarchyData method ...');
		return $listviewEntries;
	}

	/**
	 * Function to Recursively get all the upper accounts of a given Account
	 * @param  integer   $id      		- accountid
	 * @param  array   $parent_accounts   - Array of all the parent accounts
	 * returns All the parent accounts of the given accountid in array format
	 */
	public function __getParentAccounts($id, &$parent_accounts, &$encountered_accounts, $depthBase = 0)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug('Entering __getParentAccounts(' . $id . ') method ...');

		if ($depthBase == AppConfig::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			$log->error('Exiting __getParentAccounts method ... - exceeded maximum depth of hierarchy');
			return $parent_accounts;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = 'SELECT vtiger_account.*, vtiger_accountaddress.*,' .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_account' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid' .
			' INNER JOIN vtiger_accountaddress ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and vtiger_account.accountid = ?';
		$res = $adb->pquery($query, [$id]);

		if ($adb->getRowCount($res) > 0) {
			$row = $adb->getRow($res);
			$parentid = $row['parentid'];
			if ($parentid != '' && $parentid != 0 && !in_array($parentid, $encountered_accounts)) {
				$encountered_accounts[] = $parentid;
				$this->__getParentAccounts($parentid, $parent_accounts, $encountered_accounts, $depthBase + 1);
			}
			$parent_account_info = [];
			$depth = 0;
			if (isset($parent_accounts[$parentid])) {
				$depth = $parent_accounts[$parentid]['depth'] + 1;
			}
			$parent_account_info['depth'] = $depth;
			foreach ($this->hierarchyFields as &$field) {
				$fieldName = $field['fieldname'];

				if ($fieldName == 'assigned_user_id') {
					$parent_account_info[$fieldName] = $row['user_name'];
				} elseif ($fieldName == 'shownerid') {
					$sharedOwners = Vtiger_SharedOwner_UIType::getSharedOwners($row['accountid']);
					if (!empty($sharedOwners)) {
						$sharedOwners = implode(',', array_map('vtlib\Functions::getOwnerRecordLabel', $sharedOwners));
						$parent_account_info[$fieldName] = $sharedOwners;
					}
				} else {
					$parent_account_info[$fieldName] = $row[$field['columnname']];
				}
			}
			$parent_accounts[$id] = $parent_account_info;
		}
		$log->debug('Exiting __getParentAccounts method ...');
		return $parent_accounts;
	}

	/**
	 * Function to Recursively get all the child accounts of a given Account
	 * @param  integer   $id      		- accountid
	 * @param  array   $child_accounts   - Array of all the child accounts
	 * @param  integer   $depth          - Depth at which the particular account has to be placed in the hierarchy
	 * returns All the child accounts of the given accountid in array format
	 */
	public function __getChildAccounts($id, &$child_accounts, $depthBase)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug('Entering __getChildAccounts(' . $id . ',' . $depthBase . ') method ...');

		if ($depthBase == AppConfig::module('Accounts', 'MAX_HIERARCHY_DEPTH')) {
			$log->error('Exiting __getChildAccounts method ... - exceeded maximum depth of hierarchy');
			return $child_accounts;
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_account.*, vtiger_accountaddress.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			' FROM vtiger_account' .
			' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid' .
			' INNER JOIN vtiger_accountaddress ON vtiger_account.accountid = vtiger_accountaddress.accountaddressid ' .
			' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid' .
			' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid' .
			' WHERE vtiger_crmentity.deleted = 0 and parentid = ?';
		$res = $adb->pquery($query, [$id]);

		if ($adb->getRowCount($res) > 0) {
			$depth = $depthBase + 1;
			while ($row = $adb->getRow($res)) {
				$child_acc_id = $row['accountid'];
				$child_account_info = [];
				$child_account_info['depth'] = $depth;
				foreach ($this->hierarchyFields as &$field) {
					$fieldName = $field['fieldname'];
					if ($fieldName == 'assigned_user_id') {
						$child_account_info[$fieldName] = $row['user_name'];
					} elseif ($fieldName == 'shownerid') {
						$sharedOwners = Vtiger_SharedOwner_UIType::getSharedOwners($child_acc_id);
						if (!empty($sharedOwners)) {
							$sharedOwners = implode(',', array_map('vtlib\Functions::getOwnerRecordLabel', $sharedOwners));
							$child_account_info[$fieldName] = $sharedOwners;
						}
					} else {
						$child_account_info[$fieldName] = $row[$field['columnname']];
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildAccounts($child_acc_id, $child_accounts[$child_acc_id], $depth);
			}
		}
		$log->debug('Exiting __getChildAccounts method ...');
		return $child_accounts;
	}

	// Function to unlink the dependent records of the given record by id
	public function unlinkDependencies($module, $id)
	{
		$log = vglobal('log');
		//Backup Contact-Account Relation
		$con_q = 'SELECT contactid FROM vtiger_contactdetails WHERE parentid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			$con_ids_list = [];
			for ($k = 0; $k < $this->db->num_rows($con_res); $k++) {
				$con_ids_list[] = $this->db->query_result($con_res, $k, "contactid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'vtiger_contactdetails', 'parentid', 'contactid', implode(",", $con_ids_list));
			$this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Contact-Account Relation.
		$con_q = 'UPDATE vtiger_contactdetails SET parentid = 0 WHERE parentid = ?';
		$this->db->pquery($con_q, array($id));

		//Backup Trouble Tickets-Account Relation
		$tkt_q = 'SELECT ticketid FROM vtiger_troubletickets WHERE parent_id = ?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = [];
			for ($k = 0; $k < $this->db->num_rows($tkt_res); $k++) {
				$tkt_ids_list[] = $this->db->query_result($tkt_res, $k, "ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'vtiger_troubletickets', 'parent_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Trouble Tickets-Account Relation.
		$tt_q = 'UPDATE vtiger_troubletickets SET parent_id = 0 WHERE parent_id = ?';
		$this->db->pquery($tt_q, array($id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Campaigns') {
			$this->db->delete('vtiger_campaign_records', 'crmid=? && campaignid=?', [$id, $return_id]);
		} else if ($return_module == 'Products') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE crmid=? && productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $with_module, $with_crmids, $relatedName = false)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if (!is_array($with_crmids))
			$with_crmids = [$with_crmids];
		if (!in_array($with_module, ['Products', 'Campaigns'])) {
			parent::save_related_module($module, $crmid, $with_module, $with_crmids, $relatedName);
		} else {
			foreach ($with_crmids as $with_crmid) {
				if ($with_module == 'Products') {
					$insert = $db->insert('vtiger_seproductsrel', [
						'crmid' => $crmid,
						'productid' => $with_crmid,
						'setype' => $module,
						'rel_created_user' => $currentUser->getId(),
						'rel_created_time' => date('Y-m-d H:i:s')
					]);
				} elseif ($with_module == 'Campaigns') {
					$checkResult = $db->pquery('SELECT 1 FROM vtiger_campaign_records WHERE campaignid = ? && crmid = ?', [$with_crmid, $crmid]);
					if ($db->getRowCount($checkResult) > 0) {
						continue;
					}
					$db->insert('vtiger_campaign_records', [
						'campaignid' => $with_crmid,
						'crmid' => $crmid,
						'campaignrelstatusid' => 1
					]);
				}
			}
		}
	}
	/* Function to get attachments in the related list of accounts module */

	public function get_attachments($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{

		global $currentModule, $singlepane_view;
		$this_module = $currentModule;
		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);
		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true') {
			$returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				'Documents' ActivityType,vtiger_attachments.type  FileType,crm2.modifiedtime lastmodified,vtiger_crmentity.modifiedtime,
				vtiger_seattachmentsrel.attachmentsid attachmentsid, vtiger_notes.notesid crmid, vtiger_notes.notecontent description,vtiger_notes.*
				from vtiger_notes
				INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid= vtiger_notes.notesid
				LEFT JOIN vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0
				INNER JOIN vtiger_crmentity crm2 ON crm2.crmid=vtiger_senotesrel.crmid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid =vtiger_notes.notesid
				LEFT JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid= vtiger_users.id
				WHERE crm2.crmid IN (" . $entityIds . ")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;
		return $return_value;
	}

	public function createDependentQuery($other, $row, $id)
	{
		$dependentColumn = $row['columnname'];
		$dependentTable = $row['tablename'];
		$joinTables = [];
		$join = '';
		$tables = '';
		foreach ($other->tab_name_index as $table => $index) {
			if ($table == $other->table_name) {
				continue;
			}
			$joinTables[] = $table;
			$join .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $index . ' = ' . $other->table_name . '.' . $other->table_index;
		}

		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$tables .= ", $tname.*";
				if (in_array($tname, $joinTables)) {
					continue;
				}
				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$join .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}
		$entityIds = $this->getRelatedContactsIds();
		$entityIds[] = $id;
		$entityIds = implode(',', $entityIds);

		$query = "SELECT vtiger_crmentity.*, $other->table_name.*";
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'), 'Users');
		$query .= $tables;
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";
		$query .= sprintf(' FROM %s', $other->table_name);
		$query .= $join;
		$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " WHERE vtiger_crmentity.deleted = 0 && $other->table_name.$dependentColumn IN ($entityIds)";
		return $query;
	}

	/**
	 * Function to handle the related list for the module.
	 * NOTE: vtlib\Module::setRelatedList sets reference to this function in vtiger_relatedlists table
	 * if function name is not explicitly specified.
	 */
	public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $currentModule, $singlepane_view;

		$current_module = vtlib\Functions::getModuleName($cur_tab_id);
		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($current_module, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' " .
					" type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$current_module&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\"" .
					" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module, $related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname, $related_module) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true') {
			$returnset = "&return_module=$current_module&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$current_module&return_action=CallRelatedList&return_id=$id";
		}

		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT vtiger_crmentity.*, $other->table_name.*,
				CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name FROM $other->table_name
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index
				INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid || vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
				$more_relation
				LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
				LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0 && (vtiger_crmentityrel.crmid IN (" . $entityIds . ") || vtiger_crmentityrel.relcrmid IN (" . $entityIds . "))";

		$return_value = GetRelatedList($current_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}
	/* Function to get related contact ids for an account record */

	public function getRelatedContactsIds($id = null)
	{
		$adb = PearDatabase::getInstance();
		if ($id == null)
			$id = $this->id;
		$entityIds = [];
		$query = 'SELECT contactid FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				WHERE vtiger_contactdetails.parentid = ? && vtiger_crmentity.deleted = 0';
		$accountContacts = $adb->pquery($query, array($id));
		$numOfContacts = $adb->num_rows($accountContacts);
		if ($accountContacts && $numOfContacts > 0) {
			for ($i = 0; $i < $numOfContacts; ++$i) {
				array_push($entityIds, $adb->query_result($accountContacts, $i, 'contactid'));
			}
		}
		return $entityIds;
	}
}
