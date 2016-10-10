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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Activities/Activity.php,v 1.26 2005/03/26 10:42:13 rank Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** 
 * Contributor(s): YetiForce.com
 */

require_once('modules/Calendar/RenderRelatedListUI.php');
require_once('modules/Calendar/CalendarCommon.php');

// Task is used to store customer information.
class Activity extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_activity";
	var $table_index = 'activityid';
	var $reminder_table = 'vtiger_activity_reminder';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_activity', 'vtiger_activitycf');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid', 'vtiger_activity_reminder' => 'activity_id', 'vtiger_recurringevents' => 'activityid', 'vtiger_activitycf' => 'activityid');
	var $column_fields = [];
	var $sortby_fields = Array('subject', 'due_date', 'date_start', 'smownerid', 'activitytype', 'lastname'); //Sorting is added for due date and start date
	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_activitycf', 'activityid');
	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
		'Close' => Array('activity' => 'status'),
		'Type' => Array('activity' => 'activitytype'),
		'Subject' => Array('activity' => 'subject'),
		'Related to' => Array('activity' => 'link'),
		'Start Date' => Array('activity' => 'date_start'),
		'Start Time' => Array('activity', 'time_start'),
		'End Date' => Array('activity' => 'due_date'),
		'End Time' => Array('activity', 'time_end'),
		'Recurring Type' => Array('recurringevents' => 'recurringtype'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $range_fields = Array(
		'name',
		'date_modified',
		'start_date',
		'id',
		'status',
		'date_due',
		'time_start',
		'description',
		'priority',
		'duehours',
		'dueminutes',
		'location'
	);
	var $list_fields_name = Array(
		'Close' => 'status',
		'Type' => 'activitytype',
		'Subject' => 'subject',
		'Related to' => 'link',
		'Start Date & Time' => 'date_start',
		'End Date & Time' => 'due_date',
		'Recurring Type' => 'recurringtype',
		'Assigned To' => 'assigned_user_id',
		'Start Date' => 'date_start',
		'Start Time' => 'time_start',
		'End Date' => 'due_date',
		'End Time' => 'time_end');
	var $list_link_field = 'subject';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

	public function __construct()
	{
		$this->log = LoggerManager::getLogger('Calendar');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calendar');
	}

	public function save_module($module)
	{
		$adb = PearDatabase::getInstance();
		//Handling module specific save
		$recordId = $this->id;
		$recur_type = '';
		if (($recur_type == "--None--" || $recur_type == '') && $this->mode == "edit") {
			$sql = 'delete  from vtiger_recurringevents where activityid=?';
			$adb->pquery($sql, array($this->id));
		}
		//Handling for recurring type
		//Insert into vtiger_recurring event table
		if (isset($this->column_fields['recurringtype']) && $this->column_fields['recurringtype'] != '' && $this->column_fields['recurringtype'] != '--None--') {
			$recur_type = trim($this->column_fields['recurringtype']);
			$recur_data = \vtlib\Functions::getRecurringObjValue();
			if (is_object($recur_data))
				$this->insertIntoRecurringTable($recur_data);
		}

		//Insert into vtiger_activity_remainder table

		$this->insertIntoReminderTable('vtiger_activity_reminder', $module, "");

		//Handling for invitees
		$this->insertIntoInviteeTable($module);

		//Inserting into sales man activity rel
		$adb->pquery('UPDATE vtiger_activity SET smownerid = ? WHERE `activityid` = ?;', array($this->column_fields['assigned_user_id'], $recordId));
		$this->insertIntoActivityReminderPopup($module);
	}

	/** Function to insert values in vtiger_activity_reminder_popup table for the specified module
	 * @param $cbmodule -- module:: Type varchar
	 */
	public function insertIntoActivityReminderPopup($cbmodule)
	{

		$adb = PearDatabase::getInstance();

		$cbrecord = $this->id;
		unset($_SESSION['next_reminder_time']);
		if (isset($cbmodule) && isset($cbrecord)) {
			$cbdate = getValidDBInsertDateValue($this->column_fields['date_start']);
			$cbtime = $this->column_fields['time_start'];

			$reminderQuery = 'SELECT reminderid FROM vtiger_activity_reminder_popup WHERE semodule = ? and recordid = ?';
			$reminderParams = [$cbmodule, $cbrecord];
			$reminderidres = $adb->pquery($reminderQuery, $reminderParams);

			$reminderid = null;
			if ($reminderidres->rowCount() > 0) {
				$reminderid = $adb->query_result($reminderidres, 0, 'reminderid');
			}

			$currentStates = Calendar_Module_Model::getComponentActivityStateLabel('current');
			$state = Calendar_Module_Model::getCalendarState($this->column_fields);
			if (in_array($state, $currentStates)) {
				$status = 0;
			} else {
				$status = 1;
			}

			if (isset($reminderid)) {
				$adb->update('vtiger_activity_reminder_popup', [
					'date_start' => $cbdate,
					'time_start' => $cbtime,
					'status' => $status,
					], 'reminderid = ?', [$reminderid]
				);
			} else {
				$adb->insert('vtiger_activity_reminder_popup', [
					'recordid' => $cbrecord,
					'semodule' => $cbmodule,
					'date_start' => $cbdate,
					'time_start' => $cbtime,
					'status' => $status,
				]);
			}
		}
	}

	/** Function to insert values in vtiger_activity_remainder table for the specified module,
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoReminderTable($table_name, $module, $recurid)
	{
		$log = LoggerManager::getInstance();
		$log->info('in insertIntoReminderTable  ' . $table_name . '    module is  ' . $module);
		if (AppRequest::get('set_reminder') == 'Yes') {
			unset($_SESSION['next_reminder_time']);
			$log->debug('set reminder is set');
			$rem_days = AppRequest::get('remdays');
			$log->debug('rem_days is ' . $rem_days);
			$rem_hrs = AppRequest::get('remhrs');
			$log->debug('rem_hrs is ' . $rem_hrs);
			$rem_min = AppRequest::get('remmin');
			$log->debug('rem_minutes is ' . $rem_min);
			$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
			$log->debug('reminder_time is ' . $reminder_time);
			if ($recurid == '') {
				if (AppRequest::get('mode') == 'edit') {
					$this->activity_reminder($this->id, $reminder_time, 0, $recurid, 'edit');
				} else {
					$this->activity_reminder($this->id, $reminder_time, 0, $recurid, '');
				}
			} else {
				$this->activity_reminder($this->id, $reminder_time, 0, $recurid, '');
			}
		} elseif (AppRequest::get('set_reminder') == 'No') {
			$this->activity_reminder($this->id, '0', 0, $recurid, 'delete');
		}
	}

	// Code included by Jaguar - starts
	/** Function to insert values in vtiger_recurringevents table for the specified tablename,module
	 * @param $recurObj -- Recurring Object:: Type varchar
	 */
	public function insertIntoRecurringTable(& $recurObj)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$st_date = $recurObj->startdate->get_DB_formatted_date();
		$end_date = $recurObj->enddate->get_DB_formatted_date();
		if (!empty($recurObj->recurringenddate)) {
			$recurringenddate = $recurObj->recurringenddate->get_DB_formatted_date();
		}
		$type = $recurObj->getRecurringType();
		$flag = 'true';

		if (AppRequest::get('mode') == 'edit') {
			$activity_id = $this->id;

			$sql = 'select min(recurringdate) AS min_date,max(recurringdate) AS max_date, recurringtype, activityid from vtiger_recurringevents where activityid=? group by activityid, recurringtype';
			$result = $adb->pquery($sql, array($activity_id));
			$noofrows = $adb->num_rows($result);
			for ($i = 0; $i < $noofrows; $i++) {
				$recur_type_b4_edit = $adb->query_result($result, $i, "recurringtype");
				$date_start_b4edit = $adb->query_result($result, $i, "min_date");
				$end_date_b4edit = $adb->query_result($result, $i, "max_date");
			}
			if (($st_date == $date_start_b4edit) && ($end_date == $end_date_b4edit) && ($type == $recur_type_b4_edit)) {
				if (AppRequest::get('set_reminder') == 'Yes') {
					$sql = 'delete from vtiger_activity_reminder where activity_id=?';
					$adb->pquery($sql, array($activity_id));
					$sql = 'delete  from vtiger_recurringevents where activityid=?';
					$adb->pquery($sql, array($activity_id));
					$flag = "true";
				} elseif (AppRequest::get('set_reminder') == 'No') {
					$sql = 'delete  from vtiger_activity_reminder where activity_id=?';
					$adb->pquery($sql, array($activity_id));
					$flag = "false";
				} else
					$flag = "false";
			}
			else {
				$sql = 'delete from vtiger_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$sql = 'delete  from vtiger_recurringevents where activityid=?';
				$adb->pquery($sql, array($activity_id));
			}
		}

		$recur_freq = $recurObj->getRecurringFrequency();
		$recurringinfo = $recurObj->getDBRecurringInfoString();

		if ($flag == "true") {
			$max_recurid_qry = 'select max(recurringid) AS recurid from vtiger_recurringevents;';
			$result = $adb->pquery($max_recurid_qry, []);
			$noofrows = $adb->num_rows($result);
			$recur_id = 0;
			if ($noofrows > 0) {
				$recur_id = $adb->query_result($result, 0, "recurid");
			}
			$current_id = $recur_id + 1;
			$recurring_insert = "insert into vtiger_recurringevents values (?,?,?,?,?,?,?)";
			$rec_params = array($current_id, $this->id, $st_date, $type, $recur_freq, $recurringinfo, $recurringenddate);
			$adb->pquery($recurring_insert, $rec_params);
			unset($_SESSION['next_reminder_time']);
			if (AppRequest::get('set_reminder') == 'Yes') {
				$this->insertIntoReminderTable("vtiger_activity_reminder", $module, $current_id, '');
			}
		}
	}

	/** Function to insert values in u_yf_activity_invitation table for the specified module,tablename ,invitees_array
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 * @param $invitees_array Array
	 */
	public function insertIntoInviteeTable($module)
	{
		$log = LoggerManager::getInstance();
		if (!AppRequest::has('inviteesid')) {
			$log->fatal('No invitations in request, Exiting insertIntoInviteeTable method ...');
			return;
		}
		$log->debug("Entering insertIntoInviteeTable($module) method ...");

		$inviteesRequest = AppRequest::get('inviteesid');
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM u_yf_activity_invitation WHERE activityid=?', [$this->id]);
		$invities = [];
		while ($row = $db->getRow($result)) {
			$invities[$row['inviteesid']] = $row;
		}
		if (!empty($inviteesRequest)) {
			foreach ($inviteesRequest as &$invitation) {
				if (isset($invities[$invitation[2]])) {
					unset($invities[$invitation[2]]);
				} else {
					$db->insert('u_yf_activity_invitation', [
						'email' => $invitation[0],
						'crmid' => $invitation[1],
						'activityid' => $this->id
					]);
				}
			}
		}
		foreach ($invities as &$invitation) {
			$db->delete('u_yf_activity_invitation', 'inviteesid = ?', [$invitation['inviteesid']]);
		}
		$log->debug('Exiting insertIntoInviteeTable method ...');
	}

	/** Function to insert values in vtiger_salesmanactivityrel table for the specified module
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoSmActivityRel($module)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		if ($this->mode == 'edit') {
			$sql = "delete from vtiger_salesmanactivityrel where activityid=?";
			$adb->pquery($sql, array($this->id));
		}

		$userName = vtlib\Functions::getUserName($this->column_fields['assigned_user_id']);
		if (!empty($userName)) {
			$sql_qry = "insert into vtiger_salesmanactivityrel (smid,activityid) values(?,?)";
			$adb->pquery($sql_qry, array($this->column_fields['assigned_user_id'], $this->id));

			if (!AppRequest::isEmpty('inviteesid')) {
				$invitees = AppRequest::get('inviteesid');
				foreach ($invitees as $inviteeid) {
					if ($inviteeid != '') {
						$resultcheck = $adb->pquery("select * from vtiger_salesmanactivityrel where activityid=? and smid=?", array($this->id, $inviteeid));
						if ($adb->num_rows($resultcheck) != 1) {
							$query = "insert into vtiger_salesmanactivityrel values(?,?)";
							$adb->pquery($query, array($inviteeid, $this->id));
						}
					}
				}
			}
		}
	}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName)
	{
		if ($tableName == "vtiger_activity_reminder")
			return 'LEFT JOIN';
		return parent::getJoinClause($tableName);
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	public function getSortOrder()
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering getSortOrder() method ...');
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		else
			$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '') ? ($_SESSION['ACTIVITIES_SORT_ORDER']) : ($this->default_sort_order));
		$log->debug('Exiting getSortOrder method ...');
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	public function getOrderBy()
	{
		$log = LoggerManager::getInstance();
		$log->debug("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		else
			$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '') ? ($_SESSION['ACTIVITIES_ORDER_BY']) : ($use_default_order_by));
		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}

	// Mike Crowe Mod --------------------------------------------------------
//Function Call for Related List -- Start
	/**
	 * Function to get Activity related Contacts
	 * @param  integer   $id      - activityid
	 * returns related Contacts record in array format
	 */
	public function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = LoggerManager::getInstance();
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_contacts(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);
		$returnset = '&return_module=' . $this_module . '&return_action=DetailView&activity_mode=Events&return_id=' . $id;

		$search_string = '';
		$button = '';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
		}

		$query = 'select vtiger_users.user_name,vtiger_contactdetails.parentid,vtiger_contactdetails.contactid, vtiger_contactdetails.firstname,vtiger_contactdetails.lastname, vtiger_contactdetails.department, vtiger_contactdetails.title, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime '
			. 'from vtiger_contactdetails '
			. 'inner join vtiger_activity on vtiger_activity.link=vtiger_contactdetails.contactid '
			. 'inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid '
			. 'left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid '
			. 'left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid '
			. 'where vtiger_activity.activityid=' . $id . ' and vtiger_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = [];
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Activity related Users
	 * @param  integer   $id      - activityid
	 * returns related Users record in array format
	 */
	public function get_users($id)
	{
		$log = vglobal('log');
		$log->debug("Entering get_contacts(" . $id . ") method ...");

		$focus = new Users();

		$button = '<input title="Change" accessKey="" tabindex="2" type="button" class="crmbutton small edit"
					value="' . \includes\Language::translate('LBL_SELECT_USER_BUTTON_LABEL') . '" name="button" LANGUAGE=javascript
					onclick=\'return window.open("index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id=' . $id . '&recordid=' . $id . '","test","width=640,height=525,resizable=0,scrollbars=0")\';>';

		$returnset = '&return_module=Calendar&return_action=CallRelatedList&return_id=' . $id;

		$query = 'SELECT vtiger_users.id, vtiger_users.first_name,vtiger_users.last_name, vtiger_users.user_name, vtiger_users.email1, vtiger_users.status, vtiger_users.is_admin, vtiger_user2role.roleid, vtiger_users.phone_home, vtiger_users.phone_work, vtiger_users.phone_mobile, vtiger_users.phone_other, vtiger_users.phone_fax,vtiger_activity.date_start,vtiger_activity.due_date,vtiger_activity.time_start,vtiger_activity.duration_hours,vtiger_activity.duration_minutes from vtiger_users inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.smid=vtiger_users.id  inner join vtiger_activity on vtiger_activity.activityid=vtiger_salesmanactivityrel.activityid inner join vtiger_user2role on vtiger_user2role.userid=vtiger_users.id where vtiger_activity.activityid= %d';
		$query = sprintf($query, $id);
		$return_data = GetRelatedList('Calendar', 'Users', $focus, $query, $button, $returnset);

		if ($return_data == null)
			$return_data = [];
		$return_data['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_users method ...");
		return $return_data;
	}

//calendarsync
	/**
	 * Function to get meeting count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
	public function getCount_Meeting($user_name)
	{
		$log = vglobal('log');
		$log->debug("Entering getCount_Meeting(" . $user_name . ") method ...");
		$query = "select count(*) from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid where user_name=? and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting'";
		$result = $this->db->pquery($query, array($user_name), true, "Error retrieving contacts count");
		$rows_found = $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);
		$log->debug("Exiting getCount_Meeting method ...");
		return $row["count(*)"];
	}

	public function get_calendars($user_name, $from_index, $offset)
	{
		$log = vglobal('log');
		$log->debug("Entering get_calendars(" . $user_name . "," . $from_index . "," . $offset . ") method ...");
		$query = 'select vtiger_activity.location as location,vtiger_activity.duration_hours as duehours, vtiger_activity.duration_minutes as dueminutes,vtiger_activity.time_start as time_start, vtiger_activity.subject as name,vtiger_crmentity.modifiedtime as date_modified, vtiger_activity.date_start start_date,vtiger_activity.activityid as id,vtiger_activity.status as status, vtiger_crmentity.description as description, vtiger_activity.priority as vtiger_priority, vtiger_activity.due_date as date_due ,vtiger_contactdetails.firstname cfn, vtiger_contactdetails.lastname cln '
			. 'from vtiger_activity inner '
			. 'join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid '
			. 'inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid '
			. 'left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_activity.link '
			. 'inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid '
			. "where user_name='" . $user_name . "' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting' limit " . $from_index . "," . $offset;
		$log->debug("Exiting get_calendars method ...");
		return $this->process_list_query1($query);
	}

//calendarsync
	/**
	 * Function to get task count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
	public function getCount($user_name)
	{
		$log = vglobal('log');
		$log->debug("Entering getCount(" . $user_name . ") method ...");
		$query = "select count(*) from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid where user_name=? and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task'";
		$result = $this->db->pquery($query, array($user_name), true, "Error retrieving contacts count");
		$rows_found = $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);

		$log->debug("Exiting getCount method ...");
		return $row["count(*)"];
	}

	/**
	 * Function to get list of task for user with given limit
	 * @param  string   $user_name        - User Name
	 * @param  string   $from_index       - query string
	 * @param  string   $offset           - query string
	 * returns tasks in array format
	 */
	public function get_tasks($user_name, $from_index, $offset)
	{
		$log = vglobal('log');
		$log->debug('Entering get_tasks(' . $user_name . ',' . $from_index . ',' . $offset . ') method ...');
		$query = 'select vtiger_activity.subject as name,vtiger_crmentity.modifiedtime as date_modified, vtiger_activity.date_start start_date,vtiger_activity.activityid as id,vtiger_activity.status as status, vtiger_crmentity.description as description, vtiger_activity.priority as priority, vtiger_activity.due_date as date_due ,vtiger_contactdetails.firstname cfn, vtiger_contactdetails.lastname cln '
			. 'from vtiger_activity '
			. 'inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid '
			. 'inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid '
			. 'left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_activity.link '
			. "inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid where user_name='" . $user_name . "' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task' limit " . $from_index . "," . $offset;
		$log->debug('Exiting get_tasks method ...');
		return $this->process_list_query1($query);
	}

	/**
	 * Function to process the activity list query
	 * @param  string   $query     - query string
	 * return  array    $response  - activity lists
	 */
	public function process_list_query1($query)
	{
		$log = vglobal('log');
		$log->debug("Entering process_list_query1(" . $query . ") method ...");
		$result = & $this->db->query($query, true, "Error retrieving $this->object_name list: ");
		$list = [];
		$rows_found = $this->db->getRowCount($result);
		if ($rows_found != 0) {
			$task = [];
			for ($index = 0, $row = $this->db->fetchByAssoc($result, $index); $row && $index < $rows_found; $index++, $row = $this->db->fetchByAssoc($result, $index)) {
				foreach ($this->range_fields as $columnName) {
					if (isset($row[$columnName])) {
						if ($columnName == 'time_start') {
							$startDate = new DateTimeField($row['date_start'] . ' ' .
								$row[$columnName]);
							$task[$columnName] = $startDate->getDBInsertTimeValue();
						} else {
							$task[$columnName] = $row[$columnName];
						}
					} else {
						$task[$columnName] = "";
					}
				}

				$task[contact_name] = return_name($row, 'cfn', 'cln');

				$list[] = $task;
			}
		}

		$response = [];
		$response['list'] = $list;
		$response['row_count'] = $rows_found;
		$response['next_offset'] = $next_offset;
		$response['previous_offset'] = $previous_offset;


		$log->debug("Exiting process_list_query1 method ...");
		return $response;
	}

	/**
	 * Function to get reminder for activity
	 * @param  integer   $activityId     - activity id
	 * @param  string    $reminderTime   - reminder time
	 * @param  integer   $reminderSent   - 0 or 1
	 * @param  integer   $recurid         - recuring eventid
	 * @param  string    $reminderMode    - string like 'edit'
	 */
	public function activity_reminder($activityId, $reminderTime, $reminderSent = 0, $recurid, $reminderMode = '')
	{
		$log = LoggerManager::getInstance();
		$log->debug("Entering vtiger_activity_reminder($activityId,$reminderTime,$reminderSent,$recurid,$reminderMode) method ...");
		//Check for vtiger_activityid already present in the reminder_table
		$query = sprintf('SELECT activity_id FROM %s WHERE activity_id = ?', $this->reminder_table);
		$resultExist = $this->db->pquery($query, array($activityId));

		if ($reminderMode == 'edit') {
			if ($this->db->getRowCount($resultExist) > 0) {
				$this->db->update($this->reminder_table, [
					'reminder_time' => $reminderTime,
					'reminder_sent' => $reminderSent
					], 'activity_id = ?', [$activityId]
				);
			} else {
				$this->db->insert($this->reminder_table, [
					'activity_id' => $activityId,
					'reminder_time' => $reminderTime,
					'reminder_sent' => 0,
					'recurringid' => $recurid
				]);
			}
		} elseif (($reminderMode == 'delete') && ($this->db->getRowCount($resultExist) > 0)) {
			$this->db->delete($this->reminder_table, 'activity_id = ?', [$activityId]);
		} else {
			if (AppRequest::get('set_reminder') == 'Yes') {
				$this->db->insert($this->reminder_table, [
					'activity_id' => $activityId,
					'reminder_time' => $reminderTime,
					'reminder_sent' => 0,
					'recurringid' => $recurid
				]);
			}
		}
		$log->debug('Exiting vtiger_activity_reminder method ...');
	}

	//Used for vtigerCRM Outlook Add-In
	/**
	 * Function to get tasks to display in outlookplugin
	 * @param   string    $username     -  User name
	 * return   string    $query        -  sql query
	 */
	public function get_tasksforol($username)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug("Entering get_tasksforol(" . $username . ") method ...");
		$current_user = vglobal('current_user');
		require_once("modules/Users/Users.php");
		$seed_user = new Users();
		$user_id = $seed_user->retrieve_user_id($username);
		$current_user = $seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select tablename,columnname from vtiger_field where tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.presence in (0,2)";
			$params1 = [];
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = [];
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params1, $profileList);
			}
		}
		$result1 = $adb->pquery($sql1, $params1);
		for ($i = 0; $i < $adb->num_rows($result1); $i++) {
			$permitted_lists[] = $adb->query_result($result1, $i, 'tablename');
			$permitted_lists[] = $adb->query_result($result1, $i, 'columnname');
			/* if($adb->query_result($result1,$i,'columnname') == "parentid")
			  {
			  $permitted_lists[] = 'vtiger_account';
			  $permitted_lists[] = 'accountname';
			  } */
		}
		$permitted_lists = array_chunk($permitted_lists, 2);
		$column_table_lists = [];
		for ($i = 0; $i < count($permitted_lists); $i++) {
			$column_table_lists[] = implode(".", $permitted_lists[$i]);
		}

		$query = "select vtiger_activity.activityid as taskid, %s from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
			 inner join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
			 where vtiger_users.user_name='%s' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task'";
		$columnTableList = implode(',', $column_table_lists, $username);
		$query = sprintf($query, $columnTableList);
		$log->debug("Exiting get_tasksforol method ...");
		return $query;
	}

	/**
	 * Function to get calendar query for outlookplugin
	 * @param   string    $username     -  User name                                                                            * return   string    $query        -  sql query                                                                            */
	public function get_calendarsforol($user_name)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering get_calendarsforol(" . $user_name . ") method ...");
		$current_user = vglobal('current_user');
		require_once("modules/Users/Users.php");
		$seed_user = new Users();
		$user_id = $seed_user->retrieve_user_id($user_name);
		$current_user = $seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql1 = "select tablename,columnname from vtiger_field where tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.presence in (0,2)";
			$params1 = [];
		} else {
			$profileList = getCurrentUserProfileList();
			$sql1 = "select tablename,columnname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid=9 and tablename <> 'vtiger_recurringevents' and tablename <> 'vtiger_activity_reminder' and vtiger_field.displaytype in (1,2,4,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params1 = [];
			if (count($profileList) > 0) {
				$sql1 .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params1, $profileList);
			}
		}
		$result1 = $adb->pquery($sql1, $params1);
		for ($i = 0; $i < $adb->num_rows($result1); $i++) {
			$permitted_lists[] = $adb->query_result($result1, $i, 'tablename');
			$permitted_lists[] = $adb->query_result($result1, $i, 'columnname');
			if ($adb->query_result($result1, $i, 'columnname') == "date_start") {
				$permitted_lists[] = 'vtiger_activity';
				$permitted_lists[] = 'time_start';
			}
			if ($adb->query_result($result1, $i, 'columnname') == "due_date") {
				$permitted_lists[] = 'vtiger_activity';
				$permitted_lists[] = 'time_end';
			}
		}
		$permitted_lists = array_chunk($permitted_lists, 2);
		$column_table_lists = [];
		for ($i = 0; $i < count($permitted_lists); $i++) {
			$column_table_lists[] = implode(".", $permitted_lists[$i]);
		}

		$query = "select vtiger_activity.activityid as clndrid, %s from vtiger_activity
				inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid
				inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid
				left join vtiger_contactdetails on vtiger_contactdetails.contactid=vtiger_activity.link
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
				where vtiger_users.user_name='%s' and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Meeting'";
		$columnTableLists = implode(',', $column_table_lists);
		$query = sprintf($query, $columnTableLists, $user_name);
		$log->debug("Exiting get_calendarsforol method ...");
		return $query;
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	public function unlinkDependencies($module, $id)
	{
		$log = vglobal('log');

		$sql = 'DELETE FROM vtiger_activity_reminder WHERE activity_id=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM vtiger_recurringevents WHERE activityid=?';
		$this->db->pquery($sql, array($id));

		parent::unlinkDependencies($module, $id);
	}

	/**
	 * this function sets the status flag of activity to true or false depending on the status passed to it
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	public function setActivityReminder($status)
	{
		$adb = PearDatabase::getInstance();
		if ($status == "on") {
			$flag = 0;
		} elseif ($status == "off") {
			$flag = 1;
		} else {
			return false;
		}
		$sql = "update vtiger_activity_reminder_popup set status=1 where recordid=?";
		$adb->pquery($sql, array($this->id));
		return true;
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
		$matrix->setDependency('vtiger_crmentityCalendar', array('vtiger_groupsCalendar', 'vtiger_usersCalendar', 'vtiger_lastModifiedByCalendar'));
		$matrix->setDependency('vtiger_activity', array('vtiger_activitycf', 'vtiger_activity_reminder', 'vtiger_recurringevents'));

		if (!$queryPlanner->requireTable('vtiger_activity', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_activity", "activityid", $queryPlanner);

		if ($queryPlanner->requireTable("vtiger_crmentityCalendar", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityCalendar on vtiger_crmentityCalendar.crmid=vtiger_activity.activityid and vtiger_crmentityCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsCalendar")) {
			$query .=" 	left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_activitycf")) {
			$query .=" 	left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_activity_reminder")) {
			$query .=" 	left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_recurringevents")) {
			$query .=" 	left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_accountRelCalendar")) {
			$query .=" 	left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_leaddetailsRelCalendar")) {
			$query .=" 	left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_troubleticketsRelCalendar")) {
			$query .=" left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_activity.process";
		}
		if ($queryPlanner->requireTable("vtiger_campaignRelCalendar")) {
			$query .=" 	left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_activity.process";
		}
		if ($queryPlanner->requireTable("vtiger_groupsCalendar")) {
			$query .=" left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersCalendar")) {
			$query .=" 	left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByCalendar")) {
			$query .="  left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentityCalendar.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyCalendar")) {
			$query .= " left join vtiger_users as vtiger_createdbyCalendar on vtiger_createdbyCalendar.id = vtiger_crmentityCalendar.smcreatorid ";
		}
		return $query;
	}

	public function getNonAdminAccessControlQuery($module, $user, $scope = '')
	{
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = \includes\Modules::getModuleId($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . $user->id . '_t' . $tabId;
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			$this->setupTemporaryTable($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);

			$sharedUsers = $this->getListViewAccessibleUsers($user->id);
			// we need to include group id's in $sharedUsers list to get the current user's group records
			if ($current_user_groups) {
				$sharedUsers = $sharedUsers . ',' . implode(',', $current_user_groups);
			}
			$query = " INNER JOIN $tableName $tableName$scope ON ($tableName$scope.id = " .
				"vtiger_crmentity$scope.smownerid and $tableName$scope.shared=0 and $tableName$scope.id IN ($sharedUsers)) ";
		}
		return $query;
	}

	/**
	 * To get non admin access query for Reports generation
	 * @param type $tableName
	 * @param type $tabId
	 * @param type $user
	 * @param type $parent_roles
	 * @param type $groups
	 * @return $query
	 */
	public function getReportsNonAdminAccessControlQuery($tableName, $tabId, $user, $parent_roles, $groups)
	{
		$sharedUsers = $this->getListViewAccessibleUsers($user->id);
		$this->setupTemporaryTable($tableName, $tabId, $user, $parent_roles, $groups);
		$query = "SELECT id FROM $tableName WHERE $tableName.shared=0 && $tableName.id IN ($sharedUsers)";
		return $query;
	}

	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = getTabname($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared " .
			"int(1) default 0) ignore " . $query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, []);
		if (is_object($result)) {
			$query = "REPLACE INTO $tableName (id) SELECT userid as id FROM vtiger_sharedcalendar WHERE sharedid = ?";
			$result = $db->pquery($query, array($user->id));

			//For newly created users, entry will not be there in vtiger_sharedcalendar table
			//so, consider the users whose having the calendarsharedtype is public
			$query = "REPLACE INTO $tableName (id) SELECT id FROM vtiger_users WHERE calendarsharedtype = ?";
			$result = $db->pquery($query, array('public'));

			if (is_object($result)) {
				return true;
			}
		}
		return false;
	}

	protected function getListViewAccessibleUsers($sharedid)
	{
		$db = PearDatabase::getInstance();
		;
		$query = "SELECT vtiger_users.id as userid FROM vtiger_sharedcalendar
					RIGHT JOIN vtiger_users ON vtiger_sharedcalendar.userid=vtiger_users.id and status= 'Active'
					WHERE sharedid=? || (vtiger_users.status='Active' && vtiger_users.calendarsharedtype='public' && vtiger_users.id <> ?);";
		$result = $db->pquery($query, array($sharedid, $sharedid));
		$rows = $db->num_rows($result);
		if ($db->num_rows($result) != 0) {
			for ($j = 0; $j < $db->num_rows($result); $j++) {
				$userid[] = $db->query_result($result, $j, 'userid');
			}
			$shared_ids = implode(",", $userid);
		}
		$userid[] = $sharedid;
		$shared_ids = implode(",", $userid);
		return $shared_ids;
	}

	public function deleteRelatedDependent($module, $crmid, $withModule, $withCrmid)
	{
		$fieldRes = $this->db->pquery('SELECT vtiger_field.tabid, vtiger_field.tablename, vtiger_field.columnname, vtiger_tab.name FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.`tabid` = vtiger_field.`tabid` WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? && relmodule=?)', [$module, $withModule]);
		if ($fieldRes->rowCount()) {
			$results = $this->db->getArray($fieldRes);
		} else {
			$fieldRes = $this->db->pquery('SELECT fieldname AS `name`, fieldid AS id, fieldlabel AS label, columnname AS `column`, tablename AS `table`, vtiger_field.*  FROM vtiger_field WHERE `uitype` IN (66,67,68) && `tabid` = ?;', [vtlib\Functions::getModuleId($module)]);
			while ($row = $this->db->getRow($fieldRes)) {
				$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $module);
				$fieldModel = new $className();
				foreach ($row as $properName => $propertyValue) {
					$fieldModel->$properName = $propertyValue;
				}
				$moduleList = $fieldModel->getUITypeModel()->getReferenceList();
				if (!empty($moduleList) && in_array($withModule, $moduleList)) {
					$row['name'] = $module;
					$results[] = $row;
					break;
				}
			}
		}
		foreach ($results as $result) {
			$focusObj = CRMEntity::getInstance($row['name']);
			$columnName = $row['columnname'];
			$columns = [$columnName => null];
			$where = "$columnName = ? && $focusObj->table_index = ?";
			$this->db->update($row['tablename'], $columns, $where, [$withCrmid, $crmid]);
		}
	}
}
