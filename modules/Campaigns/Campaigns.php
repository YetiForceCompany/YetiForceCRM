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

class Campaigns extends CRMEntity
{
	public $table_name = 'vtiger_campaign';
	public $table_index = 'campaignid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_campaignscf', 'campaignid'];
	public $column_fields = [];
	public $list_fields = [
		'Campaign Name' => ['campaign' => 'campaignname'],
		'Campaign Type' => ['campaign' => 'campaigntype'],
		'Campaign Status' => ['campaign' => 'campaignstatus'],
		'Expected Revenue' => ['campaign' => 'expectedrevenue'],
		'Expected Close Date' => ['campaign' => 'closingdate'],
		'Assigned To' => ['crmentity' => 'smownerid'],
	];
	public $list_fields_name = [
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
		'Campaign Status' => 'campaignstatus',
		'Expected Revenue' => 'expectedrevenue',
		'Expected Close Date' => 'closingdate',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['campaignname', 'campaigntype', 'campaignstatus', 'expectedrevenue', 'closingdate', 'assigned_user_id'];
	public $list_link_field = 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	public $search_fields = [
		'Campaign Name' => ['vtiger_campaign' => 'campaignname'],
		'Campaign Type' => ['vtiger_campaign' => 'campaigntype'],
	];
	public $search_fields_name = [
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
	];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id'];
	// For Alphabetical search
	public $def_basicsearch_col = 'campaignname';

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
			'Contacts' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'Leads' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'Accounts' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'Vendors' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'Partners' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'Competition' => ['vtiger_campaign_records' => ['campaignid', 'crmid'], 'vtiger_campaign' => 'campaignid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_campaign' => 'campaignid'],
			'Products' => ['vtiger_campaign' => ['campaignid', 'product_id']],
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

		if (in_array($returnModule, ['Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			App\Db::getInstance()->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => $returnId])->execute();
		} elseif ($returnModule == 'Accounts') {
			$db = App\Db::getInstance();
			$db->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => $returnId])->execute();
			$db->createCommand()->delete('vtiger_campaign_records', ['campaignid' => $id, 'crmid' => (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $returnId])])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids)) {
			$withCrmids = [$withCrmids];
		}
		if (!in_array($withModule, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			parent::saveRelatedModule($module, $crmid, $withModule, $withCrmids, $relatedName);
		} else {
			foreach ($withCrmids as $withCrmid) {
				$checkResult = (new App\Db\Query())->from('vtiger_campaign_records')
					->where(['campaignid' => $crmid, 'crmid' => $withCrmid])
					->exists();
				if ($checkResult) {
					continue;
				}
				App\Db::getInstance()->createCommand()->insert('vtiger_campaign_records', [
					'campaignid' => $crmid,
					'crmid' => $withCrmid,
					'campaignrelstatusid' => 0,
				])->execute();
			}
		}
	}
}
