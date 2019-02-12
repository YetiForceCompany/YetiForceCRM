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
