<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

class Vendors extends CRMEntity
{
	public $table_name = 'vtiger_vendor';
	public $table_index = 'vendorid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_vendor', 'vtiger_vendoraddress', 'vtiger_vendorcf', 'vtiger_entity_stats'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_vendor' => 'vendorid', 'vtiger_vendoraddress' => 'vendorid', 'vtiger_vendorcf' => 'vendorid', 'vtiger_entity_stats' => 'crmid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_vendorcf', 'vendorid'];
	public $column_fields = [];
	public $related_tables = [
		'vtiger_vendorcf' => ['vendorid', 'vtiger_vendor', 'vendorid'],
		'vtiger_vendoraddress' => ['vendorid', 'vtiger_vendor', 'vendorid'],
	];
	//Pavani: Assign value to entity_table
	public $entity_table = 'vtiger_crmentity';

	public $list_fields_name = [
		'Vendor Name' => 'vendorname',
		'Phone' => 'phone',
		'Email' => 'email',
		'Category' => 'category',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	public $search_fields = [
		'Vendor Name' => ['vendor' => 'vendorname'],
		'Phone' => ['vendor' => 'phone'],
	];
	public $search_fields_name = [];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'vendorname', 'assigned_user_id'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// For Alphabetical search
	public $def_basicsearch_col = 'vendorname';

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
			'Products' => ['vtiger_products' => ['vendor_id', 'productid'], 'vtiger_vendor' => 'vendorid'],
			'Campaigns' => ['vtiger_campaign_records' => ['crmid', 'campaignid'], 'vtiger_vendor' => 'vendorid'],
		];
		if (false === $secModule) {
			return $relTables;
		}

		return $relTables[$secModule];
	}
}
