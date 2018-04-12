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
	public $table_name = 'vtiger_activity';
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
		// Format: Field Label => fieldname
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
		'Assigned To' => ['crmentity' => 'smownerid'],
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
		'End Time' => 'time_end', ];
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
	 * @param string $tableName
	 *
	 * @return string
	 */
	public function getJoinClause($tableName)
	{
		if ($tableName == 'vtiger_activity_reminder') {
			return 'LEFT JOIN';
		}

		return parent::getJoinClause($tableName);
	}

	/**
	 * Function to get reminder for activity.
	 *
	 * @param int    $activityId   - activity id
	 * @param string $reminderTime - reminder time
	 * @param int    $reminderSent - 0 or 1
	 * @param int    $recurid      - recuring eventid
	 * @param string $reminderMode - string like 'edit'
	 */
	public function activityReminder($activityId, $reminderTime, $reminderSent, $recurid, $reminderMode = '')
	{
		\App\Log::trace("Entering vtiger_activity_reminder($activityId,$reminderTime,$reminderSent,$recurid,$reminderMode) method ...");
		//Check for vtiger_activityid already present in the reminder_table
		$query = sprintf('SELECT activity_id FROM %s WHERE activity_id = ?', $this->reminder_table);
		$resultExist = $this->db->pquery($query, [$activityId]);

		if ($reminderMode == 'edit') {
			if ($this->db->getRowCount($resultExist) > 0) {
				$this->db->update($this->reminder_table, [
					'reminder_time' => $reminderTime,
					'reminder_sent' => $reminderSent,
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
	 * this function sets the status flag of activity to true or false depending on the status passed to it.
	 *
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	public function setActivityReminder($status)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_activity_reminder_popup', [
				'status' => 1,
				], ['recordid' => $this->id])
				->execute();

		return true;
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
