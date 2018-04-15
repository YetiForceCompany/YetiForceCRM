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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ******************************************************************************
 * Contributor(s): YetiForce.com
 */

// Contact is used to store customer information.
class Contacts extends CRMEntity
{
	public $table_name = 'vtiger_contactdetails';
	public $table_index = 'contactid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_contactdetails', 'vtiger_contactaddress', 'vtiger_contactsubdetails', 'vtiger_contactscf', 'vtiger_customerdetails', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_contactdetails' => 'contactid', 'vtiger_contactaddress' => 'contactaddressid', 'vtiger_contactsubdetails' => 'contactsubscriptionid', 'vtiger_contactscf' => 'contactid', 'vtiger_customerdetails' => 'customerid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_contactscf', 'contactid'];
	public $column_fields = [];
	public $list_link_field = 'lastname';
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'First Name' => ['contactdetails' => 'firstname'],
		'Last Name' => ['contactdetails' => 'lastname'],
		'Title' => ['contactdetails' => 'title'],
		'Member Of' => ['account' => 'parentid'],
		'Email' => ['contactdetails' => 'email'],
		'Office Phone' => ['contactdetails' => 'phone'],
		'Assigned To' => ['crmentity' => 'smownerid'],
	];
	public $list_fields_name = [
		'First Name' => 'firstname',
		'Last Name' => 'lastname',
		'Title' => 'title',
		'Member Of' => 'parent_id',
		'Email' => 'email',
		'Office Phone' => 'phone',
		'Assigned To' => 'assigned_user_id',
	];
	public $search_fields = [
		'First Name' => ['contactdetails' => 'firstname'],
		'Last Name' => ['contactdetails' => 'lastname'],
		'Title' => ['contactdetails' => 'title'],
		'Member Of' => ['contactdetails' => 'parent_id'],
		'Assigned To' => ['crmentity' => 'smownerid'],
	];
	public $search_fields_name = [
		'First Name' => 'firstname',
		'Last Name' => 'lastname',
		'Title' => 'title',
		'Member Of' => 'parent_id',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['firstname', 'lastname', 'jobtitle', 'email', 'phone', 'assigned_user_id'];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'lastname', 'createdtime', 'modifiedtime'];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['firstname', 'lastname', 'salutation', 'title', 'email', 'department', 'phone', 'mobile', 'support_start_date', 'support_end_date'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'lastname';

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

		$rel_table_arr = ['Products' => 'vtiger_seproductsrel', 'Documents' => 'vtiger_senotesrel',
			'Attachments' => 'vtiger_seattachmentsrel', 'Campaigns' => 'vtiger_campaign_records',
			'ServiceContracts' => 'vtiger_servicecontracts', 'Project' => 'vtiger_project', ];

		$tbl_field_arr = ['vtiger_seproductsrel' => 'productid', 'vtiger_senotesrel' => 'notesid',
			'vtiger_seattachmentsrel' => 'attachmentsid', 'vtiger_campaign_records' => 'campaignid',
			'vtiger_servicecontracts' => 'servicecontractsid', 'vtiger_project' => 'projectid',
			'vtiger_payments' => 'paymentsid', ];

		$entity_tbl_field_arr = ['vtiger_seproductsrel' => 'crmid', 'vtiger_senotesrel' => 'crmid',
			'vtiger_seattachmentsrel' => 'crmid', 'vtiger_campaign_records' => 'crmid',
			'vtiger_servicecontracts' => 'sc_related_to', 'vtiger_project' => 'linktoaccountscontacts',
			'vtiger_payments' => 'relatedcontact', ];

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
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_contactdetails' => 'contactid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_contactdetails' => 'contactid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_contactdetails' => 'contactid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_contactdetails' => 'contactid'],
		];
		if ($secModule === false) {
			return $relTables;
		}

		return $relTables[$secModule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		if (empty($return_module) || empty($return_id)) {
			return;
		}
		if ($return_module === 'Accounts') {
			App\Db::getInstance()->createCommand()->update('vtiger_contactdetails', ['parentid' => 0], ['contactid' => $id])->execute();
		} elseif ($return_module === 'Campaigns') {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['crmid' => $id, 'campaignid' => $return_id])->execute();
		} elseif ($return_module === 'Vendors') {
			$db = App\Db::getInstance();
			$db->createCommand()->update('vtiger_contactdetails', ['parentid' => 0], ['contactid' => $id])->execute();
			$db->createCommand()->delete('vtiger_vendorcontactrel', ['vendorid' => $return_id, 'contactid' => $id])->execute();
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName = false)
	{
		if (!is_array($withCrmid)) {
			$withCrmid = [$withCrmid];
		}
		if (!in_array($withModule, ['Products', 'Campaigns', 'Vendors'])) {
			parent::saveRelatedModule($module, $crmid, $withModule, $withCrmid, $relatedName);
		} else {
			foreach ($withCrmid as $id) {
				if ($withModule === 'Campaigns') {
					App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
						'campaignid' => $id,
						'crmid' => $crmid,
						'campaignrelstatusid' => 0,
					])->execute();
				} elseif ($withModule === 'Vendors') {
					App\Db::getInstance()->createCommand()->insert('vtiger_vendorcontactrel', [
						'vendorid' => $id,
						'contactid' => $crmid,
					])->execute();
				}
			}
		}
	}
}
