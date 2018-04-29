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
	public $list_fields = [
		//Module Sequence Numbering
		//'Ticket ID'=>Array('crmentity'=>'crmid'),
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
	public $relationFields = ['ticket_no', 'ticket_title', 'parent_id', 'ticketstatus', 'ticketpriorities', 'assigned_user_id', 'sum_time'];
	public $list_link_field = 'ticket_title';
	public $search_fields = [
		//'Ticket ID' => Array('vtiger_crmentity'=>'crmid'),
		'Ticket No' => ['vtiger_troubletickets' => 'ticket_no'],
		'Title' => ['vtiger_troubletickets' => 'title'],
	];
	public $search_fields_name = [
		'Ticket No' => 'ticket_no',
		'Title' => 'ticket_title',
	];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime', 'ticket_title', 'update_log'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'ticket_title';

	public function saveRelatedModule($module, $crmid, $with_module, $with_crmid, $relatedName = false)
	{
		if ($with_module == 'ServiceContracts') {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmid);
			$serviceContract = CRMEntity::getInstance('ServiceContracts');
			$serviceContract->updateHelpDeskRelatedTo($with_crmid, $crmid);
			$serviceContract->updateServiceContractState($with_crmid);
		} else {
			parent::saveRelatedModule($module, $crmid, $with_module, $with_crmid, $relatedName);
		}
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 *
	 * @param string This module name
	 * @param array List of Entity Id's from which related records need to be transfered
	 * @param int Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = ['Attachments' => 'vtiger_seattachmentsrel', 'Documents' => 'vtiger_senotesrel'];

		$tbl_field_arr = ['vtiger_seattachmentsrel' => 'attachmentsid', 'vtiger_senotesrel' => 'notesid'];

		$entity_tbl_field_arr = ['vtiger_seattachmentsrel' => 'crmid', 'vtiger_senotesrel' => 'crmid'];

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", [$transferId, $entityId]);
				$res_cnt = $adb->numRows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; ++$i) {
						$id_field_value = $adb->queryResult($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", [$entityId, $transferId, $id_field_value]);
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

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
		if ($secModule === false) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ($returnModule === 'Accounts' || $returnModule === 'Vendors') {
			$dbCommand = App\Db::getInstance()->createCommand();
			$dbCommand->update('vtiger_troubletickets', ['parent_id' => null], ['ticketid' => $id])->execute();
			$dbCommand->delete('vtiger_seticketsrel', ['ticketid' => $id])->execute();
		} elseif ($returnModule === 'Products') {
			App\Db::getInstance()->createCommand()->update('vtiger_troubletickets', ['product_id' => null], ['ticketid' => $id])->execute();
		} elseif ($returnModule === 'ServiceContracts' && $relatedName !== 'getManyToMany') {
			parent::unlinkRelationship($id, $returnModule, $returnId);
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}
}
