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

class Services extends CRMEntity
{

	public $table_name = 'vtiger_service';
	public $table_index = 'serviceid';
	public $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_servicecf', 'serviceid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_service', 'vtiger_servicecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_service' => 'serviceid',
		'vtiger_servicecf' => 'serviceid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Service No' => Array('service' => 'service_no'),
		'Service Name' => Array('service' => 'servicename'),
		'Commission Rate' => Array('service' => 'commissionrate'),
		'No of Units' => Array('service' => 'qty_per_unit'),
		'Price' => Array('service' => 'unit_price')
	);
	public $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Commission Rate' => 'commissionrate',
		'No of Units' => 'qty_per_unit',
		'Price' => 'unit_price'
	);
	// Make the field link to detail view
	public $list_link_field = 'servicename';
	// For Popup listview and UI type support
	public $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Service No' => Array('service' => 'service_no'),
		'Service Name' => Array('service' => 'servicename'),
		'Price' => Array('service' => 'unit_price')
	);
	public $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Service No' => 'service_no',
		'Service Name' => 'servicename',
		'Price' => 'unit_price'
	);

	/** @var string[] List of fields in the RelationListView */
	public $relationFields = ['service_no', 'servicename', 'unit_price'];
	// For Popup window record selection
	public $popup_fields = Array('servicename', 'service_usageunit', 'unit_price');
	// For Alphabetical search
	public $def_basicsearch_col = 'servicename';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'servicename';
	// Required Information for enabling Import feature
	public $required_fields = Array('servicename' => 1);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = Array('servicename', 'assigned_user_id');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $unit_price;

	/**
	 * Get list view query.
	 */
	public function getListQuery($module, $where = '')
	{
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if (!empty($this->customFieldTable))
			$query .= ', ' . $this->customFieldTable[0] . '.* ';

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$query .= " LEFT JOIN vtiger_groups
						ON vtiger_groups.groupid = vtiger_crmentity.smownerid
					LEFT JOIN vtiger_users
						ON vtiger_users.id = vtiger_crmentity.smownerid ";
		$current_user = vglobal('current_user');
		$query .= $this->getNonAdminAccessControlQuery($module, $current_user);
		$query .= sprintf('WHERE vtiger_crmentity.deleted = 0 %s', $where);
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	public function getListViewSecurityParameter($module)
	{
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		$sec_query = '';
		$tabid = \App\Module::getModuleId($module);

		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {

			$sec_query .= " && (vtiger_crmentity.smownerid in($current_user->id) || vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . " && tabid=" . $tabid . "
					)
					OR
						(";

			// Build the query based on the group association of current user.
			if (sizeof($current_user_groups) > 0) {
				$sec_query .= ' vtiger_groups.groupid IN (' . implode(',', $current_user_groups) . ') || ';
			}
			$sec_query .= ' vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=' . $current_user->id . ' and tabid=' . $tabid . '
						)';
			$sec_query .= ')
				)';
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	public function create_export_query($where)
	{
		$current_user = vglobal('current_user');

		include('include/utils/ExportUtils.php');

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Services', 'detail_view');

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if (!empty($this->customFieldTable)) {
			$query .= ' INNER JOIN ' . $this->customFieldTable[0] . ' ON ' . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id && vtiger_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Services', $current_user);
		$where_auto = ' vtiger_crmentity.deleted=0';

		if ($where != '')
			$query .= " WHERE ($where) && $where_auto";
		else
			$query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	public function transform_export_value($key, $value)
	{
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	public function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
	{
		$select_clause = sprintf("SELECT %s.%s AS recordid, vtiger_users_last_import.deleted,%s", $this->table_name, $this->table_index, $table_cols);

		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (isset($this->customFieldTable)) {
			$from_clause .= ' INNER JOIN ' . $this->customFieldTable[0] . ' ON ' . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		$where_clause = '	WHERE vtiger_crmentity.deleted = 0';
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= ' INNER JOIN ' . $this->customFieldTable[0] . ' tcf ON tcf.' . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
			' LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=' . $this->table_name . '.' . $this->table_index .
			' INNER JOIN (' . $sub_query . ') AS temp ON ' . get_on_clause($field_values, $ui_type_arr, $module) .
			$where_clause .
			" ORDER BY $table_cols," . $this->table_name . '.' . $this->table_index . ' ASC';

		return $query;
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

		$rel_table_arr = Array("PriceBooks" => "vtiger_pricebookproductrel", "Documents" => "vtiger_senotesrel");

		$tbl_field_arr = Array("vtiger_inventoryproductrel" => "id", "vtiger_pricebookproductrel" => "pricebookid", "vtiger_senotesrel" => "notesid");

		$entity_tbl_field_arr = Array("vtiger_inventoryproductrel" => "productid", "vtiger_pricebookproductrel" => "productid", "vtiger_senotesrel" => "crmid");

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

		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace("Exiting transferRelatedRecords...");
	}
	/*
	 * Function to get the primary query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsQuery($module, $queryPlanner)
	{
		$current_user = vglobal('current_user');

		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_seproductsrel', array('vtiger_crmentityRelServices', 'vtiger_accountRelServices', 'vtiger_leaddetailsRelServices', 'vtiger_servicecf'));
		$query = 'from vtiger_service
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_service.serviceid';
		if ($queryPlanner->requireTable('vtiger_servicecf')) {
			$query .= ' left join vtiger_servicecf on vtiger_service.serviceid = vtiger_servicecf.serviceid';
		}
		if ($queryPlanner->requireTable('vtiger_usersServices')) {
			$query .= ' left join vtiger_users as vtiger_usersServices on vtiger_usersServices.id = vtiger_crmentity.smownerid';
		}
		if ($queryPlanner->requireTable('vtiger_groupsServices')) {
			$query .= ' left join vtiger_groups as vtiger_groupsServices on vtiger_groupsServices.groupid = vtiger_crmentity.smownerid';
		}
		if ($queryPlanner->requireTable('vtiger_seproductsrel')) {
			$query .= ' left join vtiger_seproductsrel on vtiger_seproductsrel.productid= vtiger_service.serviceid';
		}
		if ($queryPlanner->requireTable('vtiger_crmentityRelServices')) {
			$query .= ' left join vtiger_crmentity as vtiger_crmentityRelServices on vtiger_crmentityRelServices.crmid = vtiger_seproductsrel.crmid and vtiger_crmentityRelServices.deleted = 0';
		}
		if ($queryPlanner->requireTable('vtiger_accountRelServices')) {
			$query .= ' left join vtiger_account as vtiger_accountRelServices on vtiger_accountRelServices.accountid=vtiger_seproductsrel.crmid';
		}
		if ($queryPlanner->requireTable('vtiger_leaddetailsRelServices')) {
			$query .= ' left join vtiger_leaddetails as vtiger_leaddetailsRelServices on vtiger_leaddetailsRelServices.leadid = vtiger_seproductsrel.crmid';
		}
		if ($queryPlanner->requireTable('vtiger_lastModifiedByServices')) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedByServices on vtiger_lastModifiedByServices.id = vtiger_crmentity.modifiedby';
		}
		if ($queryplanner->requireTable('u_yf_crmentity_showners')) {
			$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
		}
		if ($queryplanner->requireTable("vtiger_shOwners$module")) {
			$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
		}
		if ($queryPlanner->requireTable('innerService')) {
			$query .= " LEFT JOIN (
					SELECT vtiger_service.serviceid,
							(CASE WHEN (vtiger_service.currency_id = 1 ) THEN vtiger_service.unit_price
								ELSE (vtiger_service.unit_price / vtiger_currency_info.conversion_rate) END
							) AS actual_unit_price
					FROM vtiger_service
					LEFT JOIN vtiger_currency_info ON vtiger_service.currency_id = vtiger_currency_info.id
					LEFT JOIN vtiger_productcurrencyrel ON vtiger_service.serviceid = vtiger_productcurrencyrel.productid
					AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
				) AS innerService ON innerService.serviceid = vtiger_service.serviceid";
		}
		return $query;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryPlanner)
	{
		$current_user = vglobal('current_user');
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_service', array('actual_unit_price', 'vtiger_currency_info', 'vtiger_productcurrencyrel', 'vtiger_servicecf', 'vtiger_crmentityServices'));
		$matrix->setDependency('vtiger_crmentityServices', array('vtiger_usersServices', 'vtiger_groupsServices', 'vtiger_lastModifiedByServices'));
		if (!$queryPlanner->requireTable("vtiger_service", $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, "vtiger_service", "serviceid", $queryPlanner);
		if ($queryPlanner->requireTable("innerService")) {
			$query .= " LEFT JOIN (
			SELECT vtiger_service.serviceid,
			(CASE WHEN (vtiger_service.currency_id = " . $current_user->currency_id . " ) THEN vtiger_service.unit_price
			WHEN (vtiger_productcurrencyrel.actual_price IS NOT NULL) THEN vtiger_productcurrencyrel.actual_price
			ELSE (vtiger_service.unit_price / vtiger_currency_info.conversion_rate) * " . $current_user->conv_rate . " END
			) AS actual_unit_price FROM vtiger_service
            LEFT JOIN vtiger_currency_info ON vtiger_service.currency_id = vtiger_currency_info.id
            LEFT JOIN vtiger_productcurrencyrel ON vtiger_service.serviceid = vtiger_productcurrencyrel.productid
			AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . ")
            AS innerService ON innerService.serviceid = vtiger_service.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_crmentityServices", $matrix)) {
			$query .= " left join vtiger_crmentity as vtiger_crmentityServices on vtiger_crmentityServices.crmid=vtiger_service.serviceid and vtiger_crmentityServices.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_servicecf")) {
			$query .= " left join vtiger_servicecf on vtiger_service.serviceid = vtiger_servicecf.serviceid";
		}
		if ($queryPlanner->requireTable("vtiger_usersServices")) {
			$query .= " left join vtiger_users as vtiger_usersServices on vtiger_usersServices.id = vtiger_crmentityServices.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsServices")) {
			$query .= " left join vtiger_groups as vtiger_groupsServices on vtiger_groupsServices.groupid = vtiger_crmentityServices.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedByServices")) {
			$query .= " left join vtiger_users as vtiger_lastModifiedByServices on vtiger_lastModifiedByServices.id = vtiger_crmentityServices.modifiedby ";
		}
		if ($queryPlanner->requireTable("vtiger_createdbyServices")) {
			$query .= " left join vtiger_users as vtiger_createdbyServices on vtiger_createdbyServices.id = vtiger_crmentityServices.smcreatorid ";
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
			'PriceBooks' => array('vtiger_pricebookproductrel' => array('productid', 'pricebookid'), 'vtiger_service' => 'serviceid'),
			'Documents' => array('vtiger_senotesrel' => array('crmid', 'notesid'), 'vtiger_service' => 'serviceid'),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	/**
	 * Function to unlink all the dependent entities of the given Entity by Id
	 * @param string $moduleName
	 * @param int $recordId
	 */
	public function deletePerminently($moduleName, $recordId)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['or', ['productid' => $recordId], ['crmid' => $recordId]])->execute();
		parent::deletePerminently($moduleName, $recordId);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{

		require_once('include/utils/utils.php');
		$adb = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$moduleInstance->allowSharing();

			$ttModuleInstance = vtlib\Module::getInstance('HelpDesk');
			$ttModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$leadModuleInstance = vtlib\Module::getInstance('Leads');
			$leadModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$accModuleInstance = vtlib\Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$conModuleInstance = vtlib\Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'));

			$pbModuleInstance = vtlib\Module::getInstance('PriceBooks');
			$pbModuleInstance->setRelatedList($moduleInstance, 'Services', array('select'), 'getPricebookServices');

			// Initialize module sequence for the module
			\App\Fields\RecordNumber::setNumber($moduleName, 'SER', 1);
			// Mark the module as Standard module
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($moduleName));
		} else if ($eventType == 'module.disabled') {
			
		} else if ($eventType == 'module.enabled') {
			
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			$ServicesModule = vtlib\Module::getInstance('Services');
			vtlib\Access::setDefaultSharing($ServicesModule);
		}
	}

	/** Function to unlink an entity with given Id from another entity */
	public function unlinkRelationship($id, $return_module, $return_id, $relatedName = false)
	{
		global $currentModule;

		\App\Log::error('id:--' . $id);
		\App\Log::error('return_module:--' . $return_module);
		\App\Log::error('return_id:---' . $return_id);
		if ($return_module == 'Accounts') {
			$focus = CRMEntity::getInstance($return_module);
			$entityIds = $focus->getRelatedContactsIds($return_id);
			array_push($entityIds, $return_id);
			$return_modules = ['Accounts', 'Contacts'];
		} else {
			$entityIds = $return_id;
			$return_modules = [$return_module];
		}
		if ($relatedName && $relatedName != 'getRelatedList') {
			parent::unlinkRelationship($id, $return_module, $return_id, $relatedName);
		} else {
			$where = '(relcrmid= ? AND module IN (' . generateQuestionMarks($return_modules) . ') AND crmid IN (' . generateQuestionMarks($entityIds) . ')) OR (crmid= ? AND relmodule IN (' . generateQuestionMarks($return_modules) . ') AND relcrmid IN (' . generateQuestionMarks($entityIds) . '))';
			$params = [$id, $return_modules, $entityIds, $id, $return_modules, $entityIds];
			$this->db->delete('vtiger_crmentityrel', $where, $params);
		}
	}
}
