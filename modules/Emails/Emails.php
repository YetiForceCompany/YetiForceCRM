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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Emails/Emails.php,v 1.41 2005/04/28 08:11:21 rank Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ******************************************************************************
 * Contributor(s): YetiForce.com
 */

// Email is used to store customer information.
class Emails extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_activity";
	var $table_index = 'activityid';
	// Stored vtiger_fields
	// added to check email save from plugin or not
	var $plugin_save = false;
	var $rel_users_table = "vtiger_salesmanactivityrel";
	var $rel_contacts_table = "vtiger_cntactivityrel";
	var $rel_serel_table = "vtiger_seactivityrel";
	var $tab_name = Array('vtiger_crmentity', 'vtiger_activity', 'vtiger_emaildetails');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid',
		'vtiger_seactivityrel' => 'activityid', 'vtiger_cntactivityrel' => 'activityid', 'vtiger_email_track' => 'mailid', 'vtiger_emaildetails' => 'emailid');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Subject' => Array('activity' => 'subject'),
		'Related to' => Array('seactivityrel' => 'parent_id'),
		'Date Sent' => Array('activity' => 'date_start'),
		'Time Sent' => Array('activity' => 'time_start'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Access Count' => Array('email_track', 'access_count')
	);
	var $list_fields_name = Array(
		'Subject' => 'subject',
		'Related to' => 'parent_id',
		'Date Sent' => 'date_start',
		'Time Sent' => 'time_start',
		'Assigned To' => 'assigned_user_id',
		'Access Count' => 'access_count'
	);
	var $list_link_field = 'subject';
	var $column_fields = Array();
	var $sortby_fields = Array('subject', 'date_start', 'saved_toid');
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject', 'assigned_user_id');

	public function save_module($module)
	{
		$adb = PearDatabase::getInstance();
		//Inserting into seactivityrel
		//modified by Richie as raju's implementation broke the feature for addition of webmail to vtiger_crmentity.need to be more careful in future while integrating code
		if ($_REQUEST['module'] == "Emails" && (!$this->plugin_save)) {
			if ($_REQUEST['currentid'] != '') {
				$actid = $_REQUEST['currentid'];
			} else {
				$actid = $_REQUEST['record'];
			}
			$parentid = $_REQUEST['parent_id'];
			if ($_REQUEST['module'] != 'Emails') {
				if (!$parentid) {
					$parentid = $adb->getUniqueID('vtiger_seactivityrel');
				}
				$mysql = 'insert into vtiger_seactivityrel values(?,?)';
				$adb->pquery($mysql, array($parentid, $actid));
			} else {
				$myids = explode("|", $parentid);  //2@71|
				for ($i = 0; $i < (count($myids) - 1); $i++) {
					$realid = explode("@", $myids[$i]);
					$mycrmid = $realid[0];
					//added to handle the relationship of emails with vtiger_users
					if ($realid[1] == -1) {
						$del_q = 'delete from vtiger_salesmanactivityrel where smid=? and activityid=?';
						$adb->pquery($del_q, array($mycrmid, $actid));
						$mysql = 'insert into vtiger_salesmanactivityrel values(?,?)';
					} else {
						$del_q = 'delete from vtiger_seactivityrel where crmid=? and activityid=?';
						$adb->pquery($del_q, array($mycrmid, $actid));
						$mysql = 'insert into vtiger_seactivityrel values(?,?)';
					}
					$params = array($mycrmid, $actid);
					$adb->pquery($mysql, $params);
				}
			}
		} else {
			if (isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '') {
				$adb->pquery("DELETE FROM vtiger_seactivityrel WHERE crmid = ? && activityid = ? ", array($this->column_fields['parent_id'], $this->id));
				$sql = 'insert into vtiger_seactivityrel values(?,?)';
				$params = array($this->column_fields['parent_id'], $this->id);
				$adb->pquery($sql, $params);
			} elseif ($this->column_fields['parent_id'] == '' && $insertion_mode == "edit") {
				$this->deleteRelation('vtiger_seactivityrel');
			}
		}


		//Insert into cntactivity rel

		if (isset($this->column_fields['contact_id']) && $this->column_fields['contact_id'] != '') {
			$this->insertIntoEntityTable('vtiger_cntactivityrel', $module);
		} elseif ($this->column_fields['contact_id'] == '' && $insertion_mode == "edit") {
			$this->deleteRelation('vtiger_cntactivityrel');
		}

		//Inserting into attachment

		$this->insertIntoAttachment($this->id, $module);
	}

	public function insertIntoAttachment($id, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;

		//Added to send generated Invoice PDF with mail
		$pdfAttached = $_REQUEST['pdf_attachment'];
		//created Invoice pdf is attached with the mail
		if (isset($_REQUEST['pdf_attachment']) && $_REQUEST['pdf_attachment'] != '') {
			$file_saved = pdfAttach($this, $module, $pdfAttached, $id);
		}

		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		foreach ($_FILES as $fileindex => $files) {
			if ($files['name'] != '' && $files['size'] > 0) {
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex . '_hidden']);
				$file_saved = $this->uploadAndSaveFile($id, $module, $files);
			}
		}
		if ($module == 'Emails' && isset($_REQUEST['att_id_list']) && $_REQUEST['att_id_list'] != '') {
			$att_lists = explode(";", $_REQUEST['att_id_list'], -1);
			$id_cnt = count($att_lists);
			if ($id_cnt != 0) {
				for ($i = 0; $i < $id_cnt; $i++) {
					$sql_rel = 'insert into vtiger_seattachmentsrel values(?,?)';
					$adb->pquery($sql_rel, array($id, $att_lists[$i]));
				}
			}
		}
		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}

	public function saveForwardAttachments($id, $module, $file_details)
	{
		$log = vglobal('log');
		$log->debug("Entering into saveForwardAttachments($id,$module,$file_details) method.");
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');

		$mailbox = $_REQUEST["mailbox"];
		$MailBox = new MailBox($mailbox);
		$mail = $MailBox->mbox;
		$binFile = \includes\fields\File::sanitizeUploadFileName($file_details['name']);
		$filename = ltrim(basename(" " . $binFile)); //allowed filename like UTF-8 characters
		$filetype = $file_details['type'];
		$filesize = $file_details['size'];
		$filepart = $file_details['part'];
		$transfer = $file_details['transfer'];
		$file = imap_fetchbody($mail, $_REQUEST['mailid'], $filepart);
		if ($transfer == 'BASE64')
			$file = imap_base64($file);
		elseif ($transfer == 'QUOTED-PRINTABLE')
			$file = imap_qprint($file);
		$current_id = $adb->getUniqueID("vtiger_crmentity");
		$date_var = date('Y-m-d H:i:s');
		//to get the owner id
		$ownerid = $this->column_fields['assigned_user_id'];
		if (!isset($ownerid) || $ownerid == '')
			$ownerid = $current_user->id;
		$upload_file_path = \vtlib\Functions::initStorageFileDirectory();
		file_put_contents($upload_file_path . $current_id . "_" . $filename, $file);

		$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
		$params1 = array($current_id, $current_user->id, $ownerid, $module . " Attachment", $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
		$adb->pquery($sql1, $params1);

		$sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?,?,?,?,?)";
		$params2 = array($current_id, $filename, $this->column_fields['description'], $filetype, $upload_file_path);
		$result = $adb->pquery($sql2, $params2);

		if ($_REQUEST['mode'] == 'edit') {
			if ($id != '' && $_REQUEST['fileid'] != '') {
				$delquery = 'delete from vtiger_seattachmentsrel where crmid = ? and attachmentsid = ?';
				$adb->pquery($delquery, array($id, $_REQUEST['fileid']));
			}
		}
		$sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
		$adb->pquery($sql3, array($id, $current_id));
		return true;
		$log->debug("exiting from  saveforwardattachment function.");
	}

	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$currentModule = vglobal('currentModule');
		$singlepane_view = vglobal('singlepane_view');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;

		$button = '';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('BULKMAIL', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_BULK_MAILS') . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"sendmail\";this.form.module.value=\"$this_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_BULK_MAILS') . "'>";
			}
		}

		$query = sprintf('SELECT 
					vtiger_contactdetails.parentid,
					vtiger_contactdetails.contactid,
					vtiger_contactdetails.firstname,
					vtiger_contactdetails.lastname,
					vtiger_contactdetails.department,
					vtiger_contactdetails.title,
					vtiger_contactdetails.email,
					vtiger_contactdetails.phone,
					vtiger_contactdetails.emailoptout,
					vtiger_crmentity.crmid,
					vtiger_crmentity.smownerid,
					vtiger_crmentity.modifiedtime 
				  FROM
					vtiger_contactdetails 
					INNER JOIN vtiger_cntactivityrel 
					  ON vtiger_cntactivityrel.contactid = vtiger_contactdetails.contactid 
					INNER JOIN vtiger_crmentity 
					  ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid 
					LEFT JOIN vtiger_groups 
					  ON vtiger_groups.groupid = vtiger_crmentity.smownerid 
				  WHERE vtiger_cntactivityrel.activityid = %s 
					AND vtiger_crmentity.deleted = 0;', $adb->quote($id));

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/** Returns the column name that needs to be sorted
	 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
	 * All Rights Reserved..
	 * Contributor(s): Mike Crowe
	 */
	public function getSortOrder()
	{
		$log = vglobal('log');
		$log->debug("Entering getSortOrder() method ...");
		if (isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else
			$sorder = (($_SESSION['EMAILS_SORT_ORDER'] != '') ? ($_SESSION['EMAILS_SORT_ORDER']) : ($this->default_sort_order));

		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/** Returns the order in which the records need to be sorted
	 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
	 * All Rights Reserved..
	 * Contributor(s): Mike Crowe
	 */
	public function getOrderBy()
	{
		$log = vglobal('log');
		$log->debug("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		else
			$order_by = (($_SESSION['EMAILS_ORDER_BY'] != '') ? ($_SESSION['EMAILS_ORDER_BY']) : ($use_default_order_by));

		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------

	/** Returns a list of the associated vtiger_users
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	public function get_users($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_users(" . $id . ") method ...");
		$adb = PearDatabase::getInstance();
		global $app_strings;

		$id = $_REQUEST['record'];

		$button = '<input title="' . \includes\Language::translate('LBL_BULK_MAILS') . '" accessykey="F" class="crmbutton small create"
				onclick="this.form.action.value=\"sendmail\";this.form.return_action.value=\"DetailView\";this.form.module.value=\"Emails\";this.form.return_module.value=\"Emails\";"
				name="button" value="' . \includes\Language::translate('LBL_BULK_MAILS') . '" type="submit">&nbsp;
				<input title="' . \includes\Language::translate('LBL_BULK_MAILS') . '" accesskey="" tabindex="2" class="crmbutton small edit"
				value="' . \includes\Language::translate('LBL_SELECT_USER_BUTTON_LABEL') . '" name="Button" language="javascript"
				onclick=\"return window.open("index.php?module=Users&return_module=Emails&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=true&return_id=' . $id . '&recordid=' . $id . '","test","width=640,height=520,resizable=0,scrollbars=0");\"
				type="button">';

		$query = 'SELECT vtiger_users.id, vtiger_users.first_name,vtiger_users.last_name, vtiger_users.user_name, vtiger_users.email1,  vtiger_users.phone_home, vtiger_users.phone_work, vtiger_users.phone_mobile, vtiger_users.phone_other, vtiger_users.phone_fax from vtiger_users inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.smid=vtiger_users.id and vtiger_salesmanactivityrel.activityid=?';
		$result = $adb->pquery($query, array($id));

		$noofrows = $adb->num_rows($result);
		$header [] = $app_strings['LBL_LIST_NAME'];

		$header [] = $app_strings['LBL_LIST_USER_NAME'];

		$header [] = $app_strings['LBL_EMAIL'];

		$header [] = $app_strings['LBL_PHONE'];
		while ($row = $adb->fetch_array($result)) {

			$current_user = vglobal('current_user');

			$entries = Array();

			if (vtlib\Functions::userIsAdministrator($current_user)) {
				$entries[] = \vtlib\Deprecated::getFullNameFromArray('Users', $row);
			} else {
				$entries[] = \vtlib\Deprecated::getFullNameFromArray('Users', $row);
			}

			$entries[] = $row['user_name'];
			$entries[] = $row['email1'];
			$entries[] = $row['phone_home'];
			if ($phone == '')
				$phone = $row['phone_work'];
			if ($phone == '')
				$phone = $row['phone_mobile'];
			if ($phone == '')
				$phone = $row['phone_other'];
			if ($phone == '')
				$phone = $row['phone_fax'];

			//Adding Security Check for User

			$entries_list[] = $entries;
		}

		if ($entries_list != '')
			$return_data = array("header" => $header, "entries" => $entries);

		if ($return_data == null)
			$return_data = Array();
		$return_data['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_users method ...");
		return $return_data;
	}

	/**
	 * Returns a list of the Emails to be exported
	 */
	public function create_export_query(&$order_by, &$where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $order_by . "," . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Emails", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM vtiger_activity
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid=vtiger_activity.activityid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_seactivityrel
				ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
			LEFT JOIN vtiger_contactdetails
				ON vtiger_contactdetails.contactid = vtiger_seactivityrel.crmid
			LEFT JOIN vtiger_cntactivityrel
				ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
				AND vtiger_cntactivityrel.contactid = vtiger_cntactivityrel.contactid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_salesmanactivityrel
				ON vtiger_salesmanactivityrel.activityid = vtiger_activity.activityid
			LEFT JOIN vtiger_emaildetails
				ON vtiger_emaildetails.emailid = vtiger_activity.activityid
			LEFT JOIN vtiger_seattachmentsrel
				ON vtiger_activity.activityid=vtiger_seattachmentsrel.crmid
			LEFT JOIN vtiger_attachments
				ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid";
		$query .= getNonAdminAccessControlQuery('Emails', $current_user);
		$query .= "WHERE vtiger_activity.activitytype='Emails' && vtiger_crmentity.deleted=0 ";

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/**
	 * Used to releate email and contacts -- Outlook Plugin
	 */
	public function set_emails_contact_invitee_relationship($email_id, $contact_id)
	{
		$log = vglobal('log');
		$log->debug("Entering set_emails_contact_invitee_relationship(" . $email_id . "," . $contact_id . ") method ...");
		$query = "insert into $this->rel_contacts_table (contactid,activityid) values(?,?)";
		$this->db->pquery($query, array($contact_id, $email_id), true, "Error setting email to contact relationship: " . "<BR>$query");
		$log->debug("Exiting set_emails_contact_invitee_relationship method ...");
	}

	/**
	 * Used to releate email and salesentity -- Outlook Plugin
	 */
	public function set_emails_se_invitee_relationship($email_id, $contact_id)
	{
		$log = vglobal('log');
		$log->debug("Entering set_emails_se_invitee_relationship(" . $email_id . "," . $contact_id . ") method ...");
		$query = "insert into $this->rel_serel_table (crmid,activityid) values(?,?)";
		$this->db->pquery($query, array($contact_id, $email_id), true, "Error setting email to contact relationship: " . "<BR>$query");
		$log->debug("Exiting set_emails_se_invitee_relationship method ...");
	}

	/**
	 * Used to releate email and Users -- Outlook Plugin
	 */
	public function set_emails_user_invitee_relationship($email_id, $user_id)
	{
		$log = vglobal('log');
		$log->debug("Entering set_emails_user_invitee_relationship(" . $email_id . "," . $user_id . ") method ...");
		$query = "insert into $this->rel_users_table (smid,activityid) values (?,?)";
		$this->db->pquery($query, array($user_id, $email_id), true, "Error setting email to user relationship: " . "<BR>$query");
		$log->debug("Exiting set_emails_user_invitee_relationship method ...");
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		$log = vglobal('log');

		$sql = 'DELETE FROM vtiger_seactivityrel WHERE activityid=? && crmid = ?';
		$this->db->pquery($sql, array($id, $returnId));

		$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? && relmodule=? && relcrmid=?) || (relcrmid=? && module=? && crmid=?)';
		$params = array($id, $returnModule, $returnId, $id, $returnModule, $returnId);
		$this->db->pquery($sql, $params);

		$this->db->pquery('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?', array(date('y-m-d H:i:d'), $id));
	}

	public function getNonAdminAccessControlQuery($module, $user, $scope = '')
	{
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = \includes\Modules::getModuleId($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . $user->id;
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $sharingRuleInfoVariable;
			$sharedTabId = null;
			if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
				count($sharingRuleInfo['GROUP']) > 0)) {
				$tableName = $tableName . '_t' . $tabId;
				$sharedTabId = $tabId;
			}
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
			$query = " INNER JOIN $tableName $tableName$scope ON $tableName$scope.id = " .
				"vtiger_crmentity$scope.smownerid ";
		}
		return $query;
	}

	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = getTabname($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared int(1) default 0) ignore " . $query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array());
		if (is_object($result)) {
			return true;
		}
		return false;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			"Leads" => array("vtiger_seactivityrel" => array("activityid", "crmid"), "vtiger_activity" => "activityid"),
			"Vendors" => array("vtiger_seactivityrel" => array("activityid", "crmid"), "vtiger_activity" => "activityid"),
			"Contacts" => array("vtiger_seactivityrel" => array("activityid", "crmid"), "vtiger_activity" => "activityid"),
			"Accounts" => array("vtiger_seactivityrel" => array("activityid", "crmid"), "vtiger_activity" => "activityid"),
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
		$focus = CRMEntity::getInstance($module);
		$matrix = $queryPlanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityEmails", array("vtiger_groupsEmails", "vtiger_usersEmails", "vtiger_lastModifiedByEmails"));
		$matrix->setDependency("vtiger_activity", array("vtiger_crmentityEmails", "vtiger_email_track"));

		if (!$queryPlanner->requireTable('vtiger_activity', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_activity", "activityid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityEmails")) {
			$query .= " LEFT JOIN vtiger_crmentity AS vtiger_crmentityEmails ON vtiger_crmentityEmails.crmid=vtiger_activity.activityid and vtiger_crmentityEmails.deleted = 0";
		}
		if ($queryPlanner->requireTable("vtiger_groupsEmails")) {
			$query .= " LEFT JOIN vtiger_groups AS vtiger_groupsEmails ON vtiger_groupsEmails.groupid = vtiger_crmentityEmails.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersEmails")) {
			$query .= " LEFT JOIN vtiger_users AS vtiger_usersEmails ON vtiger_usersEmails.id = vtiger_crmentityEmails.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByEmails")) {
			$query .= " LEFT JOIN vtiger_users AS vtiger_lastModifiedByEmails ON vtiger_lastModifiedByEmails.id = vtiger_crmentityEmails.modifiedby and vtiger_seactivityreltmpEmails.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyEmails")) {
			$query .= " left join vtiger_users as vtiger_createdbyEmails on vtiger_createdbyEmails.id = vtiger_crmentityEmails.smcreatorid and vtiger_seactivityreltmpEmails.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_email_track")) {
			$query .= " LEFT JOIN vtiger_email_track ON vtiger_email_track.mailid = vtiger_activity.activityid and vtiger_email_track.crmid = " . $focus->table_name . "." . $focus->table_index;
		}
		return $query;
	}
	/*
	 * Function to store the email access count value of emails in 'vtiger_email_track' table
	 * @param - $mailid
	 */

	public function setEmailAccessCountValue($mailid)
	{
		$adb = PearDatabase::getInstance();
		$successIds = array();
		$result = $adb->pquery('SELECT idlists FROM vtiger_emaildetails WHERE emailid=?', array($mailid));
		$idlists = $adb->query_result($result, 0, 'idlists');
		$idlistsArray = explode('|', $idlists);

		for ($i = 0; $i < (count($idlistsArray) - 1); $i++) {
			$crmid = explode("@", $idlistsArray[$i]);
			array_push($successIds, $crmid[0]);
		}
		$successIds = array_unique($successIds);
		sort($successIds);
		for ($i = 0; $i < count($successIds); $i++) {
			$adb->pquery("INSERT INTO vtiger_email_track(crmid, mailid,  access_count) VALUES(?,?,?)", array($successIds[$i], $mailid, 0));
		}
	}
}

//added for attach the generated pdf with email
function pdfAttach($obj, $module, $file_name, $id)
{
	$log = vglobal('log');
	$log->debug("Entering into pdfAttach() method.");

	$adb = PearDatabase::getInstance();
	$current_user = vglobal('current_user');
	global $upload_badext;
	$date_var = date('Y-m-d H:i:s');

	$ownerid = $obj->column_fields['assigned_user_id'];
	if (!isset($ownerid) || $ownerid == '')
		$ownerid = $current_user->id;

	$current_id = $adb->getUniqueID("vtiger_crmentity");

	$upload_file_path = \vtlib\Functions::initStorageFileDirectory();

	//Copy the file from temporary directory into storage directory for upload
	$source_file_path = "storage/" . $file_name;
	$status = copy($source_file_path, $upload_file_path . $current_id . "_" . $file_name);
	//Check wheather the copy process is completed successfully or not. if failed no need to put entry in attachment table
	if ($status) {
		$query1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
		$params1 = array($current_id, $current_user->id, $ownerid, $module . " Attachment", $obj->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
		$adb->pquery($query1, $params1);

		$query2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?,?,?,?,?)";
		$params2 = array($current_id, $file_name, $obj->column_fields['description'], 'pdf', $upload_file_path);
		$result = $adb->pquery($query2, $params2);

		$query3 = 'insert into vtiger_seattachmentsrel values(?,?)';
		$adb->pquery($query3, array($id, $current_id));

		// Delete the file that was copied
		\vtlib\Deprecated::checkFileAccessForDeletion($source_file_path);
		unlink($source_file_path);

		return true;
	} else {
		$log->debug("pdf not attached");
		return false;
	}
}

//this function check email fields profile permission as well as field access permission
function emails_checkFieldVisiblityPermission($fieldname, $mode = 'readonly')
{
	$current_user = vglobal('current_user');
	$ret = getFieldVisibilityPermission('Emails', $current_user->id, $fieldname, $mode);
	return $ret;
}
