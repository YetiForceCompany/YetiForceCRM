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

class OSSCosts extends CRMEntity
{

	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_osscosts';
	var $table_index = 'osscostsid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_osscosts', 'vtiger_osscostscf', 'vtiger_inventoryproductrel');
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_osscosts' => 'osscostsid',
		'vtiger_osscostscf' => 'osscostsid',
		'vtiger_inventoryproductrel' => 'id');
	var $customFieldTable = Array('vtiger_osscostscf', 'osscostsid');
	var $entity_table = "vtiger_crmentity";
	var $column_fields = Array();
	var $sortby_fields = Array();
	var $list_fields = Array(
		'Costs_no' => Array('osscosts' => 'osscosts_no'),
		'Title' => Array('osscosts' => 'name'),
		'Total' => Array('osscosts' => 'total'),
		'Potential' => Array('osscosts' => 'potentialid'),
		'Project' => Array('osscosts' => 'projectid'),
		'HelpDesk' => Array('osscosts' => 'ticketid'),
		'Related to' => Array('osscosts' => 'relategid'),
		'Assigned To' => Array('osscosts' => 'assigned_user_id')
	);
	var $list_fields_name = Array(
		'Costs_no' => 'osscosts_no',
		'Title' => 'name',
		'Total' => 'hdnGrandTotal',
		'Potential' => 'potentialid',
		'Project' => 'projectid',
		'HelpDesk' => 'ticketid',
		'Related to' => 'relategid',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'name';
	var $search_fields = Array(
		'Costs_no' => Array('osscosts' => 'osscosts_no'),
		'Title' => Array('osscosts' => 'name'),
		'Potential' => Array('osscosts' => 'potentialid'),
		'Project' => Array('osscosts' => 'projectid'),
		'HelpDesk' => Array('osscosts' => 'ticketid'),
		'Related to' => Array('osscosts' => 'relategid'),
		'Assigned To' => Array('osscosts' => 'assigned_user_id')
	);
	var $search_fields_name = Array(
		'Costs_no' => 'osscosts_no',
		'Title' => 'name',
		'Potential' => 'potentialid',
		'Project' => 'projectid',
		'HelpDesk' => 'ticketid',
		'Related to' => 'relategid',
		'Assigned To' => 'assigned_user_id'
	);
	var $def_basicsearch_col = 'name';
	var $required_fields = Array('name' => 1);
	var $mandatory_fields = Array('name', 'createdtime', 'modifiedtime', 'assigned_user_id');
	var $default_order_by = 'name';
	var $default_sort_order = 'ASC';
	var $isLineItemUpdate = true;

	function OSSCosts()
	{
		$this->log = LoggerManager::getLogger('OSSCosts');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('OSSCosts');
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
			saveInventoryProductDetails($this, $module, $this->update_prod_stock);
			if ($this->mode != '') {
				$updateInventoryProductRel_deduct_stock = true;
			}

			// Update the currency id and the conversion rate for the invoice
			$update_query = "update vtiger_osscosts set currency_id=?,conversion_rate=? where osscostsid=?";

			$update_params = array($_REQUEST['currency_id'], $_REQUEST['conversion_rate'], $this->id);
			$this->db->pquery($update_query, $update_params);
		}
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
			$myCustomEntity->setModuleSeqNumber("configure", $moduleName, 'K', '1');
			$adb->query("UPDATE vtiger_tab SET customized=0 WHERE name='$moduleName'");
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			$docelowy_Module = Vtiger_Module::getInstance('Potentials');
			$docelowy_Module->setRelatedList($moduleInstance, 'OSSCosts', array('add'), 'get_dependents_list');
			$docelowy_Module = Vtiger_Module::getInstance('HelpDesk');
			$docelowy_Module->setRelatedList($moduleInstance, 'OSSCosts', array('add'), 'get_dependents_list');
			$docelowy_Module = Vtiger_Module::getInstance('Project');
			$docelowy_Module->setRelatedList($moduleInstance, 'OSSCosts', array('add'), 'get_dependents_list');
			$docelowy_Module = Vtiger_Module::getInstance('Accounts');
			$docelowy_Module->setRelatedList($moduleInstance, 'OSSCosts', array('add'), 'get_dependents_list');
			$docelowy_Module = Vtiger_Module::getInstance('Vendors');
			$docelowy_Module->setRelatedList($moduleInstance, 'OSSCosts', array('add'), 'get_dependents_list');

			$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('show_widgets_opportunities')");
			$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('show_widgets_helpdesk')");
			$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('show_widgets_project')");
			$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('restrict_opportunities')");
			$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('restrict_helpdesk')");
			//$adb->query("INSERT INTO vtiger_osscosts_config (param) VALUES ('restrict_project')");
		} else if ($eventType == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
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
		$matrix->setDependency('vtiger_crmentityOSSCosts', array('vtiger_usersOSSCosts', 'vtiger_groupsOSSCosts', 'vtiger_lastModifiedByOSSCosts'));
		$matrix->setDependency('vtiger_inventoryproductrelOSSCosts', array('vtiger_productsOSSCosts', 'vtiger_serviceOSSCosts'));
		$matrix->setDependency('vtiger_osscosts', array('vtiger_crmentityOSSCosts', "vtiger_currency_info$secmodule",
			'vtiger_osscostscf', 'vtiger_vendorRelOSSCosts', 'vtiger_inventoryproductrelOSSCosts', 'vtiger_contactdetailsOSSCosts', 'vtiger_contactdetailsRelOSSCosts', 'vtiger_osscostsRelOSSCosts', 'vtiger_potentialRelOSSCosts', 'vtiger_projectRelOSSCosts', 'vtiger_troubleticketsRelOSSCosts'));

		if (!$queryPlanner->requireTable('vtiger_osscosts', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_osscosts", "osscostsid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentityOSSCosts", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityOSSCosts on vtiger_crmentityOSSCosts.crmid=vtiger_osscosts.osscostsid and vtiger_crmentityOSSCosts.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_osscostscf")) {
			$query .= " left join vtiger_osscostscf on vtiger_osscosts.osscostsid = vtiger_osscostscf.osscostsid";
		}
		if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
			$query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_osscosts.currency_id";
		}
		if ($queryPlanner->requireTable("vtiger_inventoryproductrelOSSCosts", $matrix)) {
			$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductrelOSSCosts on vtiger_osscosts.osscostsid = vtiger_inventoryproductrelOSSCosts.id";
		}
		if ($queryPlanner->requireTable("vtiger_productsOSSCosts")) {
			$query .= " left join vtiger_products as vtiger_productsOSSCosts on vtiger_productsOSSCosts.productid = vtiger_inventoryproductrelOSSCosts.productid";
		}
		if ($queryPlanner->requireTable("vtiger_serviceOSSCosts")) {
			$query .= " left join vtiger_service as vtiger_serviceOSSCosts on vtiger_serviceOSSCosts.serviceid = vtiger_inventoryproductrelOSSCosts.productid";
		}
		if ($queryPlanner->requireTable("vtiger_usersOSSCosts")) {
			$query .= " left join vtiger_users as vtiger_usersOSSCosts on vtiger_usersOSSCosts.id = vtiger_crmentityOSSCosts.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsOSSCosts")) {
			$query .= " left join vtiger_groups as vtiger_groupsOSSCosts on vtiger_groupsOSSCosts.groupid = vtiger_crmentityOSSCosts.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsRelOSSCosts")) {
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelOSSCosts on vtiger_vendorRelOSSCosts.contactid = vtiger_osscosts.relategid";
		}
		if ($queryPlanner->requireTable("vtiger_osscostsRelOSSCosts")) {
			$query .= " left join vtiger_osscosts as vtiger_osscostsRelOSSCosts on vtiger_vendorRelOSSCosts.osscostsid = vtiger_osscosts.relategid";
		}
		if ($queryPlanner->requireTable("vtiger_vendorRelOSSCosts")) {
			$query .= " left join vtiger_vendor as vtiger_vendorRelOSSCosts on vtiger_vendorRelOSSCosts.vendorid = vtiger_osscosts.relategid";
		}
		if ($queryPlanner->requireTable("vtiger_potentialRelOSSCosts")) {
			$query .= " left join vtiger_potential as vtiger_potentialRelOSSCosts on vtiger_potentialRelOSSCosts.potentialid = vtiger_osscosts.potentialid";
		}
		if ($queryPlanner->requireTable("vtiger_projectRelOSSCosts")) {
			$query .= " left join vtiger_project as vtiger_projectRelOSSCosts on vtiger_projectRelOSSCosts.projectid = vtiger_osscosts.projectid";
		}
		if ($queryPlanner->requireTable("vtiger_troubleticketsRelOSSCosts")) {
			$query .= " left join vtiger_troubletickets as vtiger_troubleticketsRelOSSCosts on vtiger_troubleticketsRelOSSCosts.ticketid = vtiger_osscosts.ticketid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByOSSCosts")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByOSSCosts on vtiger_lastModifiedByOSSCosts.id = vtiger_crmentityOSSCosts.modifiedby ";
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
			"Documents" => array("vtiger_senotesrel" => array("crmid", "notesid"), "vtiger_osscosts" => "osscostsid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id)
	{
		$log = vglobal('log');
		if (empty($return_module) || empty($return_id))
			return;

		if ($return_module == 'Accounts' || $return_module == 'Contacts' || $return_module == 'Vendors') {
			$sql_req = 'UPDATE vtiger_osscosts SET relategid=? WHERE osscostsid = ?';
			$this->db->pquery($sql_req, array(null, $id));
		} elseif ($return_module == 'Potentials') {
			$sql_req = 'UPDATE vtiger_osscosts SET potentialid=? WHERE osscostsid = ?';
			$this->db->pquery($sql_req, array(null, $id));
		} elseif ($return_module == 'HelpDesk') {
			$sql_req = 'UPDATE vtiger_osscosts SET ticketid=? WHERE osscostsid = ?';
			$this->db->pquery($sql_req, array(null, $id));
		} elseif ($return_module == 'Project') {
			$sql_req = 'UPDATE vtiger_osscosts SET projectid=? WHERE osscostsid = ?';
			$this->db->pquery($sql_req, array(null, $id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')
	{
		//Ignore relation table insertions while saving of the record
		if ($table_name == 'vtiger_inventoryproductrel') {
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
	 * Returns Export OSSCosts Query.
	 */
	function create_export_query($where)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering create_export_query(" . $where . ") method ...");
		include("include/utils/ExportUtils.php");
		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("OSSCosts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_osscosts ON vtiger_osscosts.osscostsid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_osscostscf ON vtiger_osscostscf.osscostsid = vtiger_osscosts.osscostsid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_osscosts.osscostsid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_osscosts.relategid
				LEFT JOIN vtiger_osscosts ON vtiger_osscosts.osscostsid = vtiger_osscosts.relategid
				LEFT JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_osscosts.relategid
				LEFT JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_osscosts.potentialid
				LEFT JOIN vtiger_project ON vtiger_project.projectid = vtiger_osscosts.projectid
				LEFT JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_osscosts.ticketid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_osscosts.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('OSSCosts', $current_user);
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
		$current_user = vglobal('current_user');
		$log = vglobal('log');

		$log->debug("Entering getHierarchy(" . $id . ") method ...");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$listview_header = Array();
		$listview_entries = array();

		foreach ($this->list_fields_name as $fieldname => $colname) {
			if (getFieldVisibilityPermission('OSSCosts', $current_user->id, $colname) == '0') {
				$listview_header[] = getTranslatedString($fieldname);
			}
		}

		$rows_list = Array();
		$encountered_accounts = array($id);
		$rows_list = $this->__getParentRecord($id, $rows_list, $encountered_accounts);
		$rows_list = $this->__getChildRecord($id, $rows_list, $rows_list[$id]['depth']);
		foreach ($rows_list as $osscosts_id => $account_info) {
			$account_info_data = array();
			$hasRecordViewAccess = (is_admin($current_user)) || (isPermitted('OSSCosts', 'DetailView', $osscosts_id) == 'yes');
			foreach ($this->list_fields_name as $fieldname => $colname) {
				if (!$hasRecordViewAccess && $colname != 'name') {
					$account_info_data[] = '';
				} else if (getFieldVisibilityPermission('OSSCosts', $current_user->id, $colname) == '0') {
					$data = $account_info[$colname];
					if ($colname == 'name') {
						if ($osscosts_id != $id) {
							if ($hasRecordViewAccess) {
								$data = '<a href="index.php?module=OSSCosts&view=Detail&record=' . $osscosts_id . '">' . $data . '</a>';
							} else {
								$data = '<i>' . $data . '</i>';
							}
						} else {
							$data = '<b>' . $data . '</b>';
						}
						$account_depth = str_repeat(" .. ", $account_info['depth'] * 2);
						$data = $account_depth . $data;
					} else if ($colname == 'website') {
						$data = '<a href="http://' . $data . '" target="_blank">' . $data . '</a>';
					} else if ($colname == 'potentialid' || $colname == 'projectid' || $colname == 'ticketid' || $colname == 'relategid') {
						$data = '<a href="index.php?module=' . Vtiger_Functions::getCRMRecordType($data) . '&action=DetailView&record=' . $data . '">' . Vtiger_Functions::getCRMRecordLabel($data) . '</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$osscosts_id] = $account_info_data;
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
		$query = "SELECT parentid FROM vtiger_osscosts " .
			" INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osscosts.osscostsid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_osscosts.osscostsid = ?";
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
		$query = "SELECT vtiger_osscosts.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_osscosts" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_osscosts.osscostsid" .
			" LEFT JOIN vtiger_groups" .
			" ON vtiger_groups.groupid = vtiger_crmentity.smownerid" .
			" LEFT JOIN vtiger_users" .
			" ON vtiger_users.id = vtiger_crmentity.smownerid" .
			" WHERE vtiger_crmentity.deleted = 0 and vtiger_osscosts.osscostsid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);
		$parent_account_info = array();
		$depth = 0;
		$immediate_parentid = $adb->query_result($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach ($this->list_fields_name as $fieldname => $columnname) {
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
		$query = "SELECT vtiger_osscosts.*," .
			" CASE when (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END as user_name " .
			" FROM vtiger_osscosts" .
			" INNER JOIN vtiger_crmentity " .
			" ON vtiger_crmentity.crmid = vtiger_osscosts.osscostsid" .
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
				$child_acc_id = $adb->query_result($res, $i, 'osscostsid');
				if (array_key_exists($child_acc_id, $child_accounts)) {
					continue;
				}
				$child_account_info = array();
				$child_account_info['depth'] = $depth;
				foreach ($this->list_fields_name as $fieldname => $columnname) {
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
