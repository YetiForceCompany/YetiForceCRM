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

	public $table_name = "vtiger_activity";
	public $table_index = 'activityid';
	// Stored vtiger_fields
	// added to check email save from plugin or not
	public $plugin_save = false;
	public $rel_users_table = "vtiger_salesmanactivityrel";
	public $rel_contacts_table = "vtiger_cntactivityrel";
	public $rel_serel_table = "vtiger_seactivityrel";
	public $tab_name = Array('vtiger_crmentity', 'vtiger_activity', 'vtiger_emaildetails');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid',
		'vtiger_seactivityrel' => 'activityid', 'vtiger_cntactivityrel' => 'activityid', 'vtiger_email_track' => 'mailid', 'vtiger_emaildetails' => 'emailid');
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'Subject' => Array('activity' => 'subject'),
		'Related to' => Array('seactivityrel' => 'parent_id'),
		'Date Sent' => Array('activity' => 'date_start'),
		'Time Sent' => Array('activity' => 'time_start'),
		'Assigned To' => Array('crmentity', 'smownerid'),
		'Access Count' => Array('email_track', 'access_count')
	);
	public $list_fields_name = Array(
		'Subject' => 'subject',
		'Related to' => 'parent_id',
		'Date Sent' => 'date_start',
		'Time Sent' => 'time_start',
		'Assigned To' => 'assigned_user_id',
		'Access Count' => 'access_count'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['subject', 'parent_id', 'date_start', 'time_start', 'assigned_user_id', 'access_count'];
	public $list_link_field = 'subject';
	public $column_fields = Array();
	public $sortby_fields = Array('subject', 'date_start', 'saved_toid');
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('subject', 'assigned_user_id');

}
