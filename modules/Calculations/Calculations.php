<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************************************************************** */
require_once('include/CRMEntity.php');
require_once('include/Tracker.php');

class Calculations extends CRMEntity
{

	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_calculations';
	var $table_index = 'calculationsid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_calculations', 'vtiger_calculationscf', 'vtiger_calculationsproductrel');
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_calculations' => 'calculationsid',
		'vtiger_calculationscf' => 'calculationsid',
		'vtiger_calculationsproductrel' => 'id');
	var $customFieldTable = Array('vtiger_calculationscf', 'calculationsid');
	var $entity_table = "vtiger_crmentity";
	var $column_fields = Array();
	var $sortby_fields = Array();
	var $list_fields = Array(
		'Number' => Array('calculations' => 'calculations_no'),
		'Title' => Array('calculations' => 'name'),
		'Total' => Array('calculations' => 'total'),
		'Related to' => Array('calculations' => 'relatedid'),
		'Assigned To' => Array('calculations' => 'assigned_user_id')
	);
	var $list_fields_name = Array(
		'Number' => 'calculations_no',
		'Title' => 'name',
		'Total' => 'hdnGrandTotal',
		'Related to' => 'relatedid',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'name';
	var $search_fields = Array(
		'Number' => Array('calculations' => 'calculations_no'),
		'Title' => Array('calculations' => 'name'),
		'Related to' => Array('calculations' => 'relatedid'),
		'Assigned To' => Array('calculations' => 'assigned_user_id')
	);
	var $search_fields_name = Array(
		'Number' => 'calculations_no',
		'Title' => 'name',
		'Related to' => 'relatedid',
		'Assigned To' => 'assigned_user_id'
	);
	var $def_basicsearch_col = 'name';
	var $required_fields = Array('name' => 1);
	var $mandatory_fields = Array('name', 'createdtime', 'modifiedtime', 'assigned_user_id');
	var $default_order_by = 'name';
	var $default_sort_order = 'ASC';
	var $isLineItemUpdate = true;
	var $fieldsToGenerate = Array(
		'Quotes' => Array(
			'name' => 'subject',
			'potentialid' => 'potential_id',
			'relatedid' => 'account_id',
			'requirementcardsid' => 'requirementcards_id',
		),
	);

	function Calculations()
	{
		$this->log = LoggerManager::getLogger('Calculations');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calculations');
	}

	function save_module($module)
	{
		global $adb, $updateInventoryProductRel_deduct_stock;
		$updateInventoryProductRel_deduct_stock = false;
		//in ajax save we should not call this function, because this will delete all the existing product values
		if ($_REQUEST['action'] != $module . 'Ajax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {

			$requestProductIdsList = $requestQuantitiesList = array();
			$totalNoOfProducts = $_REQUEST['totalProductCount'];
			for ($i = 1; $i <= $totalNoOfProducts; $i++) {
				$productId = $_REQUEST['hdnProductId' . $i];
				$requestProductIdsList[$productId] = $productId;
				$requestQuantitiesList[$productId] = $_REQUEST['qty' . $i];
			}
			self::saveInventoryProductDetails($this, $module, $this->update_prod_stock);
			if ($this->mode != '') {
				$updateInventoryProductRel_deduct_stock = true;
			}
		}
	}

	function saveInventoryProductDetails(&$focus, $module, $update_prod_stock = 'false', $updateDemand = '')
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$id = $focus->id;
		$log->debug("Entering into saveInventoryProductDetails($module).");
		//Added to get the convertid
		if (isset($_REQUEST['convert_from']) && $_REQUEST['convert_from'] != '') {
			$id = vtlib_purify($_REQUEST['return_id']);
		} else if (isset($_REQUEST['duplicate_from']) && $_REQUEST['duplicate_from'] != '') {
			$id = vtlib_purify($_REQUEST['duplicate_from']);
		}

		$ext_prod_arr = Array();
		if ($focus->mode == 'edit') {
			self::deleteInventoryProductDetails($focus);
		}
		$tot_no_prod = $_REQUEST['totalProductCount'];
		//If the taxtype is group then retrieve all available taxes, else retrive associated taxes for each product inside loop
		$prod_seq = 1;
		$total_purchase = 0;
		$total_margin = 0;
		for ($i = 1; $i <= $tot_no_prod; $i++) {
			//if the product is deleted then we should avoid saving the deleted products
			if ($_REQUEST["deleted" . $i] == 1)
				continue;

			$prod_id = vtlib_purify($_REQUEST['hdnProductId' . $i]);
			if (isset($_REQUEST['productDescription' . $i]))
				$description = vtlib_purify($_REQUEST['productDescription' . $i]);
			$qty = vtlib_purify($_REQUEST['qty' . $i]);
			$listprice = vtlib_purify($_REQUEST['listPrice' . $i]);
			$comment = vtlib_purify($_REQUEST['comment' . $i]);
			$purchaseCost = vtlib_purify($_REQUEST['purchaseCost' . $i]);
			$rbh = vtlib_purify($_REQUEST['rbh' . $i]);
			$purchase = vtlib_purify($_REQUEST['purchase' . $i]);
			$margin = vtlib_purify($_REQUEST['margin' . $i]);
			$marginp = vtlib_purify($_REQUEST['marginp' . $i]);

			$total_purchase+= ($purchase * $qty );
			$total_margin+= $margin;


			$query = "insert into vtiger_calculationsproductrel(id, productid, sequence_no, quantity, listprice, comment, description,rbh,purchase,margin,marginp) values(?,?,?,?,?,?,?,?,?,?,?)";
			$qparams = array($focus->id, $prod_id, $prod_seq, $qty, $listprice, $comment, $description, $rbh, $purchase, $margin, $marginp);
			$adb->pquery($query, $qparams);

			$lineitem_id = $adb->getLastInsertID();

			$sub_prod_str = $_REQUEST['subproduct_ids' . $i];
			if (!empty($sub_prod_str)) {
				$sub_prod = split(":", $sub_prod_str);
				for ($j = 0; $j < count($sub_prod); $j++) {
					$query = "insert into vtiger_inventorysubproductrel(id, sequence_no, productid) values(?,?,?)";
					$qparams = array($focus->id, $prod_seq, $sub_prod[$j]);
					$adb->pquery($query, $qparams);
				}
			}
			$prod_seq++;
		}

		$updatequery = " update $focus->table_name set ";
		$updateparams = array();

		$updatequery .= " total=?";
		array_push($updateparams, vtlib_purify($_REQUEST['total']));
		$updatequery .= ", total_purchase=?";
		array_push($updateparams, $total_purchase);
		$updatequery .= ", total_margin=?";
		array_push($updateparams, $total_margin);
		$updatequery .= ", total_marginp=?";
		if (0 != $total_purchase)
			array_push($updateparams, ($total_margin / $total_purchase) * 100);
		else
			array_push($updateparams, 0);
		$updatequery .= " where " . $focus->table_index . "=?";
		array_push($updateparams, $focus->id);
		$adb->pquery($updatequery, $updateparams);
		$log->debug("Exit from saveInventoryProductDetails($module).");
	}

	function deleteInventoryProductDetails($focus)
	{
		global $log, $adb, $updateInventoryProductRel_update_product_array;
		$log->debug("Entering into deleteInventoryProductDetails(" . $focus->id . ").");

		$product_info = $adb->pquery("SELECT productid, quantity, sequence_no, incrementondel from vtiger_calculationsproductrel WHERE id=?", array($focus->id));
		$numrows = $adb->num_rows($product_info);
		for ($index = 0; $index < $numrows; $index++) {
			$productid = $adb->query_result($product_info, $index, 'productid');
			$sequence_no = $adb->query_result($product_info, $index, 'sequence_no');
			$qty = $adb->query_result($product_info, $index, 'quantity');
			$incrementondel = $adb->query_result($product_info, $index, 'incrementondel');

			if ($incrementondel) {
				$focus->update_product_array[$focus->id][$sequence_no][$productid] = $qty;
				$sub_prod_query = $adb->pquery("SELECT productid from vtiger_inventorysubproductrel WHERE id=? AND sequence_no=?", array($focus->id, $sequence_no));
				if ($adb->num_rows($sub_prod_query) > 0) {
					for ($j = 0; $j < $adb->num_rows($sub_prod_query); $j++) {
						$sub_prod_id = $adb->query_result($sub_prod_query, $j, "productid");
						$focus->update_product_array[$focus->id][$sequence_no][$sub_prod_id] = $qty;
					}
				}
			}
		}
		$updateInventoryProductRel_update_product_array = $focus->update_product_array;
		$adb->pquery("delete from vtiger_calculationsproductrel where id=?", array($focus->id));
		$adb->pquery("delete from vtiger_inventorysubproductrel where id=?", array($focus->id));

		$log->debug("Exit from deleteInventoryProductDetails(" . $focus->id . ")");
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	function vtlib_handler($moduleName, $eventType)
	{
		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			include_once('vtlib/Vtiger/Module.php');
			$myCustomEntity = CRMEntity::getInstance($moduleName);
			$myCustomEntity->setModuleSeqNumber("configure", $moduleName, '', '1');
			$adb->query("UPDATE vtiger_tab SET customized=0 WHERE name='$moduleName'");

			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModTracker');
			if ($modcommentsModuleInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once('vtlib/Vtiger/Module.php');
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = Vtiger_Functions::getModuleId($moduleName);
				$moduleModTrackerInstance = new ModTracker();
				if (!$moduleModTrackerInstance->isModulePresent($tabid)) {
					$res = $adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)", array($tabid, 1));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				} else {
					$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 1 WHERE tabid = ?", array($tabid));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				}
			}
		} else if ($eventType == 'module.readonly="readonly"') {
			// TODO Handle actions when this module is readonly="readonly".
		} else if ($eventType == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if ($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if ($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if ($eventType == 'module.postupdate') {
			
		}
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityCalculations', array('vtiger_usersCalculations', 'vtiger_groupsCalculations', 'vtiger_lastModifiedByCalculations'));
		$matrix->setDependency('vtiger_calculationsproductrelCalculations', array('vtiger_productsCalculations', 'vtiger_serviceCalculations'));
		$matrix->setDependency('vtiger_calculations', array('vtiger_crmentityCalculations', "vtiger_currency_info$secmodule",
			'vtiger_calculationscf', 'vtiger_vendorRelCalculations', 'vtiger_calculationsproductrelCalculations', 'vtiger_contactdetailsCalculations', 'vtiger_contactdetailsRelCalculations', 'vtiger_calculationsRelCalculations', 'vtiger_potentialRelCalculations', 'vtiger_projectRelCalculations', 'vtiger_troubleticketsRelCalculations'));

		if (!$queryPlanner->requireTable('vtiger_calculations', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_calculations", "calculationsid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityCalculations", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityCalculations on vtiger_crmentityCalculations.crmid=vtiger_calculations.calculationsid and vtiger_crmentityCalculations.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_calculationscf")) {
			$query .= " left join vtiger_calculationscf on vtiger_calculations.calculationsid = vtiger_calculationscf.calculationsid";
		}
		if ($queryPlanner->requireTable("vtiger_calculationsproductrelCalculations", $matrix)) {
			$query .= " left join vtiger_calculationsproductrel as vtiger_calculationsproductrelCalculations on vtiger_calculations.calculationsid = vtiger_calculationsproductrelCalculations.id";
		}
		if ($queryPlanner->requireTable("vtiger_productsCalculations")) {
			$query .= " left join vtiger_products as vtiger_productsCalculations on vtiger_productsCalculations.productid = vtiger_calculationsproductrelCalculations.productid";
		}
		if ($queryPlanner->requireTable("vtiger_serviceCalculations")) {
			$query .= " left join vtiger_service as vtiger_serviceCalculations on vtiger_serviceCalculations.serviceid = vtiger_calculationsproductrelCalculations.productid";
		}
		if ($queryPlanner->requireTable("vtiger_usersCalculations")) {
			$query .= " left join vtiger_users as vtiger_usersCalculations on vtiger_usersCalculations.id = vtiger_crmentityCalculations.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsCalculations")) {
			$query .= " left join vtiger_groups as vtiger_groupsCalculations on vtiger_groupsCalculations.groupid = vtiger_crmentityCalculations.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsRelCalculations")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelCalculations on vtiger_vendorRelCalculations.contactid = vtiger_calculations.relatedid";
		}
		if ($queryPlanner->requireTable("vtiger_calculationsRelCalculations")) {
			$query .= " left join vtiger_calculations as vtiger_calculationsRelCalculations on vtiger_vendorRelCalculations.calculationsid = vtiger_calculations.relatedid";
		}
		if ($queryPlanner->requireTable("vtiger_vendorRelCalculations")) {
			$query .= " left join vtiger_vendor as vtiger_vendorRelCalculations on vtiger_vendorRelCalculations.vendorid = vtiger_calculations.relatedid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByCalculations")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByCalculations on vtiger_lastModifiedByCalculations.id = vtiger_crmentityCalculations.modifiedby ";
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	function setRelationTables($secmodule)
	{
		$rel_tables = array(
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_calculations" => "calculationsid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Accounts' || $return_module == 'Contacts') {
			$sql_req = 'UPDATE vtiger_calculations SET relatedid=? WHERE calculationsid = ?';
			$this->db->pquery($sql_req, array(null, $id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')
	{
		//Ignore relation table insertions while saving of the record
		if ($table_name == 'vtiger_calculationsproductrel') {
			return;
		}
		parent::insertIntoEntityTable($table_name, $module, $fileid);
	}
	/* Function to create records in current module.
	 * *This function called while importing records to this module */

	function createRecords($obj)
	{
		$createRecords = createRecords($obj);
		return $createRecords;
	}
	/* Function returns the record information which means whether the record is imported or not
	 * *This function called while importing records to this module */

	function importRecord($obj, $inventoryFieldData, $lineItemDetails)
	{
		$entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
		return $entityInfo;
	}
	/* Function to return the status count of imported records in current module.
	 * *This function called while importing records to this module */

	function getImportStatusCount($obj)
	{
		$statusCount = getImportStatusCount($obj);
		return $statusCount;
	}

	function undoLastImport($obj, $user)
	{
		$undoLastImport = undoLastImport($obj, $user);
	}

	/** Function to export the lead records in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Calculations Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");
		include("include/utils/ExportUtils.php");
		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Calculations", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_calculations ON vtiger_calculations.calculationsid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_calculationscf ON vtiger_calculationscf.calculationsid = vtiger_calculations.calculationsid
				LEFT JOIN vtiger_calculationsproductrel ON vtiger_calculationsproductrel.id = vtiger_calculations.calculationsid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_calculationsproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_calculationsproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_calculations.relatedid
				LEFT JOIN vtiger_calculations ON vtiger_calculations.calculationsid = vtiger_calculations.relatedid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Calculations', $current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != "") {
			$query .= " where ($where) AND " . $where_auto;
		} else {
			$query .= " where " . $where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	function getHierarchy($id)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering getHierarchy(" . $id . ") method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$listview_header = Array();
		$listview_entries = array();

		foreach ($this->list_fields as $fieldname => $colname) {
			if (getFieldVisibilityPermission('Calculations', $current_user->id, $colname['calculations']) == '0') {
				$listview_header[] = getTranslatedString($fieldname);
			}
		}

		$rows_list = Array();
		$encountered_accounts = array($id);
		$rows_list = $this->__getParentRecord($id, $rows_list, $encountered_accounts);
		$rows_list = $this->__getChildRecord($id, $rows_list, $rows_list[$id]['depth']);
		foreach ($rows_list as $calculations_id => $account_info) {
			$account_info_data = array();
			$hasRecordViewAccess = (is_admin($current_user)) || (isPermitted('Calculations', 'DetailView', $calculations_id) == 'yes');
			foreach ($this->list_fields as $fieldname => $colname) {
				$colname = $colname['calculations'];
				if (!$hasRecordViewAccess && $colname != 'name') {
					$account_info_data[] = '';
				} else if (getFieldVisibilityPermission('Calculations', $current_user->id, $colname) == '0') {
					$data = $account_info[$colname];
					if ($colname == 'name') {
						if ($calculations_id != $id) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=Calculations&view=Detail&record=' . $calculations_id . '">' . $data . '</a>';
							} else {
								$data = '<i>' . $data . '</i>';
							}
						} else {
							$data = '<b>' . $data . '</b>';
						}
						$account_depth = str_repeat(" .. ", $account_info['depth']);
						$data = $account_depth . $data;
					} else if ($colname == 'relatedid') {
						$data = '';
						if ($data != 0)
							$data = '<a href="index.php?module=Calculations&view=Detail&record=' . $data . '">' . Vtiger_Functions::getCRMRecordLabel($data) . '</a>';
					} else if ($colname == 'website') {
						$data = '<a href="http://' . $data . '" target="_blank">' . $data . '</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$calculations_id] = $account_info_data;
		}
		$hierarchy = array('header' => $listview_header, 'entries' => $listview_entries);
		$log->debug("Exiting getHierarchy method ...");
		return $hierarchy;
	}

	function __getParentRecord($id, &$parent_accounts, &$encountered_accounts)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering __getParentRecord(" . $id . "," . $parent_accounts . ") method ...");
		$query = "SELECT parentid FROM vtiger_calculations " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_calculations.calculationsid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_calculations.calculationsid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		if ($adb->num_rows($res) > 0 &&
			$adb->query_result($res, 0, 'parentid') != '' && $adb->query_result($res, 0, 'parentid') != 0 &&
			!in_array($adb->query_result($res, 0, 'parentid'), $encountered_accounts)) {

			$parentid = $adb->query_result($res, 0, 'parentid');
			$encountered_accounts[] = $parentid;
			$this->__getParentRecord($parentid, $parent_accounts, $encountered_accounts);
		}
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_calculations.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_calculations" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_calculations.calculationsid" .
			" LEFT JOIN vtiger_groups" .
			" ON vtiger_groups.groupid = vtiger_crmentity.smownerid" .
			" LEFT JOIN vtiger_users" .
			" ON vtiger_users.id = vtiger_crmentity.smownerid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_calculations.calculationsid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		$parent_account_info = array();
		$depth = 0;
		$immediate_parentid = $adb->query_result($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach ($this->list_fields as $fieldname => $columnname) {
			$columnname = $columnname['calculations'];
			if ($columnname == 'assigned_user_id') {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, 'user_name');
			} else {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, $columnname);
			}
		}
		$parent_accounts[$id] = $parent_account_info;
		$log->debug("Exiting __getParentRecord method ...");
		return $parent_accounts;
	}

	function __getChildRecord($id, &$child_accounts, $depth)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$log->debug("Entering __getChildRecord(" . $id . "," . $child_accounts . "," . $depth . ") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_calculations.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_calculations" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_calculations.calculationsid" .
			" LEFT JOIN vtiger_groups" .
			" ON vtiger_groups.groupid = vtiger_crmentity.smownerid" .
			" LEFT JOIN vtiger_users" .
			" ON vtiger_users.id = vtiger_crmentity.smownerid" .
			" WHERE vtiger_crmentity.deleted = 0 and parentid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($res);
		if ($num_rows > 0) {
			$depth = $depth + 1;
			for ($i = 0; $i < $num_rows; $i++) {
				$child_acc_id = $adb->query_result($res, $i, 'calculationsid');
				if (array_key_exists($child_acc_id, $child_accounts)) {
					continue;
				}
				$child_account_info = array();
				$child_account_info['depth'] = $depth;
				foreach ($this->list_fields as $fieldname => $columnname) {
					$columnname = $columnname['calculations'];
					if ($columnname == 'assigned_user_id') {
						$child_account_info[$columnname] = $adb->query_result($res, $i, 'user_name');
					} else {
						$child_account_info[$columnname] = $adb->query_result($res, $i, $columnname);
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildRecord($child_acc_id, $child_accounts, $depth);
			}
		}
		$log->debug("Exiting __getChildRecord method ...");
		return $child_accounts;
	}
}
