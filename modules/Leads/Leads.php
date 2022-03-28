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
 * Contributor(s): YetiForce S.A.
 * ****************************************************************************** */

class Leads extends CRMEntity
{
	public $table_name = 'vtiger_leaddetails';
	public $table_index = 'leadid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_leaddetails', 'vtiger_leadsubdetails', 'vtiger_leadaddress', 'vtiger_leadscf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_leaddetails' => 'leadid', 'vtiger_leadsubdetails' => 'leadsubscriptionid', 'vtiger_leadaddress' => 'leadaddressid', 'vtiger_leadscf' => 'leadid', 'vtiger_entity_stats' => 'crmid'];
	public $entity_table = 'vtiger_crmentity';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_leadscf', 'leadid'];
	//construct this from database
	public $column_fields = [];
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = ['smcreatorid', 'smownerid', 'contactid', 'crmid'];

	public $list_fields_name = [
		'Company' => 'company',
		'Phone' => 'phone',
		'Website' => 'website',
		'Email' => 'email',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	public $search_fields = [
		'Company' => ['leaddetails' => 'company'],
	];
	public $search_fields_name = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assigned_user_id', 'createdtime', 'modifiedtime'];
	//Default Fields for Email Templates -- Pavani
	public $emailTemplate_defaultFields = ['leadsource', 'leadstatus', 'rating', 'industry', 'secondaryemail', 'email', 'annualrevenue'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// For Alphabetical search
	public $def_basicsearch_col = 'company';

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
			'Products' => ['vtiger_seproductsrel' => ['crmid', 'productid'], 'vtiger_leaddetails' => 'leadid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_leaddetails' => 'leadid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_leaddetails' => 'leadid'],
			'Services' => ['vtiger_crmentityrel' => ['crmid', 'relcrmid'], 'vtiger_leaddetails' => 'leadid'],
			'OSSMailView' => ['vtiger_ossmailview_relation' => ['crmid', 'ossmailviewid'], 'vtiger_leaddetails' => 'leadid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}
}
