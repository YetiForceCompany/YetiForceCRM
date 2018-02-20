<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */

// Note is used to store customer information.
class Documents extends CRMEntity
{
	public $table_name = 'vtiger_notes';
	public $table_index = 'notesid';
	public $default_note_name_dom = ['Meeting vtiger_notes', 'Reminder'];
	public $tab_name = ['vtiger_crmentity', 'vtiger_notes', 'vtiger_notescf'];
	public $tab_name_index = ['vtiger_crmentity' => 'crmid', 'vtiger_notes' => 'notesid', 'vtiger_senotesrel' => 'notesid', 'vtiger_notescf' => 'notesid'];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_notescf', 'notesid'];
	public $column_fields = [];
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = ['', '', '', ''];
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = [
		'Title' => ['notes' => 'title'],
		'File Name' => ['notes' => 'filename'],
		'Modified Time' => ['crmentity' => 'modifiedtime'],
		'Assigned To' => ['crmentity' => 'smownerid'],
		'Folder Name' => ['attachmentsfolder' => 'folderid'],
	];
	public $list_fields_name = [
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Modified Time' => 'modifiedtime',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['notes_title', 'filename', 'modifiedtime', 'assigned_user_id', 'folderid', 'filelocationtype', 'filestatus'];
	public $search_fields = [
		'Title' => ['notes' => 'notes_title'],
		'File Name' => ['notes' => 'filename'],
		'Assigned To' => ['crmentity' => 'smownerid'],
		'Folder Name' => ['attachmentsfolder' => 'foldername'],
	];
	public $search_fields_name = [
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid',
	];
	public $list_link_field = 'notes_title';
	public $old_filename = '';
	public $mandatory_fields = ['notes_title', 'createdtime', 'modifiedtime', 'filename', 'filesize', 'filetype', 'filedownloadcount', 'assigned_user_id'];
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * @param ReportRunQueryPlanner $queryplanner
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, ReportRunQueryPlanner $queryplanner)
	{
		$moduletable = $this->table_name;
		$moduleindex = $this->tab_name_index[$moduletable];
		$query = "from $moduletable
			inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";
		if ($queryplanner->requireTable('`vtiger_trees_templates_data`')) {
			$query .= " inner join `vtiger_trees_templates_data` on `vtiger_trees_templates_data`.tree=$moduletable.folderid";
		}
		if ($queryplanner->requireTable('vtiger_groups' . $module)) {
			$query .= ' left join vtiger_groups as vtiger_groups' . $module . ' on vtiger_groups' . $module . '.groupid = vtiger_crmentity.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_users' . $module)) {
			$query .= ' left join vtiger_users as vtiger_users' . $module . ' on vtiger_users' . $module . '.id = vtiger_crmentity.smownerid';
		}
		$query .= ' left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= ' left join vtiger_notescf on vtiger_notes.notesid = vtiger_notescf.notesid';
		$query .= ' left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid';
		if ($queryplanner->requireTable('vtiger_lastModifiedBy' . $module)) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedBy' . $module . ' on vtiger_lastModifiedBy' . $module . '.id = vtiger_crmentity.modifiedby ';
		}
		if ($queryplanner->requireTable('u_yf_crmentity_showners')) {
			$query .= ' LEFT JOIN u_yf_crmentity_showners ON u_yf_crmentity_showners.crmid = vtiger_crmentity.crmid';
		}
		if ($queryplanner->requireTable("vtiger_shOwners$module")) {
			$query .= ' LEFT JOIN vtiger_users AS vtiger_shOwners' . $module . ' ON vtiger_shOwners' . $module . '.id = u_yf_crmentity_showners.userid';
		}

		return $query;
	}

	/**
	 * Function to get the secondary query part of a report.
	 *
	 * @param string                $module
	 * @param string                $secmodule
	 * @param ReportRunQueryPlanner $queryPlanner
	 *
	 * @return string
	 */
	public function generateReportsSecQuery($module, $secmodule, ReportRunQueryPlanner $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency('vtiger_crmentityDocuments', ['vtiger_groupsDocuments', 'vtiger_usersDocuments', 'vtiger_lastModifiedByDocuments']);
		$matrix->setDependency('vtiger_notes', ['vtiger_crmentityDocuments', '`vtiger_trees_templates_data`']);

		if (!$queryplanner->requireTable('vtiger_notes', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module, $secmodule, 'vtiger_notes', 'notesid', $queryplanner);
		$query .= ' left join vtiger_notescf on vtiger_notes.notesid = vtiger_notescf.notesid';
		if ($queryplanner->requireTable('vtiger_crmentityDocuments', $matrix)) {
			$query .= ' left join vtiger_crmentity as vtiger_crmentityDocuments on vtiger_crmentityDocuments.crmid=vtiger_notes.notesid and vtiger_crmentityDocuments.deleted=0';
		}
		if ($queryplanner->requireTable('`vtiger_trees_templates_data`')) {
			$query .= ' left join `vtiger_trees_templates_data` on `vtiger_trees_templates_data`.tree=vtiger_notes.folderid';
		}
		if ($queryplanner->requireTable('vtiger_groupsDocuments')) {
			$query .= ' left join vtiger_groups as vtiger_groupsDocuments on vtiger_groupsDocuments.groupid = vtiger_crmentityDocuments.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_usersDocuments')) {
			$query .= ' left join vtiger_users as vtiger_usersDocuments on vtiger_usersDocuments.id = vtiger_crmentityDocuments.smownerid';
		}
		if ($queryplanner->requireTable('vtiger_lastModifiedByDocuments')) {
			$query .= ' left join vtiger_users as vtiger_lastModifiedByDocuments on vtiger_lastModifiedByDocuments.id = vtiger_crmentityDocuments.modifiedby ';
		}
		if ($queryplanner->requireTable('vtiger_createdbyDocuments')) {
			$query .= ' left join vtiger_users as vtiger_createdbyDocuments on vtiger_createdbyDocuments.id = vtiger_crmentityDocuments.smcreatorid ';
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
		$relTables = [];
		if ($secmodule === false) {
			return $relTables;
		}

		return $relTables[$secmodule];
	}

	/**
	 * Function to unlink an entity with given Id from another entity.
	 *
	 * @param int    $id
	 * @param string $returnModule
	 * @param int    $returnId
	 * @param bool   $relatedName
	 */
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId)) {
			return;
		}
		if ($returnModule === 'Accounts') {
			$subQuery = (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $returnId]);
			App\Db::getInstance()->createCommand()->delete('vtiger_senotesrel', ['and', ['notesid' => $id], ['or', ['crmid' => $returnId], ['crmid' => $subQuery]]])->execute();
		} else {
			App\Db::getInstance()->createCommand()->delete('vtiger_senotesrel', ['notesid' => $id, 'crmid' => $returnId])->execute();
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	/**
	 * Check the existence of folder by folderid.
	 *
	 * @param int $folderId
	 *
	 * @return bool
	 */
	public function isFolderPresent($folderId)
	{
		return (new \App\Db\Query())->select(['tree'])->from('vtiger_trees_templates_data')->where(['tree' => $folderId])->exists();
	}

	/**
	 * Get Folder Default.
	 *
	 * @return string
	 */
	public function getFolderDefault()
	{
		return (new \App\Db\Query())->select(['tree', 'name'])->from('vtiger_trees_templates_data')->innerJoin('vtiger_field', 'vtiger_trees_templates_data.templateid = vtiger_field.fieldparams')->where(['vtiger_field.columnname' => 'folderid', 'vtiger_field.tablename' => 'vtiger_notes', 'vtiger_trees_templates_data.name' => 'Default'])->scalar();
	}
}
