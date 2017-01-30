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
	public $default_note_name_dom = array('Meeting vtiger_notes', 'Reminder');
	public $tab_name = Array('vtiger_crmentity', 'vtiger_notes', 'vtiger_notescf');
	public $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_notes' => 'notesid', 'vtiger_senotesrel' => 'notesid', 'vtiger_notescf' => 'notesid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_notescf', 'notesid');
	public $column_fields = Array();
	// This is used to retrieve related vtiger_fields from form posts.
	public $additional_column_fields = Array('', '', '', '');
	// This is the list of vtiger_fields that are in the lists.
	public $list_fields = Array(
		'Title' => Array('notes' => 'title'),
		'File Name' => Array('notes' => 'filename'),
		'Modified Time' => Array('crmentity' => 'modifiedtime'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
		'Folder Name' => Array('attachmentsfolder' => 'folderid')
	);
	public $list_fields_name = Array(
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Modified Time' => 'modifiedtime',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid'
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['notes_title', 'filename', 'modifiedtime', 'assigned_user_id', 'folderid', 'filelocationtype', 'filestatus'];
	public $search_fields = Array(
		'Title' => Array('notes' => 'notes_title'),
		'File Name' => Array('notes' => 'filename'),
		'Assigned To' => Array('crmentity' => 'smownerid'),
		'Folder Name' => Array('attachmentsfolder' => 'foldername')
	);
	public $search_fields_name = Array(
		'Title' => 'notes_title',
		'File Name' => 'filename',
		'Assigned To' => 'assigned_user_id',
		'Folder Name' => 'folderid'
	);
	public $list_link_field = 'notes_title';
	public $old_filename = '';
	public $mandatory_fields = Array('notes_title', 'createdtime', 'modifiedtime', 'filename', 'filesize', 'filetype', 'filedownloadcount', 'assigned_user_id');
	//Added these variables which are used as default order by and sortorder in ListView
	public $default_order_by = '';
	public $default_sort_order = 'DESC';

	/**    Function used to get the sort order for Documents listview
	 *      @return string  $sorder - first check the $_REQUEST['sorder'] if request value is empty then check in the $_SESSION['NOTES_SORT_ORDER'] if this session value is empty then default sort order will be returned.
	 */
	public function getSortOrder()
	{

		\App\Log::trace('Entering getSortOrder() method ...');
		if (AppRequest::has('sorder'))
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		else
			$sorder = (($_SESSION['NOTES_SORT_ORDER'] != '') ? ($_SESSION['NOTES_SORT_ORDER']) : ($this->default_sort_order));
		\App\Log::trace('Exiting getSortOrder() method ...');
		return $sorder;
	}

	/**     Function used to get the order by value for Documents listview
	 *       @return string  $order_by  - first check the $_REQUEST['order_by'] if request value is empty then check in the $_SESSION['NOTES_ORDER_BY'] if this session value is empty then default order by will be returned.
	 */
	public function getOrderBy()
	{

		\App\Log::trace('Entering getOrderBy() method ...');

		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (AppRequest::has('order_by'))
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		else
			$order_by = (($_SESSION['NOTES_ORDER_BY'] != '') ? ($_SESSION['NOTES_ORDER_BY']) : ($use_default_order_by));
		\App\Log::trace('Exiting getOrderBy method ...');
		return $order_by;
	}

	/**
	 * Function used to get the sort order for Documents listview
	 * @return String $sorder - sort order for a given folder.
	 */
	public function getSortOrderForFolder($folderId)
	{
		if (AppRequest::has('sorder') && AppRequest::get('folderid') == $folderId) {
			$sorder = $this->db->sql_escape_string(AppRequest::get('sorder'));
		} elseif (is_array($_SESSION['NOTES_FOLDER_SORT_ORDER']) &&
			!empty($_SESSION['NOTES_FOLDER_SORT_ORDER'][$folderId])) {
			$sorder = $_SESSION['NOTES_FOLDER_SORT_ORDER'][$folderId];
		} else {
			$sorder = $this->default_sort_order;
		}
		return $sorder;
	}

	/**
	 * Function used to get the order by value for Documents listview
	 * @return String order by column for a given folder.
	 */
	public function getOrderByForFolder($folderId)
	{
		$use_default_order_by = '';
		if (AppConfig::performance('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		if (AppRequest::has('order_by') && AppRequest::get('folderid') == $folderId) {
			$order_by = $this->db->sql_escape_string(AppRequest::get('order_by'));
		} elseif (is_array($_SESSION['NOTES_FOLDER_ORDER_BY']) &&
			!empty($_SESSION['NOTES_FOLDER_ORDER_BY'][$folderId])) {
			$order_by = $_SESSION['NOTES_FOLDER_ORDER_BY'][$folderId];
		} else {
			$order_by = ($use_default_order_by);
		}
		return $order_by;
	}

	/** Function to export the notes in CSV Format
	 * @param reference variable - where condition is passed when the query is executed
	 * Returns Export Documents Query.
	 */
	public function create_export_query($where)
	{

		$current_user = vglobal('current_user');
		\App\Log::trace('Entering create_export_query(' . $where . ') method ...');

		include('include/utils/ExportUtils.php');
		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Documents', 'detail_view');
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
				'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT $fields_list, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name" .
			" FROM vtiger_notes
				inner join vtiger_crmentity
					on vtiger_crmentity.crmid=vtiger_notes.notesid
				LEFT JOIN `vtiger_trees_templates_data` on vtiger_notes.folderid=`vtiger_trees_templates_data`.tree
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id " .
			" LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid=vtiger_groups.groupid "
		;
		$query .= getNonAdminAccessControlQuery('Documents', $current_user);
		$where_auto = ' vtiger_crmentity.deleted=0';
		if ($where != '')
			$query .= "  WHERE ($where) && " . $where_auto;
		else
			$query .= '  WHERE %s';

		$query = sprintf($query, $where_auto);
		\App\Log::trace('Exiting create_export_query method ...');
		return $query;
	}
	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */

	public function generateReportsQuery($module, $queryplanner)
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
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency('vtiger_crmentityDocuments', array('vtiger_groupsDocuments', 'vtiger_usersDocuments', 'vtiger_lastModifiedByDocuments'));
		$matrix->setDependency('vtiger_notes', array('vtiger_crmentityDocuments', '`vtiger_trees_templates_data`'));

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

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		if (empty($returnModule) || empty($returnId))
			return;
		if ($returnModule == 'Accounts') {
			$subQuery = (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $returnId]);
			App\Db::getInstance()->createCommand()->delete('vtiger_senotesrel', ['and', ['notesid' => $id], ['or', ['crmid' => $returnId], ['crmid' => $subQuery]]])->execute();
		} else {
			App\Db::getInstance()->createCommand()->delete('vtiger_senotesrel', ['notesid' => $id, 'crmid' => $returnId])->execute();
			parent::deleteRelatedFromDB($relatedName, $id, $returnModule, $returnId);
		}
	}

	/**
	 * Check the existence of folder by folderid
	 */
	public function isFolderPresent($folderid)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT tree FROM `vtiger_trees_templates_data` WHERE tree = ?', array($folderid));
		if (!empty($result) && $adb->num_rows($result) > 0)
			return true;
		return false;
	}

	/**
	 * Get Folder Default
	 */
	public function getFolderDefault()
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT `tree`,`name` FROM
				`vtiger_trees_templates_data` 
			INNER JOIN `vtiger_field` 
				ON `vtiger_trees_templates_data`.`templateid` = `vtiger_field`.`fieldparams` 
			WHERE `vtiger_field`.`columnname` = ? 
				AND `vtiger_field`.`tablename` = ?
				AND `vtiger_trees_templates_data`.`name` = ?;', array('folderid', 'vtiger_notes', 'Default'));
		return $adb->query_result($result, 0, 'tree');
	}

	/**
	 * Customizing the restore procedure.
	 */
	public function restore($modulename, $id)
	{
		parent::restore($modulename, $id);

		$adb = PearDatabase::getInstance();
		$fresult = $adb->pquery('SELECT folderid FROM vtiger_notes WHERE notesid = ?', array($id));
		if (!empty($fresult) && $adb->num_rows($fresult)) {
			$folderid = $adb->query_result($fresult, 0, 'folderid');
			if (!$this->isFolderPresent($folderid)) {
				// Re-link to default folder
				$adb->pquery('UPDATE vtiger_notes set folderid = ? WHERE notesid = ?', array(self::getFolderDefault()));
			}
		}
	}

	/**
	 * Function to check the module active and user action permissions before showing as link in other modules
	 * like in more actions of detail view.
	 */
	static function isLinkPermitted($linkData)
	{
		$moduleName = 'Documents';
		if (\App\Module::isModuleActive($moduleName) && isPermitted($moduleName, 'EditView') == 'yes') {
			return true;
		}
		return false;
	}
}
