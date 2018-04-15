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
	// Josh added for importing and exporting -added in patch2
	public $unit_price;

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

		$rel_table_arr = ['HelpDesk' => 'vtiger_troubletickets', 'Products' => 'vtiger_seproductsrel', 'Attachments' => 'vtiger_seattachmentsrel',
			'PriceBooks' => 'vtiger_pricebookproductrel', 'Leads' => 'vtiger_seproductsrel',
			'Accounts' => 'vtiger_seproductsrel', 'Contacts' => 'vtiger_seproductsrel',
			'Documents' => 'vtiger_senotesrel', 'Assets' => 'vtiger_assets', ];

		$tbl_field_arr = ['vtiger_troubletickets' => 'ticketid', 'vtiger_seproductsrel' => 'crmid', 'vtiger_seattachmentsrel' => 'attachmentsid',
			'vtiger_inventoryproductrel' => 'id', 'vtiger_pricebookproductrel' => 'pricebookid', 'vtiger_seproductsrel' => 'crmid',
			'vtiger_senotesrel' => 'notesid', 'vtiger_assets' => 'assetsid', ];

		$entity_tbl_field_arr = ['vtiger_troubletickets' => 'product_id', 'vtiger_seproductsrel' => 'crmid', 'vtiger_seattachmentsrel' => 'crmid',
			'vtiger_inventoryproductrel' => 'productid', 'vtiger_pricebookproductrel' => 'productid', 'vtiger_seproductsrel' => 'productid',
			'vtiger_senotesrel' => 'crmid', 'vtiger_assets' => 'product', ];

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
		\App\Log::trace('Exiting transferRelatedRecords...');
	}

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
			'Quotes' => ['vtiger_inventoryproductrel' => ['productid', 'id'], 'vtiger_products' => 'productid'],
			'Leads' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'Accounts' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'Contacts' => ['vtiger_seproductsrel' => ['productid', 'crmid'], 'vtiger_products' => 'productid'],
			'PriceBooks' => ['vtiger_pricebookproductrel' => ['productid', 'pricebookid'], 'vtiger_products' => 'productid'],
			'Documents' => ['vtiger_senotesrel' => ['crmid', 'notesid'], 'vtiger_products' => 'productid'],
		];
		if ($secmodule === false) {
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
		if ($returnModule === 'Leads' || $returnModule === 'Accounts') {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['productid' => $id, 'crmid' => $returnId])->execute();
		} elseif ($returnModule === 'Vendors') {
			App\Db::getInstance()->createCommand()->update('vtiger_products', ['vendor_id' => null], ['productid' => $id])->execute();
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	public function saveRelatedModule($module, $crmid, $withModule, $withCrmIds, $relatedName = false)
	{
		if (!is_array($withCrmIds)) {
			$withCrmIds = [$withCrmIds];
		}
		foreach ($withCrmIds as $withCrmId) {
			if (in_array($withModule, ['Leads', 'Accounts', 'Contacts', 'Products'])) {
				if ($withModule === 'Products') {
					if ((new App\Db\Query())->from('vtiger_seproductsrel')->where(['productid' => $withCrmId])->exists()) {
						continue;
					}
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
