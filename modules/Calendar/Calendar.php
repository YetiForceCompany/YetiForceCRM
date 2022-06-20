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
 * Contributor(s): YetiForce S.A.
 */

// Task is used to store customer information.
class Calendar extends CRMEntity
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
	public $relationFields = [];
	public $search_fields_name = [];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_activitycf', 'activityid'];

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
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = 'date_start';

	/** {@inheritdoc} */
	public $default_sort_order = 'ASC';

	/** {@inheritdoc} */
	protected function init(): void
	{
		parent::init();
		$this->tableJoinClause['vtiger_activity_reminder'] = 'LEFT JOIN';
	}
}
