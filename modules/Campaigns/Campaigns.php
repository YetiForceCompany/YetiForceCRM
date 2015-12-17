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

class Campaigns extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_campaign";
	var $table_index = 'campaignid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_campaignscf', 'campaignid');
	var $column_fields = Array();
	var $sortby_fields = Array('campaignname', 'smownerid', 'campaigntype', 'productname', 'expectedrevenue', 'closingdate', 'campaignstatus', 'expectedresponse', 'targetaudience', 'expectedcost');
	var $list_fields = Array(
		'Campaign Name' => Array('campaign' => 'campaignname'),
		'Campaign Type' => Array('campaign' => 'campaigntype'),
		'Campaign Status' => Array('campaign' => 'campaignstatus'),
		'Expected Revenue' => Array('campaign' => 'expectedrevenue'),
		'Expected Close Date' => Array('campaign' => 'closingdate'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
		'Campaign Status' => 'campaignstatus',
		'Expected Revenue' => 'expectedrevenue',
		'Expected Close Date' => 'closingdate',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'DESC';
	//var $groupTable = Array('vtiger_campaigngrouprelation','campaignid');

	var $search_fields = Array(
		'Campaign Name' => Array('vtiger_campaign' => 'campaignname'),
		'Campaign Type' => Array('vtiger_campaign' => 'campaigntype'),
	);
	var $search_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
	);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id');
	// For Alphabetical search
	var $def_basicsearch_col = 'campaignname';

	function Campaigns()
	{
		$this->log = LoggerManager::getLogger('campaign');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Campaigns');
	}

	/** Function to handle module specific operations when saving a entity
	 */
	function save_module($module)
	{
		
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get Campaign related Accouts
	 * @param  integer   $id      - campaignid
	 * returns related Accounts record in array format
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_accounts(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		$current_user = vglobal('current_user');
		if (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus' => 'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		// Send mail button for selected Accounts
		$button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';
		/* To get Accounts CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$ahtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$ahtml .= $customviewcombo_html;
		$ahtml .= "</select>";
		/* To get Accounts CustomView -END */

		$button .= $ahtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_account.*,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.*, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*, vtiger_accountaddress.*
				FROM vtiger_account
				INNER JOIN vtiger_campaignaccountrel ON vtiger_campaignaccountrel.accountid = vtiger_account.accountid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
				INNER JOIN vtiger_accountaddress ON vtiger_accountaddress.accountaddressid = vtiger_account.accountid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_accountscf ON vtiger_account.accountid = vtiger_accountscf.accountid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignaccountrel.campaignrelstatusid
				WHERE vtiger_campaignaccountrel.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		else if ($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Accounts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Contacts
	 * @param  integer   $id      - campaignid
	 * returns related Contacts record in array format
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		$current_user = vglobal('current_user');
		if (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus' => 'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.parentid, vtiger_account.accountname,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_contactdetails.contactid, vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.title,
				vtiger_contactdetails.department, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid,
				vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime, vtiger_campaignrelstatus.*
				FROM vtiger_contactdetails
				INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
				INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
				INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
				INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.parentid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaigncontrel.campaignrelstatusid
				WHERE vtiger_campaigncontrel.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		else if ($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Contacts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Leads
	 * @param  integer   $id      - campaignid
	 * returns related Leads record in array format
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		global $log, $singlepane_view, $currentModule;
		$log->debug("Entering get_leads(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		$current_user = vglobal('current_user');
		if (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus' => 'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_SEND_MAIL_BUTTON') . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . getTranslatedString('Select One') . " --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml . "<input title='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . getTranslatedString('LBL_LOAD_LIST', $this_module) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_leaddetails.*, vtiger_crmentity.crmid,vtiger_leadaddress.phone,vtiger_leadsubdetails.website,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				vtiger_crmentity.smownerid, vtiger_campaignrelstatus.*
				FROM vtiger_leaddetails
				INNER JOIN vtiger_campaignleadrel ON vtiger_campaignleadrel.leadid=vtiger_leaddetails.leadid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadsubdetails  ON vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid
				INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leadsubdetails.leadsubscriptionid
				INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid = vtiger_leadscf.leadid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaignleadrel.campaignrelstatusid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_campaignleadrel.campaignid = " . $id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		else if ($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Leads');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Potentials
	 * @param  integer   $id      - campaignid
	 * returns related potentials record in array format
	 */
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_opportunities(" . $id . ") method ...");
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

		if ($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'campaignid', 'readwrite') == '0') {
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
					vtiger_potential.related_to, vtiger_account.accountname, vtiger_potential.potentialid, vtiger_potential.potentialname,
					vtiger_potential.potentialtype, vtiger_potential.sales_stage, vtiger_potential.closingdate,
					vtiger_crmentity.crmid, vtiger_crmentity.smownerid FROM vtiger_campaign
					INNER JOIN vtiger_potential ON vtiger_campaign.campaignid = vtiger_potential.campaignid
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid
					INNER JOIN vtiger_potentialscf ON vtiger_potential.potentialid = vtiger_potentialscf.potentialid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_potential.related_to
					WHERE vtiger_campaign.campaignid = " . $id . " AND vtiger_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}
	/*
	 * Function populate the status columns' HTML
	 * @param - $related_list return value from GetRelatedList
	 * @param - $status_column index of the status column in the list.
	 * returns true on success
	 */

	function add_status_popup($related_list, $status_column = 7, $related_module)
	{
		$adb = PearDatabase::getInstance();

		if (!$this->campaignrelstatus) {
			$result = $adb->query('SELECT * FROM vtiger_campaignrelstatus;');
			while ($row = $adb->fetchByAssoc($result)) {
				$this->campaignrelstatus[$row['campaignrelstatus']] = $row;
			}
		}
		foreach ($related_list['entries'] as $key => &$entry) {
			$popupitemshtml = '';
			foreach ($this->campaignrelstatus as $campaingrelstatus) {
				$camprelstatus = getTranslatedString($campaingrelstatus[campaignrelstatus], 'Campaigns');
				$popupitemshtml .= "<a onmouseover=\"javascript: showBlock('campaignstatus_popup_$key')\" href=\"javascript:updateCampaignRelationStatus('$related_module', '" . $this->id . "', '$key', '$campaingrelstatus[campaignrelstatusid]', '" . addslashes($camprelstatus) . "');\">$camprelstatus</a><br />";
			}
			$popuphtml = '<div onmouseover="javascript:clearTimeout(statusPopupTimer);" onmouseout="javascript:closeStatusPopup(\'campaignstatus_popup_' . $key . '\');" style="margin-top: -14px; width: 200px;" id="campaignstatus_popup_' . $key . '" class="calAction"><div style="background-color: #FFFFFF; padding: 8px;">' . $popupitemshtml . '</div></div>';

			$entry[$status_column] = "<a href=\"javascript: showBlock('campaignstatus_popup_$key');\">[+]</a> <span id='campaignstatus_$key'>" . $entry[$status_column] . "</span>" . $popuphtml;
		}

		return $related_list;
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
		$matrix->setDependency('vtiger_crmentityCampaigns', array('vtiger_groupsCampaigns', 'vtiger_usersCampaignss', 'vtiger_lastModifiedByCampaigns', 'vtiger_campaignscf'));
		$matrix->setDependency('vtiger_campaign', array('vtiger_crmentityCampaigns', 'vtiger_productsCampaigns'));

		if (!$queryplanner->requireTable("vtiger_campaign", $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_campaign", "campaignid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityCampaigns", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productsCampaigns")) {
			$query .=" 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid";
		}
		if ($queryplanner->requireTable("vtiger_campaignscf")) {
			$query .=" 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid";
		}
		if ($queryplanner->requireTable("vtiger_groupsCampaigns")) {
			$query .=" left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersCampaigns")) {
			$query .=" left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByCampaigns")) {
			$query .=" left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyCampaigns")) {
			$query .= " left join vtiger_users as vtiger_createdbyCampaigns on vtiger_createdbyCampaigns.id = vtiger_crmentityCampaigns.smcreatorid ";
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
			"Contacts" => array("vtiger_campaigncontrel" => array("campaignid", "contactid"), "vtiger_campaign" => "campaignid"),
			"Leads" => array("vtiger_campaignleadrel" => array("campaignid", "leadid"), "vtiger_campaign" => "campaignid"),
			"Accounts" => array("vtiger_campaignaccountrel" => array("campaignid", "accountid"), "vtiger_campaign" => "campaignid"),
			"Potentials" => array("vtiger_potential" => array("campaignid", "potentialid"), "vtiger_campaign" => "campaignid"),
			"Products" => array("vtiger_campaign" => array("campaignid", "product_id")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Leads') {
			$sql = 'DELETE FROM vtiger_campaignleadrel WHERE campaignid=? AND leadid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif ($return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif ($return_module == 'Accounts') {
			$sql = 'DELETE FROM vtiger_campaignaccountrel WHERE campaignid=? AND accountid=?';
			$this->db->pquery($sql, array($id, $return_id));
			$sql = 'DELETE FROM vtiger_campaigncontrel WHERE campaignid=? AND contactid IN (SELECT contactid FROM vtiger_contactdetails WHERE accountid=?)';
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
			if ($with_module == 'Leads') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaignleadrel WHERE campaignid = ? AND leadid = ?', array($crmid, $with_crmid));
				if ($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaignleadrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));
			} elseif ($with_module == 'Contacts') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaigncontrel WHERE campaignid = ? AND contactid = ?', array($crmid, $with_crmid));
				if ($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaigncontrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));
			} elseif ($with_module == 'Accounts') {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaignaccountrel WHERE campaignid = ? AND accountid = ?', array($crmid, $with_crmid));
				if ($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO vtiger_campaignaccountrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));
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
