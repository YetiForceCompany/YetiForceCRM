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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ******************************************************************************
 * Contributor(s): YetiForce.com
 */

// Contact is used to store customer information.
class Contacts extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_contactdetails";
	var $table_index = 'contactid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_contactdetails', 'vtiger_contactaddress', 'vtiger_contactsubdetails', 'vtiger_contactscf', 'vtiger_customerdetails', 'vtiger_entity_stats');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_contactdetails' => 'contactid', 'vtiger_contactaddress' => 'contactaddressid', 'vtiger_contactsubdetails' => 'contactsubscriptionid', 'vtiger_contactscf' => 'contactid', 'vtiger_customerdetails' => 'customerid', 'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_contactscf', 'contactid');
	var $column_fields = Array();
	var $sortby_fields = Array('lastname', 'firstname', 'title', 'email', 'phone', 'smownerid', 'accountname');
	var $list_link_field = 'lastname';
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'First Name' => Array('contactdetails' => 'firstname'),
		'Last Name' => Array('contactdetails' => 'lastname'),
		'Title' => Array('contactdetails' => 'title'),
		'Member Of' => Array('account' => 'parentid'),
		'Email' => Array('contactdetails' => 'email'),
		'Office Phone' => Array('contactdetails' => 'phone'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $range_fields = Array(
		'first_name',
		'last_name',
		'primary_address_city',
		'account_name',
		'parent_id',
		'id',
		'email1',
		'salutation',
		'title',
		'phone_mobile',
		'reports_to_name',
		'primary_address_street',
		'primary_address_city',
		'primary_address_state',
		'primary_address_postalcode',
		'primary_address_country',
		'alt_address_city',
		'alt_address_street',
		'alt_address_city',
		'alt_address_state',
		'alt_address_postalcode',
		'alt_address_country',
		'office_phone',
		'home_phone',
		'other_phone',
		'fax',
		'department',
		'birthdate',
		'assistant_name',
		'assistant_phone');
	var $list_fields_name = Array(
		'First Name' => 'firstname',
		'Last Name' => 'lastname',
		'Title' => 'title',
		'Member Of' => 'parent_id',
		'Email' => 'email',
		'Office Phone' => 'phone',
		'Assigned To' => 'assigned_user_id'
	);
	var $search_fields = Array(
		'First Name' => Array('contactdetails' => 'firstname'),
		'Last Name' => Array('contactdetails' => 'lastname'),
		'Title' => Array('contactdetails' => 'title'),
		'Member Of' => Array('contactdetails' => 'parent_id'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
	);
	var $search_fields_name = Array(
		'First Name' => 'firstname',
		'Last Name' => 'lastname',
		'Title' => 'title',
		'Member Of' => 'parent_id',
		'Assigned To' => 'assigned_user_id'
	);
	// This is the list of vtiger_fields that are required
	var $required_fields = array("lastname" => 1);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'lastname', 'createdtime', 'modifiedtime');
	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('firstname', 'lastname', 'salutation', 'title', 'email', 'department', 'phone', 'mobile', 'support_start_date', 'support_end_date');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// For Alphabetical search
	var $def_basicsearch_col = 'lastname';

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/** Function to get the number of Contacts assigned to a particular User.
	 *  @param varchar $user name - Assigned to User
	 *  Returns the count of contacts assigned to user.
	 */
	public function getCount($user_name)
	{
		$log = vglobal('log');
		$log->debug("Entering getCount(" . $user_name . ") method ...");
		$query = "select count(*) from vtiger_contactdetails  inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid inner join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid where user_name=? and vtiger_crmentity.deleted=0";
		$result = $this->db->pquery($query, array($user_name), true, "Error retrieving contacts count");
		$rows_found = $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);


		$log->debug("Exiting getCount method ...");
		return $row["count(*)"];
	}

	// This function doesn't seem to be used anywhere. Need to check and remove it.
	/** Function to get the Contact Details assigned to a particular User based on the starting count and the number of subsequent records.
	 *  @param varchar $user_name - Assigned User
	 *  @param integer $from_index - Initial record number to be displayed
	 *  @param integer $offset - Count of the subsequent records to be displayed.
	 *  Returns Query.
	 */
	public function get_contacts($user_name, $from_index, $offset)
	{
		$log = vglobal('log');
		$log->debug("Entering get_contacts(" . $user_name . "," . $from_index . "," . $offset . ") method ...");
		$query = "select vtiger_users.user_name,vtiger_groups.groupname,vtiger_contactdetails.department department, vtiger_contactdetails.phone office_phone, vtiger_contactdetails.fax fax, vtiger_contactsubdetails.assistant assistant_name, vtiger_contactsubdetails.otherphone other_phone, vtiger_contactsubdetails.homephone home_phone,vtiger_contactsubdetails.birthday birthdate, vtiger_contactdetails.lastname last_name,vtiger_contactdetails.firstname first_name,vtiger_contactdetails.contactid as id, vtiger_contactdetails.salutation as salutation, vtiger_contactdetails.email as email1,vtiger_contactdetails.title as title,vtiger_contactdetails.mobile as phone_mobile,vtiger_account.accountname as account_name,vtiger_account.accountid as parent_id, vtiger_contactaddress.mailingcity as primary_address_city,vtiger_contactaddress.mailingstreet as primary_address_street, vtiger_contactaddress.mailingcountry as primary_address_country,vtiger_contactaddress.mailingstate as primary_address_state, vtiger_contactaddress.mailingzip as primary_address_postalcode,   vtiger_contactaddress.othercity as alt_address_city,vtiger_contactaddress.otherstreet as alt_address_street, vtiger_contactaddress.othercountry as alt_address_country,vtiger_contactaddress.otherstate as alt_address_state, vtiger_contactaddress.otherzip as alt_address_postalcode  from vtiger_contactdetails inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid inner join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid left join vtiger_account on vtiger_account.accountid=vtiger_contactdetails.parentid left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid left join vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id where user_name='%s' and vtiger_crmentity.deleted=0 limit %s, %s";
		$query = sprintf($query, $user_name, $from_index, $offset);
		$log->debug("Exiting get_contacts method ...");
		return $this->process_list_query1($query);
	}

	/** Function to process list query for a given query
	 *  @param $query
	 *  Returns the results of query in array format
	 */
	public function process_list_query1($query)
	{
		$log = vglobal('log');
		$log->debug("Entering process_list_query1(" . $query . ") method ...");

		$result = & $this->db->query($query, true, "Error retrieving $this->object_name list: ");
		$list = Array();
		$rows_found = $this->db->getRowCount($result);
		if ($rows_found != 0) {
			$contact = Array();
			for ($index = 0, $row = $this->db->fetchByAssoc($result, $index); $row && $index < $rows_found; $index++, $row = $this->db->fetchByAssoc($result, $index)) {
				foreach ($this->range_fields as $columnName) {
					if (isset($row[$columnName])) {

						$contact[$columnName] = $row[$columnName];
					} else {
						$contact[$columnName] = "";
					}
				}

				$list[] = $contact;
			}
		}

		$response = Array();
		$response['list'] = $list;
		$response['row_count'] = $rows_found;
		$response['next_offset'] = $next_offset;
		$response['previous_offset'] = $previous_offset;


		$log->debug("Exiting process_list_query1 method ...");
		return $response;
	}

	/** Function to process list query for Plugin with Security Parameters for a given query
	 *  @param $query
	 *  Returns the results of query in array format
	 */
	public function plugin_process_list_query($query)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$log->debug("Entering process_list_query1(" . $query . ") method ...");
		$permitted_field_lists = Array();
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select columnname from vtiger_field where tabid=4 and block <> 75 and vtiger_field.presence in (0,2)";
			$params1 = array();
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.block <> 6 and vtiger_field.block <> 75 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params1, $profileList);
			}
		}
		$result1 = $this->db->pquery($sql1, $params1);
		for ($i = 0; $i < $adb->num_rows($result1); $i++) {
			$permitted_field_lists[] = $adb->query_result($result1, $i, 'columnname');
		}

		$result = & $this->db->query($query, true, "Error retrieving $this->object_name list: ");
		$list = Array();
		$rows_found = $this->db->getRowCount($result);
		if ($rows_found != 0) {
			for ($index = 0, $row = $this->db->fetchByAssoc($result, $index); $row && $index < $rows_found; $index++, $row = $this->db->fetchByAssoc($result, $index)) {
				$contact = Array();

				$contact[lastname] = in_array("lastname", $permitted_field_lists) ? $row[lastname] : "";
				$contact[firstname] = in_array("firstname", $permitted_field_lists) ? $row[firstname] : "";
				$contact[email] = in_array("email", $permitted_field_lists) ? $row[email] : "";


				if (in_array("accountid", $permitted_field_lists)) {
					$contact[accountname] = $row[accountname];
					$contact[parent_id] = $row[accountid];
				} else {
					$contact[accountname] = "";
					$contact[parent_id] = "";
				}
				$contact[contactid] = $row[contactid];
				$list[] = $contact;
			}
		}

		$response = Array();
		$response['list'] = $list;
		$response['row_count'] = $rows_found;
		$response['next_offset'] = $next_offset;
		$response['previous_offset'] = $previous_offset;
		$log->debug("Exiting process_list_query1 method ...");
		return $response;
	}

	/**
	 * Function to get Contact related Tickets.
	 * @param  integer   $id      - contactid
	 * returns related Ticket records in array format
	 */
	public function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
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

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "select case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.crmid, vtiger_troubletickets.title, vtiger_contactdetails.contactid, vtiger_troubletickets.parent_id,
				vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_troubletickets.status, vtiger_troubletickets.priority,
				vtiger_crmentity.smownerid, vtiger_troubletickets.ticket_no, vtiger_troubletickets.contact_id
				from vtiger_troubletickets inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_troubletickets.ticketid
				left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_troubletickets.contact_id
				LEFT JOIN vtiger_ticketcf ON vtiger_troubletickets.ticketid = vtiger_ticketcf.ticketid
				left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				where vtiger_crmentity.deleted=0 and vtiger_contactdetails.contactid=" . $id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/**
	 * Function to get Contact related Products
	 * @param  integer   $id  - contactid
	 * returns related Products record in array format
	 */
	public function get_products($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
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

		$query = sprintf('SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode,
		 		  vtiger_products.commissionrate, vtiger_products.qty_per_unit, vtiger_products.unit_price,
				  vtiger_crmentity.crmid, vtiger_crmentity.smownerid,vtiger_contactdetails.lastname
				FROM vtiger_products
				INNER JOIN vtiger_seproductsrel
					ON vtiger_seproductsrel.productid=vtiger_products.productid and vtiger_seproductsrel.setype="Contacts"
				INNER JOIN vtiger_productcf
					ON vtiger_products.productid = vtiger_productcf.productid
				INNER JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_seproductsrel.crmid
				LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			   WHERE vtiger_contactdetails.contactid = %s and vtiger_crmentity.deleted = 0', $id);

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Returns a list of the associated Campaigns
	 * @param $id -- campaign id :: Type Integer
	 * @returns list of campaigns in array format
	 */
	public function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
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
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'></td>";
			}
		}

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
					vtiger_campaign.campaignid, vtiger_campaign.campaignname, vtiger_campaign.campaigntype, vtiger_campaign.campaignstatus,
					vtiger_campaign.expectedrevenue, vtiger_campaign.closingdate, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
					vtiger_crmentity.modifiedtime from vtiger_campaign
					inner JOIN vtiger_campaign_records ON vtiger_campaign_records.campaignid=vtiger_campaign.campaignid
					inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_campaign.campaignid
					inner join vtiger_campaignscf ON vtiger_campaignscf.campaignid = vtiger_campaign.campaignid
					left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
					left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
					where vtiger_campaign_records.crmid=$id and vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}

	/**
	 * Function to get Contact related vendors.
	 * @param  integer   $id      - contactid
	 * returns related vendor records in array format
	 */
	public function get_vendors($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_vendors(" . $id . ") method ...");
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

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.crmid, vtiger_vendor.*,  vtiger_vendorcf.*
				from vtiger_vendor inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_vendor.vendorid
                INNER JOIN vtiger_vendorcontactrel on vtiger_vendorcontactrel.vendorid=vtiger_vendor.vendorid
				LEFT JOIN vtiger_vendorcf on vtiger_vendorcf.vendorid=vtiger_vendor.vendorid
				LEFT JOIN vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted=0 and vtiger_vendorcontactrel.contactid=" . $id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_vendors method ...");
		return $return_value;
	}

	/** Function to export the contact records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Contacts Query.
	 */
	public function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Contacts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT vtiger_contactdetails.salutation as 'Salutation',$fields_list,case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
                                FROM vtiger_contactdetails
                                inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
                                LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id and vtiger_users.status='Active'
                                LEFT JOIN vtiger_account on vtiger_contactdetails.parentid=vtiger_account.accountid
				left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
				left join vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid=vtiger_contactdetails.contactid
			        left join vtiger_contactscf on vtiger_contactscf.contactid=vtiger_contactdetails.contactid
			        left join vtiger_customerdetails on vtiger_customerdetails.customerid=vtiger_contactdetails.contactid
	                        LEFT JOIN vtiger_groups
                        	        ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_contactdetails vtiger_contactdetails2
					ON vtiger_contactdetails2.contactid = vtiger_contactdetails.reportsto";
		$query .= getNonAdminAccessControlQuery('Contacts', $current_user);
		$where_auto = " vtiger_crmentity.deleted = 0 ";

		if ($where != '')
			$query .= sprintf(' where (%s) && %s', $where, $where_auto);
		else
			$query .= sprintf(' where %s', $where_auto);

		$log->info("Export Query Constructed Successfully");
		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the Columnnames of the Contacts
	 * Used By vtigerCRM Word Plugin
	 * Returns the Merge Fields for Word Plugin
	 */
	public function getColumnNames()
	{
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$log->debug("Entering getColumnNames() method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select fieldlabel from vtiger_field where tabid=4 and block <> 75 and vtiger_field.presence in (0,2)";
			$params1 = array();
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select vtiger_field.fieldid,fieldlabel from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.block <> 75 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by fieldid";
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
		$log->debug("Exiting getColumnNames method ...");
		return $mergeflds;
	}

//End
	/** Function to get the Contacts assigned to a user with a valid email address.
	 * @param varchar $username - User Name
	 * @param varchar $emailaddress - Email Addr for each contact.
	 * Used By vtigerCRM Outlook Plugin
	 * Returns the Query
	 */
	public function get_searchbyemailid($username, $emailaddress)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		require_once("modules/Users/Users.php");
		$seed_user = new Users();
		$user_id = $seed_user->retrieve_user_id($username);
		$current_user = $seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
		$log->debug("Entering get_searchbyemailid(" . $username . "," . $emailaddress . ") method ...");
		$query = "select vtiger_contactdetails.lastname,vtiger_contactdetails.firstname,
					vtiger_contactdetails.contactid, vtiger_contactdetails.salutation,
					vtiger_contactdetails.email,vtiger_contactdetails.title,
					vtiger_contactdetails.mobile,vtiger_account.accountname,
					vtiger_account.accountid as accountid  from vtiger_contactdetails
						inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
						inner join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
						left join vtiger_account on vtiger_account.accountid=vtiger_contactdetails.parentid
						left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
			      LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= getNonAdminAccessControlQuery('Contacts', $current_user);
		$query .= "where vtiger_crmentity.deleted=0";
		if (trim($emailaddress) != '') {
			$query .= " and ((vtiger_contactdetails.email like '" . formatForSqlLike($emailaddress) .
				"') or vtiger_contactdetails.lastname REGEXP REPLACE('" . $emailaddress .
				"',' ','|') or vtiger_contactdetails.firstname REGEXP REPLACE('" . $emailaddress .
				"',' ','|'))  and vtiger_contactdetails.email != ''";
		} else {
			$query .= " and (vtiger_contactdetails.email like '" . formatForSqlLike($emailaddress) .
				"' and vtiger_contactdetails.email != '')";
		}

		$log->debug("Exiting get_searchbyemailid method ...");
		return $this->plugin_process_list_query($query);
	}

	/** Function to get the Contacts associated with the particular User Name.
	 *  @param varchar $user_name - User Name
	 *  Returns query
	 */
	public function get_contactsforol($user_name)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		require_once("modules/Users/Users.php");
		$seed_user = new Users();
		$user_id = $seed_user->retrieve_user_id($user_name);
		$current_user = $seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select tablename,columnname from vtiger_field where tabid=4 and vtiger_field.presence in (0,2)";
			$params1 = array();
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=4 and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params1, $profileList);
			}
		}
		$result1 = $adb->pquery($sql1, $params1);
		for ($i = 0; $i < $adb->num_rows($result1); $i++) {
			$permitted_lists[] = $adb->query_result($result1, $i, 'tablename');
			$permitted_lists[] = $adb->query_result($result1, $i, 'columnname');
			if ($adb->query_result($result1, $i, 'columnname') == "parentid") {
				$permitted_lists[] = 'vtiger_account';
				$permitted_lists[] = 'accountname';
			}
		}
		$permitted_lists = array_chunk($permitted_lists, 2);
		$column_table_lists = array();
		for ($i = 0; $i < count($permitted_lists); $i++) {
			$column_table_lists[] = implode(".", $permitted_lists[$i]);
		}

		$log->debug("Entering get_contactsforol(" . $user_name . ") method ...");
		$query = sprintf("select vtiger_contactdetails.contactid as id, %s from vtiger_contactdetails
						inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid
						inner join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
						left join vtiger_customerdetails on vtiger_customerdetails.customerid=vtiger_contactdetails.contactid
						left join vtiger_account on vtiger_account.accountid=vtiger_contactdetails.parentid
						left join vtiger_contactaddress on vtiger_contactaddress.contactaddressid=vtiger_contactdetails.contactid
						left join vtiger_contactsubdetails on vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
                        left join vtiger_campaign_records on vtiger_contactdetails.contactid = vtiger_campaign_records.crmid
                        left join vtiger_campaignrelstatus on vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaign_records.campaignrelstatusid
			      LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						where vtiger_crmentity.deleted=0 and vtiger_users.user_name='%s'", implode(',', $column_table_lists), $user_name);
		$log->debug("Exiting get_contactsforol method ...");
		return $query;
	}

	/** Function to handle module specific operations when saving a entity
	 */
	public function save_module($module)
	{
		$this->insertIntoAttachment($this->id, $module);
	}

	/**
	 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the vtiger_files to be uploaded
	 *      @param string $module  - the current module name
	 */
	public function insertIntoAttachment($id, $module)
	{
		$log = LoggerManager::getInstance();
		$adb = PearDatabase::getInstance();
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		$old_attachmentid = $adb->query_result($adb->pquery("select vtiger_crmentity.crmid from vtiger_seattachmentsrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seattachmentsrel.attachmentsid where  vtiger_seattachmentsrel.crmid=?", array($id)), 0, 'crmid');
		if ($_FILES) {
			foreach ($_FILES as $fileindex => $files) {
				$fileInstance = \includes\fields\File::loadFromRequest($files);
				if ($fileInstance->validate('image')) {
					$files['original_name'] = AppRequest::get($fileindex . '_hidden');
					$file_saved = $this->uploadAndSaveFile($id, $module, $files);
				}
			}
		}

		$imageNameSql = 'SELECT name FROM vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON
								vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid LEFT JOIN vtiger_contactdetails ON
								vtiger_contactdetails.contactid = vtiger_seattachmentsrel.crmid WHERE vtiger_seattachmentsrel.crmid = ?';
		$imageNameResult = $adb->pquery($imageNameSql, array($id));
		$imageName = decode_html($adb->query_result($imageNameResult, 0, "name"));

		//Inserting image information of record into base table
		$adb->pquery('UPDATE vtiger_contactdetails SET imagename = ? WHERE contactid = ?', array($imageName, $id));

		//This is to handle the delete image for contacts
		if ($module == 'Contacts' && $file_saved) {
			if ($old_attachmentid != '') {
				$setype = $adb->query_result($adb->pquery("select setype from vtiger_crmentity where crmid=?", array($old_attachmentid)), 0, 'setype');
				if ($setype == 'Contacts Image') {
					$del_res1 = $adb->pquery("delete from vtiger_attachments where attachmentsid=?", array($old_attachmentid));
					$del_res2 = $adb->pquery("delete from vtiger_seattachmentsrel where attachmentsid=?", array($old_attachmentid));
				}
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
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
		$log = vglobal('log');
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Products" => "vtiger_seproductsrel", "Documents" => "vtiger_senotesrel",
			"Attachments" => "vtiger_seattachmentsrel", "Campaigns" => "vtiger_campaign_records",
			'ServiceContracts' => 'vtiger_servicecontracts', 'Project' => 'vtiger_project');

		$tbl_field_arr = Array("vtiger_seproductsrel" => "productid", "vtiger_senotesrel" => "notesid",
			"vtiger_seattachmentsrel" => "attachmentsid", "vtiger_campaign_records" => "campaignid",
			'vtiger_servicecontracts' => 'servicecontractsid', 'vtiger_project' => 'projectid',
			'vtiger_payments' => 'paymentsid');

		$entity_tbl_field_arr = Array("vtiger_seproductsrel" => "crmid", "vtiger_senotesrel" => "crmid",
			"vtiger_seattachmentsrel" => "crmid", "vtiger_campaign_records" => "crmid",
			'vtiger_servicecontracts' => 'sc_related_to', 'vtiger_project' => 'linktoaccountscontacts',
			'vtiger_payments' => 'relatedcontact');

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
		$matrix->setDependency('vtiger_crmentityContacts', array('vtiger_groupsContacts', 'vtiger_usersContacts', 'vtiger_lastModifiedByContacts'));
		$matrix->setDependency('vtiger_contactdetails', array('vtiger_crmentityContacts', 'vtiger_contactaddress',
			'vtiger_customerdetails', 'vtiger_contactsubdetails', 'vtiger_contactscf'));

		if (!$queryplanner->requireTable('vtiger_contactdetails', $matrix)) {
			return '';
		}


		$query = $this->getRelationQuery($module, $secmodule, "vtiger_contactdetails", "contactid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityContacts", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityContacts on vtiger_crmentityContacts.crmid = vtiger_contactdetails.contactid  and vtiger_crmentityContacts.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_contactdetailsContacts")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsContacts on vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto";
		}
		if ($queryplanner->requireTable("vtiger_contactaddress")) {
			$query .= " left join vtiger_contactaddress on vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid";
		}
		if ($queryplanner->requireTable("vtiger_customerdetails")) {
			$query .= " left join vtiger_customerdetails on vtiger_customerdetails.customerid = vtiger_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("vtiger_contactsubdetails")) {
			$query .= " left join vtiger_contactsubdetails on vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid";
		}
		if ($queryplanner->requireTable("vtiger_accountContacts")) {
			$query .= " left join vtiger_account as vtiger_accountContacts on vtiger_accountContacts.accountid = vtiger_contactdetails.parentid";
		}
		if ($queryplanner->requireTable("vtiger_contactscf")) {
			$query .= " left join vtiger_contactscf on vtiger_contactdetails.contactid = vtiger_contactscf.contactid";
		}
		if ($queryplanner->requireTable("vtiger_email_trackContacts")) {
			$query .= " LEFT JOIN vtiger_email_track AS vtiger_email_trackContacts ON vtiger_email_trackContacts.crmid = vtiger_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("vtiger_groupsContacts")) {
			$query .= " left join vtiger_groups as vtiger_groupsContacts on vtiger_groupsContacts.groupid = vtiger_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersContacts")) {
			$query .= " left join vtiger_users as vtiger_usersContacts on vtiger_usersContacts.id = vtiger_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByContacts")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByContacts on vtiger_lastModifiedByContacts.id = vtiger_crmentityContacts.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyContacts")) {
			$query .= " left join vtiger_users as vtiger_createdbyContacts on vtiger_createdbyContacts.id = vtiger_crmentityContacts.smcreatorid ";
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
		$relTables = [
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_contactdetails' => 'contactid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_contactdetails' => 'contactid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_contactdetails' => 'contactid']
		];
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	public function unlinkDependencies($module, $id)
	{
		$log = vglobal('log');

		//Backup Contact-Trouble Tickets Relation
		/* $tkt_q = 'SELECT ticketid FROM vtiger_troubletickets WHERE contact_id=?';
		  $tkt_res = $this->db->pquery($tkt_q, array($id));
		  if ($this->db->num_rows($tkt_res) > 0) {
		  $tkt_ids_list = array();
		  for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
		  {
		  $tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
		  }
		  $params = array($id, RB_RECORD_UPDATED, 'vtiger_troubletickets', 'contact_id', 'ticketid', implode(",", $tkt_ids_list));
		  $this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		  }
		  //removing the relationship of contacts with Trouble Tickets
		  $this->db->pquery('UPDATE vtiger_troubletickets SET contact_id=0 WHERE contact_id=?', array($id));
		 */
		//remove the portal info the contact
		$this->db->pquery('DELETE FROM vtiger_portalinfo WHERE id = ?', array($id));
		$this->db->pquery('UPDATE vtiger_customerdetails SET portal=0,support_start_date=NULL,support_end_date=NULl WHERE customerid=?', array($id));
		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Accounts') {
			$sql = 'UPDATE vtiger_contactdetails SET parentid = ? WHERE contactid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif ($return_module == 'Campaigns') {
			$this->db->delete('vtiger_campaign_records', 'crmid=? && campaignid=?', [$id, $return_id]);
		} elseif ($return_module == 'Products') {
			$sql = 'DELETE FROM vtiger_seproductsrel WHERE crmid=? && productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif ($return_module == 'Vendors') {
			$sql = 'DELETE FROM vtiger_vendorcontactrel WHERE vendorid=? && contactid=?';
			$this->db->pquery($sql, array($return_id, $id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if (!is_array($withCrmid))
			$withCrmid = [$withCrmid];
		if (!in_array($withModule, ['Products', 'Campaigns', 'Vendors'])) {
			parent::save_related_module($module, $crmid, $withModule, $withCrmid, $relatedName);
		} else {
			foreach ($withCrmid as $with_crmid) {
				if ($withModule == 'Products') {
					$adb->insert('vtiger_seproductsrel', [
						'crmid' => $crmid,
						'productid' => $with_crmid,
						'setype' => 'Contacts',
						'rel_created_user' => $currentUser->getId(),
						'rel_created_time' => date('Y-m-d H:i:s')
					]);
				} else if ($withModule == 'Campaigns') {
					$adb->insert('vtiger_campaign_records', [
						'campaignid' => $with_crmid,
						'crmid' => $crmid,
						'campaignrelstatusid' => 0
					]);
				} else if ($withModule == 'Vendors') {
					$adb->insert('vtiger_vendorcontactrel', [
						'vendorid' => $with_crmid,
						'contactid' => $crmid
					]);
				}
			}
		}
	}
}
