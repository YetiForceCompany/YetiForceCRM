<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Assets extends CRMEntity
{

	public $table_name = 'vtiger_assets';
	public $table_index = 'assetsid';
	public $column_fields = [];
	protected $lockFields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_assetscf', 'assetsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_assets', 'vtiger_assetscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_assets' => 'assetsid',
		'vtiger_assetscf' => 'assetsid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Asset No' => ['assets' => 'asset_no'],
		'Asset Name' => ['assets' => 'assetname'],
		'Customer Name' => ['account' => 'account'],
		'Product Name' => ['products' => 'product'],
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'Asset No' => 'asset_no',
		'Asset Name' => 'assetname',
		'Customer Name' => 'account',
		'Product Name' => 'product',
	];
	// Make the field link to detail view
	public $list_link_field = 'assetname';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Asset No' => ['assets' => 'asset_no'],
		'Asset Name' => ['assets' => 'assetname'],
		'Customer Name' => ['account' => 'account'],
		'Product Name' => ['products' => 'product']
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'Asset No' => 'asset_no',
		'Asset Name' => 'assetname',
		'Customer Name' => 'account',
		'Product Name' => 'product'
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['asset_no', 'assetname', 'product', 'assigned_user_id'];
	// For Popup window record selection
	public $popup_fields = ['assetname', 'account', 'product'];
	// For Alphabetical search
	public $def_basicsearch_col = 'assetname';
	// Required Information for enabling Import feature
	public $required_fields = ['assetname' => 1];
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['assetname', 'product', 'assigned_user_id'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	public $unit_price;

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
				$sec_query .= " vtiger_groups.groupid IN (" . implode(",", $current_user_groups) . ") || ";
			}
			$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=" . $current_user->id . " and tabid=" . $tabid . "
						)";
			$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	public function createExportQuery($where)
	{
		$current_user = vglobal('current_user');

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Assets', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if (!empty($this->customFieldTable)) {
			$query .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

		$where_auto = " vtiger_crmentity.deleted=0";

		if ($where != '')
			$query .= sprintf(' where (%s) && %s', $where, $where_auto);
		else
			$query .= sprintf(' where %s', $where_auto);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');

		// Security Check for Field Access
		if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[\App\Module::getModuleId('Assets')] == 3) {
			//Added security check to get the permitted records only
			$query = $query . " " . getListViewSecurityParameter($thismodule);
		}
		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	public function transformExportValue($key, $value)
	{
		if ($key == 'owner')
			return \App\Fields\Owner::getLabel($value);
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	public function getDuplicatesQuery($module, $table_cols, $field_values, $ui_type_arr, $select_cols = '')
	{
		$select_clause = "SELECT %s.%s AS recordid, vtiger_users_last_import.deleted, %s ";
		$select_clause = sprintf($select_clause, $this->table_name, $this->table_index, $table_cols);
		// Select Custom Field Table Columns if present
		if (isset($this->customFieldTable))
			$query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if (isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN " . $this->customFieldTable[0] . " ON " . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] .
				" = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t." . $this->table_index;
			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
			" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
			" INNER JOIN (" . $sub_query . ") AS temp ON " . get_on_clause($field_values) .
			$where_clause .
			" ORDER BY $table_cols," . $this->table_name . "." . $this->table_index . " ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string Module name
	 * @param string Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		require_once('include/utils/utils.php');
		if ($eventType === 'module.postinstall') {
			// Mark the module as Standard module
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();

			//adds sharing accsess
			$AssetsModule = vtlib\Module::getInstance('Assets');
			vtlib\Access::setDefaultSharing($AssetsModule);

			//Showing Assets module in the related modules in the More Information Tab
			$assetInstance = vtlib\Module::getInstance('Assets');
			$assetLabel = 'Assets';

			$accountInstance = vtlib\Module::getInstance('Accounts');
			$accountInstance->setRelatedlist($assetInstance, $assetLabel, ['ADD'], 'getDependentsList');
			$productInstance = vtlib\Module::getInstance('Products');
			$productInstance->setRelatedlist($assetInstance, $assetLabel, ['ADD'], 'getDependentsList');

			\App\Fields\RecordNumber::setNumber($moduleName, 'ASSET', 1);
		} else if ($eventType === 'module.postupdate') {
			\App\Fields\RecordNumber::setNumber($moduleName, 'ASSET', 1);
		}
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

		$rel_table_arr = ["Documents" => "vtiger_senotesrel", "Attachments" => "vtiger_seattachmentsrel"];

		$tbl_field_arr = ["vtiger_senotesrel" => "notesid", "vtiger_seattachmentsrel" => "attachmentsid"];

		$entity_tbl_field_arr = ["vtiger_senotesrel" => "crmid", "vtiger_seattachmentsrel" => "crmid"];

		foreach ($transferEntityIds as $transferId) {
			foreach ($rel_table_arr as $rel_module => $rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result = $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
					" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)", [$transferId, $entityId]);
				$res_cnt = $adb->numRows($sel_result);
				if ($res_cnt > 0) {
					for ($i = 0; $i < $res_cnt; $i++) {
						$id_field_value = $adb->queryResult($sel_result, $i, $id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?", [$entityId, $transferId, $id_field_value]);
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		\App\Log::trace("Exiting transferRelatedRecords...");
	}
}
