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

class HelpDesk extends CRMEntity
{
	public $table_name = 'vtiger_troubletickets';
	public $table_index = 'ticketid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_troubletickets', 'vtiger_ticketcf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_troubletickets' => 'ticketid', 'vtiger_ticketcf' => 'ticketid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ticketcf', 'ticketid'];
	public $column_fields = [];
	//Pavani: Assign value to entity_table
	public $entity_table = 'vtiger_crmentity';

	public $list_fields_name = [
		'Ticket No' => 'ticket_no',
		'Subject' => 'ticket_title',
		'Related To' => 'parent_id',
		'Contact Name' => 'contact_id',
		'Status' => 'ticketstatus',
		'Priority' => 'ticketpriorities',
		'Assigned To' => 'assigned_user_id',
		'FL_TOTAL_TIME_H' => 'sum_time',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	public $search_fields = [
		//'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
		'Ticket No' => ['troubletickets' => 'ticket_no'],
		// END
		'Subject' => ['troubletickets' => 'title'],
		'Related To' => ['troubletickets' => 'parent_id'],
		'Contact Name' => ['troubletickets' => 'contact_id'],
		'Status' => ['troubletickets' => 'status'],
		'Priority' => ['troubletickets' => 'priority'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'FL_TOTAL_TIME_H' => ['troubletickets', 'sum_time'],
	];
	public $search_fields_name = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'ticket_title';

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secModule secondary module name
	 *
	 * @return array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secModule = false)
	{
		$relTables = [
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_troubletickets' => 'ticketid'],
			'Services' => ['vtiger_crmentityrel' => ['crmid', 'relcrmid'], 'vtiger_troubletickets' => 'ticketid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_troubletickets' => 'ticketid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}
}
