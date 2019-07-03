<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
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
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Product Name' => ['products' => 'productname'],
		'Part Number' => ['products' => 'productcode'],
		'Commission Rate' => ['products' => 'commissionrate'],
		'Qty/Unit' => ['products' => 'qty_per_unit'],
		'Unit Price' => ['products' => 'unit_price'],
	];
	public $list_fields_name = [
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Commission Rate' => 'commissionrate',
		'Qty/Unit' => 'qty_per_unit',
		'Unit Price' => 'unit_price',
	];
	public $list_link_field = 'productname';
	public $search_fields = [
		'Product Name' => ['products' => 'productname'],
		'Part Number' => ['products' => 'productcode'],
		'Unit Price' => ['products' => 'unit_price'],
	];
	public $search_fields_name = [
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Unit Price' => 'unit_price',
	];

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = ['productname', 'productcode', 'commissionrate', 'qty_per_unit', 'unit_price'];
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
	 * @param - $secmodule secondary module name
	 *                     returns the array with table names and fieldnames storing relations between module and this module
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

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ('Leads' === $returnModule || 'Accounts' === $returnModule) {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['productid' => $id, 'crmid' => $returnId])->execute();
		} elseif ('Vendors' === $returnModule) {
			App\Db::getInstance()->createCommand()->update('vtiger_products', ['vendor_id' => null], ['productid' => $id])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmIds, $relatedName = false)
	{
		if (!\is_array($withCrmIds)) {
			$withCrmIds = [$withCrmIds];
		}
		foreach ($withCrmIds as $withCrmId) {
			if ('PriceBooks' === $withModule) {
				if ((new App\Db\Query())->from('vtiger_pricebookproductrel')->where(['pricebookid' => $withCrmId, 'productid' => $crmid])->exists()) {
					continue;
				}
				App\Db::getInstance()->createCommand()->insert('vtiger_pricebookproductrel', [
					'pricebookid' => $withCrmId,
					'productid' => $crmid,
					'listprice' => 0,
					'usedcurrency' => Vtiger_Record_Model::getInstanceById($withCrmId, $withModule)->get('currency_id')
				])->execute();
			} elseif (\in_array($withModule, ['Leads', 'Accounts', 'Contacts', 'Products'])) {
				if ('Products' === $withModule && (new App\Db\Query())->from('vtiger_seproductsrel')->where(['productid' => $withCrmId])->exists()) {
					continue;
				}
				$isExists = (new App\Db\Query())->from('vtiger_seproductsrel')->where(['crmid' => $withCrmId, 'productid' => $crmid])->exists();
				if (!$isExists) {
					App\Db::getInstance()->createCommand()->insert('vtiger_seproductsrel', [
						'crmid' => $withCrmId,
						'productid' => $crmid,
						'setype' => $withModule,
						'rel_created_user' => App\User::getCurrentUserId(),
						'rel_created_time' => date('Y-m-d H:i:s'),
					])->execute();
				}
			} else {
				parent::saveRelatedModule($module, $crmid, $withModule, $withCrmId, $relatedName);
			}
		}
	}
}
