<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Products extends CRMEntity
{
	public $table_name = 'vtiger_products';
	public $table_index = 'productid';
	public $column_fields = [];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_productcf', 'productid'];
	public $tab_name = ['vtiger_crmentity', 'vtiger_products', 'vtiger_productcf'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_products' => 'productid', 'vtiger_productcf' => 'productid', 'vtiger_seproductsrel' => 'productid'];

	public $list_fields_name = [
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Commission Rate' => 'commissionrate',
		'Qty/Unit' => 'qty_per_unit',
		'Unit Price' => 'unit_price',
	];

	public $search_fields = [
		'Product Name' => ['products' => 'productname'],
		'Part Number' => ['products' => 'productcode'],
		'Unit Price' => ['products' => 'unit_price'],
	];
	public $search_fields_name = [];

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = [];
	public $def_basicsearch_col = 'productname';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'productname', 'assigned_user_id'];

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
			'HelpDesk' => ['vtiger_troubletickets' => ['product_id', 'ticketid'], 'vtiger_products' => 'productid'],
			'Leads' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'Accounts' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'Contacts' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'PriceBooks' => ['vtiger_pricebookproductrel' => ['productid', 'pricebookid'], 'vtiger_products' => 'productid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_products' => 'productid'],
		];
		if (false === $secmodule) {
			return $relTables;
		}

		return $relTables[$secmodule];
	}
}
