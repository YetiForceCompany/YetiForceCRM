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
	public $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_productcf', 'productid');
	public $tab_name = Array('vtiger_crmentity', 'vtiger_products', 'vtiger_productcf');
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_products' => 'productid', 'vtiger_productcf' => 'productid', 'vtiger_seproductsrel' => 'productid'];
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'Product Name' => Array('products' => 'productname'),
		'Part Number' => Array('products' => 'productcode'),
		'Commission Rate' => Array('products' => 'commissionrate'),
		'Qty/Unit' => Array('products' => 'qty_per_unit'),
		'Unit Price' => Array('products' => 'unit_price')
	);
	public $list_fields_name = Array(
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Commission Rate' => 'commissionrate',
		'Qty/Unit' => 'qty_per_unit',
		'Unit Price' => 'unit_price'
	);
	public $list_link_field = 'productname';
	public $search_fields = Array(
		'Product Name' => Array('products' => 'productname'),
		'Part Number' => Array('products' => 'productcode'),
		'Unit Price' => Array('products' => 'unit_price')
	);
	public $search_fields_name = Array(
		'Product Name' => 'productname',
		'Part Number' => 'productcode',
		'Unit Price' => 'unit_price'
	);
	public $required_fields = Array(
		'productname' => 1
	);

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = ['productname', 'productcode', 'commissionrate', 'qty_per_unit', 'unit_price'];
	public $def_basicsearch_col = 'productname';
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname', 'assigned_user_id');
	// Josh added for importing and exporting -added in patch2
	public $unit_price;

	/** 	function used to get the number of vendors which are related to the product
	 * 	@param int $id - product id
	 * 	@return int number of rows - return the number of products which do not have relationship with vendor
	 */
	public function product_novendor()
	{

		\App\Log::trace("Entering product_novendor() method ...");
		$query = "SELECT vtiger_products.productname, vtiger_crmentity.deleted
			FROM vtiger_products
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_products.vendor_id is NULL";
		$result = $this->db->pquery($query, array());
		\App\Log::trace("Exiting product_novendor method ...");
		return $this->db->num_rows($result);
	}

	/** 	function used to get the export query for product
	 * 	@param reference $where - reference of the where variable which will be added with the query
	 * 	@return string $query - return the query which will give the list of products to export
	 */
	public function create_export_query($where)
	{

		$current_user = vglobal('current_user');
		\App\Log::trace("Entering create_export_query(" . $where . ") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Products", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM " . $this->table_name . "
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			LEFT JOIN vtiger_productcf
				ON vtiger_products.productid = vtiger_productcf.productid
			LEFT JOIN vtiger_vendor
				ON vtiger_vendor.vendorid = vtiger_products.vendor_id";

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id && vtiger_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Products', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		\App\Log::trace("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to check if the product is parent of any other product
	 */
	public function isparent_check()
	{
		$adb = PearDatabase::getInstance();
		$isparent_query = $adb->pquery(getListQuery("Products") . " && (vtiger_products.productid IN (SELECT productid from vtiger_seproductsrel WHERE vtiger_seproductsrel.productid = ? && vtiger_seproductsrel.setype='Products'))", array($this->id));
		$isparent = $adb->num_rows($isparent_query);
		return $isparent;
	}

	/** Function to check if the product is member of other product
	 */
	public function ismember_check()
	{
		$adb = PearDatabase::getInstance();
		$ismember_query = $adb->pquery(getListQuery("Products") . " && (vtiger_products.productid IN (SELECT crmid from vtiger_seproductsrel WHERE vtiger_seproductsrel.crmid = ? && vtiger_seproductsrel.setype='Products'))", array($this->id));
		$ismember = $adb->num_rows($ismember_query);
		return $ismember;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	public function transferRelatedRecords($module, $transferEntityIds, $entityId)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("HelpDesk" => "vtiger_troubletickets", "Products" => "vtiger_seproductsrel", "Attachments" => "vtiger_seattachmentsrel",
			"PriceBooks" => "vtiger_pricebookproductrel", "Leads" => "vtiger_seproductsrel",
			"Accounts" => "vtiger_seproductsrel", "Contacts" => "vtiger_seproductsrel",
			"Documents" => "vtiger_senotesrel", 'Assets' => 'vtiger_assets',);

		$tbl_field_arr = Array("vtiger_troubletickets" => "ticketid", "vtiger_seproductsrel" => "crmid", "vtiger_seattachmentsrel" => "attachmentsid",
			"vtiger_inventoryproductrel" => "id", "vtiger_pricebookproductrel" => "pricebookid", "vtiger_seproductsrel" => "crmid",
			"vtiger_senotesrel" => "notesid", 'vtiger_assets' => 'assetsid');

		$entity_tbl_field_arr = Array("vtiger_troubletickets" => "product_id", "vtiger_seproductsrel" => "crmid", "vtiger_seattachmentsrel" => "crmid",
			"vtiger_inventoryproductrel" => "productid", "vtiger_pricebookproductrel" => "productid", "vtiger_seproductsrel" => "productid",
			"vtiger_senotesrel" => "crmid", 'vtiger_assets' => 'product');

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", array($transferId, $entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->query_result($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", array($entityId, $transferId, $id_field_value));
					}
				}
			}
		}
		\App\Log::trace("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$current_user = vglobal('current_user');
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityProducts", array("vtiger_groupsProducts", "vtiger_usersProducts", "vtiger_lastModifiedByProducts"));
		$matrix->setDependency("vtiger_products", array("innerProduct", "vtiger_crmentityProducts", "vtiger_productcf", "vtiger_vendorRelProducts"));
		//query planner Support  added
		if (!$queryplanner->requireTable('vtiger_products', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_products", "productid", $queryplanner);
		if ($queryplanner->requireTable("innerProduct")) {
			$query .= " LEFT JOIN (
				    SELECT vtiger_products.productid,
						    (CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
							    ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
						    ) AS actual_unit_price
				    FROM vtiger_products
				    LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
				    LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
				    && vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
			    ) AS innerProduct ON innerProduct.productid = vtiger_products.productid";
		}
		if ($queryplanner->requireTable("vtiger_crmentityProducts")) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityProducts on vtiger_crmentityProducts.crmid=vtiger_products.productid and vtiger_crmentityProducts.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productcf")) {
			$query .= " left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid";
		}
		if ($queryplanner->requireTable("vtiger_groupsProducts")) {
			$query .= " left join vtiger_groups as vtiger_groupsProducts on vtiger_groupsProducts.groupid = vtiger_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersProducts")) {
			$query .= " left join vtiger_users as vtiger_usersProducts on vtiger_usersProducts.id = vtiger_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_vendorRelProducts")) {
			$query .= " left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByProducts")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByProducts on vtiger_lastModifiedByProducts.id = vtiger_crmentityProducts.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyProducts")) {
			$query .= " left join vtiger_users as vtiger_createdbyProducts on vtiger_createdbyProducts.id = vtiger_crmentityProducts.smcreatorid ";
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
			'HelpDesk' => array('vtiger_troubletickets' => array('product_id', 'ticketid'), 'vtiger_products' => 'productid'),
			'Quotes' => array('vtiger_inventoryproductrel' => array('productid', 'id'), 'vtiger_products' => 'productid'),
			'Leads' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'Accounts' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'Contacts' => array('vtiger_seproductsrel' => array('productid', 'crmid'), 'vtiger_products' => 'productid'),
			'PriceBooks' => array('vtiger_pricebookproductrel' => array('productid', 'pricebookid'), 'vtiger_products' => 'productid'),
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_products' => 'productid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	public function deleteProduct2ProductRelation($record, $return_id, $is_parent)
	{
		$adb = PearDatabase::getInstance();
		if ($is_parent == 0) {
			$sql = "delete from vtiger_seproductsrel WHERE crmid = ? && productid = ?";
			$adb->pquery($sql, array($record, $return_id));
		} else {
			$sql = "delete from vtiger_seproductsrel WHERE crmid = ? && productid = ?";
			$adb->pquery($sql, array($return_id, $record));
		}
	}

	/**
	 * Function to unlink all the dependent entities of the given Entity by Id
	 * @param string $moduleName
	 * @param int $recordId
	 */
	public function deletePerminently($moduleName, $recordId)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->update('vtiger_campaign', ['product_id' => 0], ['product_id' => $id])->execute();
		$db->createCommand()->delete('vtiger_seproductsrel', ['or', ['productid' => $recordId], ['crmid' => $recordId]])->execute();
		parent::deletePerminently($moduleName, $recordId);
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{

		if (empty($return_module) || empty($return_id))
			return;
		if ($return_module === 'Leads' || $return_module === 'Accounts') {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['productid' => $id, 'crmid' => $return_id])->execute();
		} elseif ($return_module == 'Vendors') {
			$sql = 'UPDATE vtiger_products SET vendor_id = ? WHERE productid = ?';
			$this->db->pquery($sql, array(null, $id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $withModule, $withCrmIds, $relatedName = false)
	{
		if (!is_array($withCrmIds))
			$withCrmIds = [$withCrmIds];
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
						'rel_created_time' => date('Y-m-d H:i:s')
					])->execute();
				}
			} else {
				parent::save_related_module($module, $crmid, $withModule, $withCrmId, $relatedName);
			}
		}
	}
}
