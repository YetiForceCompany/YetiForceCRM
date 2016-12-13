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

	public $table_name = "vtiger_activity";
	public $table_index = 'activityid';
	public $reminder_table = 'vtiger_activity_reminder';
	public $tab_name = Array('vtiger_crmentity', 'vtiger_activity', 'vtiger_activitycf');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid', 'vtiger_activity_reminder' => 'activity_id', 'vtiger_recurringevents' => 'activityid', 'vtiger_activitycf' => 'activityid');
	public $column_fields = [];
	public $sortby_fields = Array('subject', 'due_date', 'date_start', 'smownerid', 'activitytype', 'lastname'); //Sorting is added for due date and start date
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'activitytype', 'date_start', 'due_date', 'visibility', 'assigned_user_id'];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'Type' => 'activitytype',
		'Subject' => 'subject',
		'Related to' => 'link',
		'Start Date & Time' => 'date_start',
		'End Date & Time' => 'due_date',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_activitycf', 'activityid');
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
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
	public $range_fields = Array(
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
	public $list_fields_name = Array(
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
	public $list_link_field = 'subject';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';

	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calendar');
	}

	/**
	 * Function to handle module specific operations when saving a entity
	 * @param string $module
	 */
	public function save_module($module)
	{
		$recordId = $this->id;
		$recurType = $this->column_fields['recurringtype'];

		if ((empty($recurType) || $recurType === '--None--') && $this->mode === 'edit') {
			\App\Db::getInstance()->createCommand()->delete('vtiger_recurringevents', ['activityid' => $recordId])->execute();
		}

		//Insert into vtiger_recurring event table
		if (isset($recurType) && !empty($recurType) && $recurType !== '--None--') {
			$recurData = \vtlib\Functions::getRecurringObjValue();
			if (is_object($recurData))
				$this->insertIntoRecurringTable($recurData);
		}
	}

	/** Function to insert values in vtiger_activity_remainder table for the specified module,
	 * @param $table_name -- table name:: Type varchar
	 * @param $module -- module:: Type varchar
	 */
	public function insertIntoReminderTable($table_name, $module, $recurid)
	{

		\App\Log::trace('in insertIntoReminderTable  ' . $table_name . '    module is  ' . $module);
		if (AppRequest::get('set_reminder') == 'Yes') {
			unset($_SESSION['next_reminder_time']);
			\App\Log::trace('set reminder is set');
			$rem_days = AppRequest::get('remdays');
			$rem_hrs = AppRequest::get('remhrs');
			$rem_min = AppRequest::get('remmin');
			$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
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
	 * @param RecurringType $recurObj - Object of class RecurringType
	 */
	public function insertIntoRecurringTable(& $recurObj)
	{
		$db = \App\Db::getInstance();

		$stDate = $recurObj->startdate->get_DB_formatted_date();
		$endDate = $recurObj->enddate->get_DB_formatted_date();
		if (!empty($recurObj->recurringenddate)) {
			$recurringEndDate = $recurObj->recurringenddate->get_DB_formatted_date();
		}
		$type = $recurObj->getRecurringType();
		$flag = true;

		if (AppRequest::get('mode') === 'edit') {
			$activityId = $this->id;

			$data = (new \App\Db\Query())->select(['min_date' => 'recurringdate', 'max_date' => 'recurringdate', 'recurringtype', 'activityid'])->from('vtiger_recurringevents')->where(['activityid' => $activityId])->one();
			if ($data && ($stDate === $data['min_date'] && $endDate === $data['max_date'] && $type == $data['recurringtype'])) {
				if (AppRequest::get('set_reminder') === 'Yes') {
					$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $activityId])->execute();
					$db->createCommand()->delete('vtiger_recurringevents', ['activityid' => $activityId])->execute();
					$flag = true;
				} elseif (AppRequest::get('set_reminder') === 'No') {
					$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $activityId])->execute();
					$flag = false;
				} else
					$flag = false;
			} else {
				$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $activityId])->execute();
				$db->createCommand()->delete('vtiger_recurringevents', ['activityid' => $activityId])->execute();
			}
		}

		$recurFreq = $recurObj->getRecurringFrequency();
		$recurringInfo = $recurObj->getDBRecurringInfoString();

		if ($flag) {
			$currentId = $db->getUniqueID('vtiger_recurringevents', 'recurringid', false);
			$result = $db->createCommand()->insert('vtiger_recurringevents', [
					'recurringid' => $currentId,
					'activityid' => $this->id,
					'recurringdate' => $stDate,
					'recurringtype' => $type,
					'recurringfreq' => $recurFreq,
					'recurringinfo' => $recurringInfo,
					'recurringenddate' => $recurringEndDate
				])->execute();
			unset($_SESSION['next_reminder_time']);
			if (AppRequest::get('set_reminder') === 'Yes') {
				$this->insertIntoReminderTable('vtiger_activity_reminder', $module, $currentId, '');
			}
		}
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

		\App\Log::trace('Entering getSortOrder() method ...');
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		else
			$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '') ? ($_SESSION['ACTIVITIES_SORT_ORDER']) : ($this->default_sort_order));
		\App\Log::trace('Exiting getSortOrder method ...');
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	public function getOrderBy()
	{

		\App\Log::trace("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		else
			$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '') ? ($_SESSION['ACTIVITIES_ORDER_BY']) : ($use_default_order_by));
		\App\Log::trace("Exiting getOrderBy method ...");
		return $order_by;
	}

//calendarsync
	/**
	 * Function to get task count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
	public function getCount($user_name)
	{

		\App\Log::trace("Entering getCount(" . $user_name . ") method ...");
		$query = "select count(*) from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid inner join vtiger_salesmanactivityrel on vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid inner join vtiger_users on vtiger_users.id=vtiger_salesmanactivityrel.smid where user_name=? and vtiger_crmentity.deleted=0 and vtiger_activity.activitytype='Task'";
		$result = $this->db->pquery($query, array($user_name), true, "Error retrieving contacts count");
		$rows_found = $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);

		\App\Log::trace("Exiting getCount method ...");
		return $row["count(*)"];
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

		\App\Log::trace("Entering vtiger_activity_reminder($activityId,$reminderTime,$reminderSent,$recurid,$reminderMode) method ...");
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
		\App\Log::trace('Exiting vtiger_activity_reminder method ...');
	}

	/**
	 * Function to unlink all the dependent entities of the given Entity by Id
	 * @param string $moduleName
	 * @param int $recordId
	 */
	public function deletePerminently($moduleName, $recordId)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_activity_reminder', ['activity_id' => $recordId])->execute();
		$db->createCommand()->delete('vtiger_recurringevents', ['activityid' => $recordId])->execute();
		parent::deletePerminently($moduleName, $recordId);
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
		\App\Db::getInstance()->createCommand()
			->update('vtiger_activity_reminder_popup', [
				'status' => 1
				], ['recordid' => $this->id])
			->execute();
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
			$query .= " left join vtiger_crmentity as vtiger_crmentityCalendar on vtiger_crmentityCalendar.crmid=vtiger_activity.activityid and vtiger_crmentityCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsCalendar")) {
			$query .= " 	left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_activitycf")) {
			$query .= " 	left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_activity_reminder")) {
			$query .= " 	left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_recurringevents")) {
			$query .= " 	left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid";
		}
		if ($queryPlanner->requireTable("vtiger_accountRelCalendar")) {
			$query .= " 	left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_leaddetailsRelCalendar")) {
			$query .= " 	left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_activity.link";
		}
		if ($queryPlanner->requireTable("vtiger_troubleticketsRelCalendar")) {
			$query .= " left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_activity.process";
		}
		if ($queryPlanner->requireTable("vtiger_campaignRelCalendar")) {
			$query .= " 	left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_activity.process";
		}
		if ($queryPlanner->requireTable("vtiger_groupsCalendar")) {
			$query .= " left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersCalendar")) {
			$query .= " 	left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByCalendar")) {
			$query .= "  left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentityCalendar.modifiedby ";
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
		$tabId = \App\Module::getModuleId($module);
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
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
			$module = \App\Module::getModuleName($tabId);
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
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.tablename', 'vtiger_field.columnname', 'vtiger_tab.name'])
				->from('vtiger_field')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $module, 'relmodule' => $withModule])])
				->createCommand()->query();

		if ($dataReader->count()) {
			$results = $dataReader->readAll();
		} else {
			$dataReader = (new \App\Db\Query())->select(['name' => 'fieldname', 'id' => 'fieldid', 'label' => 'fieldlabel', 'column' => 'columnname', 'table' => 'tablename', 'vtiger_field.*'])
					->from('vtiger_field')
					->where(['uitype' => [66, 67, 68], 'tabid' => App\Module::getModuleId($module)])
					->createCommand()->query();
			while ($row = $dataReader->read()) {
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
		foreach ($results as $row) {
			App\Db::getInstance()->createCommand()
				->update($row['tablename'], [$row['columnname'] => 0], [$row['columnname'] => $withCrmid, CRMEntity::getInstance($row['name'])->table_index => $crmid])->execute();
		}
	}
}
