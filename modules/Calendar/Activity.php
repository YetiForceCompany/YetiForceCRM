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

// Task is used to store customer information.
class Activity extends CRMEntity
{

	public $table_name = "vtiger_activity";
	public $table_index = 'activityid';
	public $reminder_table = 'vtiger_activity_reminder';
	public $tab_name = ['vtiger_crmentity', 'vtiger_activity', 'vtiger_activitycf'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid', 'vtiger_activity_reminder' => 'activity_id', 'vtiger_activitycf' => 'activityid'];
	public $column_fields = [];
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = ['assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name'];

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
	public $customFieldTable = ['vtiger_activitycf', 'activityid'];
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Close' => ['activity' => 'status'],
		'Type' => ['activity' => 'activitytype'],
		'Subject' => ['activity' => 'subject'],
		'Related to' => ['activity' => 'link'],
		'Start Date' => ['activity' => 'date_start'],
		'Start Time' => ['activity', 'time_start'],
		'End Date' => ['activity' => 'due_date'],
		'End Time' => ['activity', 'time_end'],
		'Assigned To' => ['crmentity' => 'smownerid']
	];
	public $range_fields = [
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
	];
	public $list_fields_name = [
		'Close' => 'status',
		'Type' => 'activitytype',
		'Subject' => 'subject',
		'Related to' => 'link',
		'Start Date & Time' => 'date_start',
		'End Date & Time' => 'due_date',
		'Assigned To' => 'assigned_user_id',
		'Start Date' => 'date_start',
		'Start Time' => 'time_start',
		'End Date' => 'due_date',
		'End Time' => 'time_end'];
	public $list_link_field = 'subject';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = 'date_start';
	public $default_sort_order = 'ASC';

	public function __construct()
	{
		$this->db = PearDatabase::getInstance();
		$this->column_fields = vtlib\Deprecated::getColumnFields('Calendar');
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

	/**
	 * Function to get reminder for activity
	 * @param  integer   $activityId     - activity id
	 * @param  string    $reminderTime   - reminder time
	 * @param  integer   $reminderSent   - 0 or 1
	 * @param  integer   $recurid         - recuring eventid
	 * @param  string    $reminderMode    - string like 'edit'
	 */
	public function activityReminder($activityId, $reminderTime, $reminderSent = 0, $recurid, $reminderMode = '')
	{

		\App\Log::trace("Entering vtiger_activity_reminder($activityId,$reminderTime,$reminderSent,$recurid,$reminderMode) method ...");
		//Check for vtiger_activityid already present in the reminder_table
		$query = sprintf('SELECT activity_id FROM %s WHERE activity_id = ?', $this->reminder_table);
		$resultExist = $this->db->pquery($query, [$activityId]);

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
				]);
			}
		} elseif (($reminderMode == 'delete') && ($this->db->getRowCount($resultExist) > 0)) {
			$this->db->delete($this->reminder_table, 'activity_id = ?', [$activityId]);
		} else {
			if (\App\Request::_get('set_reminder') == 'Yes') {
				$this->db->insert($this->reminder_table, [
					'activity_id' => $activityId,
					'reminder_time' => $reminderTime,
					'reminder_sent' => 0,
				]);
			}
		}
		\App\Log::trace('Exiting vtiger_activity_reminder method ...');
	}

	/**
	 * this function sets the status flag of activity to true or false depending on the status passed to it
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	public function setActivityReminder($status)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_activity_reminder_popup', [
				'status' => 1
				], ['recordid' => $this->id])
			->execute();
		return true;
	}

	/**
	 * Function to get the secondary query part of a report
	 * @param string $module
	 * @param string $secmodule
	 * @param ReportRunQueryPlanner $queryPlanner
	 * @return string
	 */
	public function generateReportsSecQuery($module, $secmodule, ReportRunQueryPlanner $queryPlanner)
	{
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityCalendar', ['vtiger_groupsCalendar', 'vtiger_usersCalendar', 'vtiger_lastModifiedByCalendar']);
		$matrix->setDependency('vtiger_activity', ['vtiger_activitycf', 'vtiger_activity_reminder']);

		if (!$queryPlanner->requireTable('vtiger_activity', $matrix)) {
			return '';
		}
		$moduleLevel = App\ModuleHierarchy::getModuleLevel($module);
		if ($moduleLevel === false) {
			$query = $this->getRelationQuery($module, $secmodule, 'vtiger_activity', 'activityid', $queryPlanner);
		} else {
			$field = App\ModuleHierarchy::getMappingRelatedField($module);
			$query = " LEFT JOIN vtiger_activity ON vtiger_activity.$field = vtiger_crmentity.crmid";
		}

		if ($queryPlanner->requireTable('vtiger_crmentityCalendar', $matrix)) {
			$query .= ' left join vtiger_crmentity as vtiger_crmentityCalendar on vtiger_crmentityCalendar.crmid=vtiger_activity.activityid and vtiger_crmentityCalendar.deleted=0';
		}
		if ($queryPlanner->requireTable('vtiger_contactdetailsCalendar')) {
			$query .= ' 	left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_activity.link';
		}
		if ($queryPlanner->requireTable('vtiger_activitycf')) {
			$query .= ' 	left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_activity.activityid';
		}
		if ($queryPlanner->requireTable('vtiger_activity_reminder')) {
			$query .= ' 	left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid';
		}
		if ($queryPlanner->requireTable('vtiger_accountRelCalendar')) {
			$query .= ' 	left join vtiger_account as vtiger_accountRelCalendar on vtiger_accountRelCalendar.accountid=vtiger_activity.link';
		}
		if ($queryPlanner->requireTable('vtiger_leaddetailsRelCalendar')) {
			$query .= ' 	left join vtiger_leaddetails as vtiger_leaddetailsRelCalendar on vtiger_leaddetailsRelCalendar.leadid = vtiger_activity.link';
		}
		if ($queryPlanner->requireTable('vtiger_troubleticketsRelCalendar')) {
			$query .= ' left join vtiger_troubletickets as vtiger_troubleticketsRelCalendar on vtiger_troubleticketsRelCalendar.ticketid = vtiger_activity.process';
		}
		if ($queryPlanner->requireTable('vtiger_campaignRelCalendar')) {
			$query .= ' 	left join vtiger_campaign as vtiger_campaignRelCalendar on vtiger_campaignRelCalendar.campaignid = vtiger_activity.process';
		}
		if ($queryPlanner->requireTable('vtiger_groupsCalendar')) {
			$query .= ' left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentityCalendar.smownerid';
		}
		if ($queryPlanner->requireTable('vtiger_usersCalendar')) {
			$query .= ' 	left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentityCalendar.smownerid';
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedByCalendar')) {
			$query .= '  left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentityCalendar.modifiedby ';
		}
		if ($queryPlanner->requireTable('vtiger_createdbyCalendar')) {
			$query .= ' left join vtiger_users as vtiger_createdbyCalendar on vtiger_createdbyCalendar.id = vtiger_crmentityCalendar.smcreatorid ';
		}
		return $query;
	}

	public function getNonAdminAccessControlQuery($module, $scope = '')
	{
		require('user_privileges/user_privileges_' . \App\User::getCurrentUserId() . '.php');
		require('user_privileges/sharing_privileges_' . \App\User::getCurrentUserId() . '.php');
		$query = ' ';
		$tabId = \App\Module::getModuleId($module);
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u' . \App\User::getCurrentUserId() . '_t' . $tabId;
			$sharedTabId = null;
			$this->setupTemporaryTable($tableName, $sharedTabId, $current_user_parent_role_seq, $current_user_groups);

			$sharedUsers = $this->getListViewAccessibleUsers(\App\User::getCurrentUserId());
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
	public function getReportsNonAdminAccessControlQuery($tableName, $tabId, $parent_roles, $groups)
	{
		$sharedUsers = $this->getListViewAccessibleUsers(\App\User::getCurrentUserId());
		$this->setupTemporaryTable($tableName, $tabId, $parent_roles, $groups);
		$query = "SELECT id FROM $tableName WHERE $tableName.shared=0 && $tableName.id IN ($sharedUsers)";
		return $query;
	}

	protected function setupTemporaryTable($tableName, $tabId, $parentRole, $userGroups)
	{
		$module = null;
		if (!empty($tabId)) {
			$module = \App\Module::getModuleName($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared " .
			"int(1) default 0) ignore " . $query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, []);
		if (is_object($result)) {
			$query = "REPLACE INTO $tableName (id) SELECT userid as id FROM vtiger_sharedcalendar WHERE sharedid = ?";
			$result = $db->pquery($query, [\App\User::getCurrentUserId()]);
			if (is_object($result)) {
				return true;
			}
		}
		return false;
	}

	protected function getListViewAccessibleUsers($sharedId)
	{
		$db = PearDatabase::getInstance();
		$query = "SELECT vtiger_users.id as userid FROM vtiger_sharedcalendar
					RIGHT JOIN vtiger_users ON vtiger_sharedcalendar.userid=vtiger_users.id and status= 'Active'
					WHERE sharedid=? || (vtiger_users.status='Active' && vtiger_users.id <> ?);";
		$result = $db->pquery($query, [$sharedId, $sharedId]);
		$numberOfRows = $db->numRows($result);
		if ($numberOfRows !== 0) {
			for ($j = 0; $j < $numberOfRows; $j++) {
				$userId[] = $db->queryResult($result, $j, 'userid');
			}
			$sharedIds = implode(',', $userId);
		}
		$userId[] = $sharedId;
		$sharedIds = implode(',', $userId);
		return $sharedIds;
	}

	public function deleteRelatedDependent($crmid, $withModule, $withCrmid)
	{
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.tabid', 'vtiger_field.tablename', 'vtiger_field.columnname', 'vtiger_tab.name'])
				->from('vtiger_field')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
				->where(['fieldid' => (new \App\Db\Query())->select(['fieldid'])->from('vtiger_fieldmodulerel')->where(['module' => $this->moduleName, 'relmodule' => $withModule])])
				->createCommand()->query();

		if ($dataReader->count()) {
			$results = $dataReader->readAll();
		} else {
			$dataReader = (new \App\Db\Query())->select(['name' => 'fieldname', 'id' => 'fieldid', 'label' => 'fieldlabel', 'column' => 'columnname', 'table' => 'tablename', 'vtiger_field.*'])
					->from('vtiger_field')
					->where(['uitype' => [66, 67, 68], 'tabid' => App\Module::getModuleId($this->moduleName)])
					->createCommand()->query();
			while ($row = $dataReader->read()) {
				$className = Vtiger_Loader::getComponentClassName('Model', 'Field', $this->moduleName);
				$fieldModel = new $className();
				foreach ($row as $properName => $propertyValue) {
					$fieldModel->$properName = $propertyValue;
				}
				$moduleList = $fieldModel->getUITypeModel()->getReferenceList();
				if (!empty($moduleList) && in_array($withModule, $moduleList)) {
					$row['name'] = $this->moduleName;
					$results[] = $row;
					break;
				}
			}
			$dataReader->close();
		}
		foreach ($results as $row) {
			App\Db::getInstance()->createCommand()
				->update($row['tablename'], [$row['columnname'] => 0], [$row['columnname'] => $withCrmid, CRMEntity::getInstance($row['name'])->table_index => $crmid])->execute();
		}
	}
}
