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

	public $table_name = "vtiger_campaign";
	public $table_index = 'campaignid';
	public $tab_name = Array('vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf', 'vtiger_entity_stats');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid', 'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_campaignscf', 'campaignid');
	public $column_fields = Array();
	public $list_fields = Array(
		'Campaign Name' => Array('campaign' => 'campaignname'),
		'Campaign Type' => Array('campaign' => 'campaigntype'),
		'Campaign Status' => Array('campaign' => 'campaignstatus'),
		'Expected Revenue' => Array('campaign' => 'expectedrevenue'),
		'Expected Close Date' => Array('campaign' => 'closingdate'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	public $list_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
		'Campaign Status' => 'campaignstatus',
		'Expected Revenue' => 'expectedrevenue',
		'Expected Close Date' => 'closingdate',
		'Assigned To' => 'assigned_user_id'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['campaignname', 'campaigntype', 'campaignstatus', 'expectedrevenue', 'closingdate', 'assigned_user_id'];
	public $list_link_field = 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	public $search_fields = Array(
		'Campaign Name' => Array('vtiger_campaign' => 'campaignname'),
		'Campaign Type' => Array('vtiger_campaign' => 'campaigntype'),
	);
	public $search_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
	);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id');
	// For Alphabetical search
	public $def_basicsearch_col = 'campaignname';

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityCampaigns', array('vtiger_groupsCampaigns', 'vtiger_usersCampaignss', 'vtiger_lastModifiedByCampaigns', 'vtiger_campaignscf'));
		$matrix->setDependency('vtiger_campaign', array('vtiger_crmentityCampaigns', 'vtiger_productsCampaigns'));

		if (!$queryplanner->requireTable("vtiger_campaign", $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_campaign", "campaignid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityCampaigns", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productsCampaigns")) {
			$query .= " 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid";
		}
		if ($queryplanner->requireTable("vtiger_campaignscf")) {
			$query .= " 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid";
		}
		if ($queryplanner->requireTable("vtiger_groupsCampaigns")) {
			$query .= " left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersCampaigns")) {
			$query .= " left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByCampaigns")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyCampaigns")) {
			$query .= " left join vtiger_users as vtiger_createdbyCampaigns on vtiger_createdbyCampaigns.id = vtiger_crmentityCampaigns.smcreatorid ";
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			'Contacts' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Leads' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Accounts' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Vendors' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Partners' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Competition' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Products' => array('vtiger_campaign' => array('campaignid', 'product_id')),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{

		if (empty($returnModule) || empty($returnId))
			return;

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

	public function save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		if (!is_array($withCrmids))
			$withCrmids = [$withCrmids];
		if (!in_array($withModule, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			parent::save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName);
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
					'campaignrelstatusid' => 0
				])->execute();
			}
		}
	}
}
