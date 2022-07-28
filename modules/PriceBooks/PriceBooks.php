<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ****************************************************************************** */

class PriceBooks extends CRMEntity
{
	public $table_name = 'vtiger_pricebook';
	public $table_index = 'pricebookid';
	public $tab_name = ['vtiger_crmentity', 'vtiger_pricebook', 'vtiger_pricebookcf'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_pricebook' => 'pricebookid', 'vtiger_pricebookcf' => 'pricebookid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_pricebookcf', 'pricebookid'];
	public $column_fields = [];

	public $list_fields_name = [
		'Price Book Name' => 'bookname',
		'Active' => 'active',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];
	public $search_fields = [
		'Price Book Name' => ['pricebook' => 'bookname'],
	];
	public $search_fields_name = [];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $mandatory_fields = ['bookname', 'currency_id', 'pricebook_no', 'createdtime', 'modifiedtime'];
	// For Alphabetical search
	public $def_basicsearch_col = 'bookname';

	/**
	 * Function to get the relation tables for related modules.
	 *
	 * @param bool|string $secmodule secondary module name
	 *
	 * @return array returns the array with table names and fieldnames storing relations between module and this module
	 */
	public function setRelationTables($secmodule = false)
	{
		$relTables = [
			'Products' => ['vtiger_pricebookproductrel' => ['pricebookid', 'productid'], 'vtiger_pricebook' => 'pricebookid'],
			'Services' => ['vtiger_pricebookproductrel' => ['pricebookid', 'productid'], 'vtiger_pricebook' => 'pricebookid'],
		];
		if (false === $secmodule) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}
}
