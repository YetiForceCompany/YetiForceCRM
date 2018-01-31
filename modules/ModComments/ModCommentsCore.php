<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once('include/CRMEntity.php');

class ModCommentsCore extends CRMEntity
{

	public $table_name = 'vtiger_modcomments';
	public $table_index = 'modcommentsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_modcommentscf', 'modcommentsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_modcomments', 'vtiger_modcommentscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_modcomments' => 'modcommentsid',
		'vtiger_modcommentscf' => 'modcommentsid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Comment' => ['modcomments', 'commentcontent'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public $list_fields_name = [
		/* Format: Field Label => fieldname */
		'Comment' => 'commentcontent',
		'Assigned To' => 'assigned_user_id'
	];
	// Make the field link to detail view
	public $list_link_field = 'commentcontent';
	// For Popup listview and UI type support
	public $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Comment' => ['modcomments', 'commentcontent']
	];
	public $search_fields_name = [
		/* Format: Field Label => fieldname */
		'Comment' => 'commentcontent'
	];
	// For Popup window record selection
	public $popup_fields = ['commentcontent'];
	// Should contain field labels
	//var $detailview_links = Array ('Comment');
	// For Alphabetical search
	public $def_basicsearch_col = 'commentcontent';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'commentcontent';
	// Required Information for enabling Import feature
	public $required_fields = ['assigned_user_id' => 1];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'commentcontent'];

	public function __construct()
	{
		$this->column_fields = getColumnFields('ModComments');
		$this->db = PearDatabase::getInstance();
	}

	/**
	 * Transform the value while exporting (if required)
	 */
	public function transformExportValue($key, $value)
	{
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Function to get the secondary query part of a report
	 * @param string $module
	 * @param string $secmodule
	 * @param ReportRunQueryPlanner $queryPlanner
	 * @return string
	 */
	public function generateReportsSecQuery($module, $secmodule, ReportRunQueryPlanner $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency('vtiger_crmentityModComments', ['vtiger_groupsModComments', 'vtiger_usersModComments', 'vtiger_contactdetailsRelModComments', 'vtiger_modcommentsRelModComments']);
		$matrix->setDependency('vtiger_modcomments', ['vtiger_crmentityModComments']);

		if (!$queryplanner->requireTable("vtiger_modcomments", $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, 'vtiger_modcomments', 'modcommentsid', $queryplanner);

		if ($queryplanner->requireTable('vtiger_crmentityModComments', $matrix)) {
			$query .= ' left join vtiger_crmentity as vtiger_crmentityModComments on vtiger_crmentityModComments.crmid=vtiger_modcomments.modcommentsid and vtiger_crmentityModComments.deleted=0';
		}
		if ($queryplanner->requireTable('vtiger_groupsModComments')) {
			$query .= ' left join vtiger_groups vtiger_groupsModComments on vtiger_groupsModComments.groupid = vtiger_crmentityModComments.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_usersModComments')) {
			$query .= ' left join vtiger_users as vtiger_usersModComments on vtiger_usersModComments.id = vtiger_crmentityModComments.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_contactdetailsRelModComments')) {
			$query .= ' left join vtiger_contactdetails as vtiger_contactdetailsRelModComments on vtiger_contactdetailsRelModComments.contactid = vtiger_crmentityModComments.crmid';
		}
		if ($queryplanner->requireTable('vtiger_modcommentsRelModComments')) {
			$query .= ' left join vtiger_modcomments as vtiger_modcommentsRelModComments on vtiger_modcommentsRelModComments.modcommentsid = vtiger_crmentityModComments.crmid';
		}
		return $query;
	}
}
